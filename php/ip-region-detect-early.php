<?php
/**
 * Early IP region detection for W3TC cache key injection.
 * Included from wp-config.php before advanced-cache.php loads.
 */

// The region for this request is decided here, where W3TC can key the page cache on it:
// \lqx\regions\get_region_from_ip() must not look the IP up again while rendering
if (!defined('LQX_EARLY_REGION_DETECT')) define('LQX_EARLY_REGION_DETECT', true);

require_once __DIR__ . '/client-ip.php';

// Skip for admin, cron, and CLI
if (
    (defined('DOING_CRON') && DOING_CRON) ||
    (defined('DOING_AJAX') && DOING_AJAX) ||
    php_sapi_name() === 'cli' ||
    str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-admin') ||
    str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-login.php') ||
    str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-json')
) {
    return;
}

// Skip if user has an explicit manual selection — their choice takes full priority
if (!empty($_COOKIE['selectedRegion'])) return;

// Load regions config
$config_file = dirname(__DIR__, 3) . '/regions-cache.json';

if (!file_exists($config_file)) return;

$config = json_decode(file_get_contents($config_file), true);
if (!is_array($config)) return;

$regions     = $config['regions']               ?? [];
$mmdb_path   = $config['mmdb_path']             ?? '';
$reader_path = $config['reader_path']           ?? '';
$ip_header   = $config['ip_header']             ?? 'REMOTE_ADDR';
$test_ip     = $config['test_ip']               ?? '';
$default     = $config['no_user_region_meaning'] ?? 'outside-region';

if (!$regions || !file_exists($mmdb_path) || !file_exists($reader_path . 'Reader.php')) return;

// Same resolution as the ip2geo REST endpoint: proxy lists and fallback headers
$ip = $test_ip ?: \lqx\util\get_client_ip($ip_header);
$ip = filter_var($ip, FILTER_VALIDATE_IP);
if (!$ip) return;
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) return;

require_once $reader_path . 'Reader.php';
require_once $reader_path . 'Decoder.php';
require_once $reader_path . 'InvalidDatabaseException.php';
require_once $reader_path . 'Metadata.php';
require_once $reader_path . 'Util.php';

try {
    $reader = new \lqx\ip2geo\Reader($mmdb_path);
    $geo    = $reader->get($ip);
    $reader->close();
} catch (Exception $e) {
    return;
}

$lat = isset($geo['location']['latitude'])  ? (float)$geo['location']['latitude']  : null;
$lon = isset($geo['location']['longitude']) ? (float)$geo['location']['longitude'] : null;

if ($lat === null || $lon === null || !is_finite($lat) || !is_finite($lon)) {
    $region = $default;
} else {
    $region = $default;
    foreach ($regions as $r) {
        if (!empty($r['alias']) && !empty($r['geojson']) && _ip_region_point_in_geojson($lon, $lat, $r['geojson'])) {
            $region = $r['alias'];
            break;
        }
    }
}

// Inject into $_COOKIE so W3TC can read it THIS request when building the Redis key
// We only touch ipDetectedRegion — selectedRegion belongs to the user
$_COOKIE['ipDetectedRegion'] = $region;


// --- Polygon functions ---

function _ip_region_point_in_geojson(float $lon, float $lat, array $g): bool {
    $type = $g['type'] ?? null;
    if ($type === 'FeatureCollection' && is_array($g['features'] ?? null)) {
        foreach ($g['features'] as $feature) {
            if (is_array($feature) && _ip_region_point_in_geojson($lon, $lat, $feature)) return true;
        }
        return false;
    }
    if ($type === 'Feature') {
        $geom = $g['geometry'] ?? null;
        return is_array($geom) && _ip_region_point_in_geojson($lon, $lat, $geom);
    }
    if ($type === 'Polygon' && is_array($g['coordinates'] ?? null)) {
        return _ip_region_in_polygon_coords($lon, $lat, $g['coordinates']);
    }
    if ($type === 'MultiPolygon' && is_array($g['coordinates'] ?? null)) {
        foreach ($g['coordinates'] as $poly) {
            if (is_array($poly) && _ip_region_in_polygon_coords($lon, $lat, $poly)) return true;
        }
        return false;
    }
    return false;
}

function _ip_region_in_polygon_coords(float $lon, float $lat, array $coords): bool {
    if (!isset($coords[0]) || !is_array($coords[0]) || count($coords[0]) < 3) return false;
    if (!_ip_region_in_polygon($lon, $lat, $coords[0])) return false;
    for ($i = 1; $i < count($coords); $i++) {
        if (is_array($coords[$i]) && _ip_region_in_polygon($lon, $lat, $coords[$i])) return false;
    }
    return true;
}

function _ip_region_in_polygon(float $testLon, float $testLat, array $ring): bool {
    $n = count($ring);
    if ($n < 3) return false;
    $oddNodes = false;
    $j = $n - 1;
    for ($i = 0; $i < $n; $i++) {
        $iLat = (float)$ring[$i][1];
        $jLat = (float)$ring[$j][1];
        if (($iLat < $testLat && $jLat >= $testLat) || ($jLat < $testLat && $iLat >= $testLat)) {
            $iLon = (float)$ring[$i][0];
            $jLon = (float)$ring[$j][0];
            if ($iLon + ($testLat - $iLat) / ($jLat - $iLat) * ($jLon - $iLon) < $testLon) {
                $oddNodes = !$oddNodes;
            }
        }
        $j = $i;
    }
    return $oddNodes;
}