/**
 * fields-display.php - Controls the display of Lyquix blocks fields based on global settings, presets, and user settings
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

((wp, acf, $) => {
	const { subscribe, select } = wp.data;

	// Waiting for blocks to be loaded
	$(() => {
		// eslint-disable-next-line no-undef
		setTimeout(() => {
			const blocks = select('core/block-editor').getBlocks();
			if (blocks.length) initBlocks(blocks);
		}, 300);
	});

	// Initialize dependencies on loaded blocks
	function initBlocks(blocks) {
		blocks.forEach((block) => {
			// eslint-disable-next-line no-undef
			acfObj.json.forEach((item) => {
				if (block.attributes.name === item.settings.block_name) {
					updateBlock(item, block);
				}
			});
		});
	}

	// Watch for changes or new blocks
	subscribe(() => {
		var select = wp.data.select('core/block-editor');
		if (select) {
			let block = select.getSelectedBlock();
			if (block) {
				// eslint-disable-next-line no-undef
				acfObj.json.forEach((item) => {
					if (block.attributes.name === item.settings.block_name) {
						let blockEl = $('#block-' + block.clientId);
						// Set an interval to check the condition every 500 milliseconds (0.5 seconds)
						let interval = setInterval(function() {
							if (blockEl.length === 1) {
								updateBlock(item, block, blockEl);
								clearInterval(interval); // Stop the interval after the condition is met and function is executed
							}
						}, 200);
					}
				});
			}
		}
	});

	// Handle block dependency
	const updateBlock = (item, block, blockEl) => {
		for (const rule of item.rules) {
			let dependency = {};

			let settings = processSettings(item.settings, rule);

			if (!block.fieldDependencies) {
				block.fieldDependencies = [];
			}

			let globalSetting = settings.globalSetting;
			let userSetting = settings.userSetting;
			let adminSetting = settings.adminSetting;
			let userSettingKey = findKeyByValue(block.attributes.data, userSetting.user_preset_field) ?
				findKeyByValue(block.attributes.data, userSetting.user_preset_field).replace('_', '') : findValueByKey(block.attributes.data, userSetting.user_preset_field);
			let adminSettingKey = findKeyByValue(block.attributes.data, adminSetting) ?
				findKeyByValue(block.attributes.data, adminSetting).replace('_', '') : findValueByKey(block.attributes.data, adminSetting);

			dependency = block.fieldDependencies.find(obj => obj.key === settings.field_key);

			if (!dependency) {
				dependency = {
					key: settings.field_key,
					controller: rule.controller,
					operator: rule.operator,
					value: rule.value,
					global_setting: globalSetting,
					user_setting: {
						field_value: block.attributes.data[userSettingKey],
						setting_field: userSetting.user_preset_field,
						global_presets_field: getACFFieldValue(userSetting.global_presets_field),
					},
					admin_setting: {
						field_value: block.attributes.data[adminSettingKey],
						setting_field: adminSetting
					}
				};
				block.fieldDependencies.push(dependency);
			} else {
				dependency.user_setting.field_value = findValueByKey(block.attributes.data, userSetting.user_preset_field) ?? block.attributes.data[userSettingKey];
				dependency.admin_setting.field_value = findValueByKey(block.attributes.data, adminSetting) ?? block.attributes.data[adminSettingKey];
				let index = block.fieldDependencies.findIndex(obj => obj.key === dependency.key);
				block.fieldDependencies[index] = dependency;
			}

			handleFieldVisibility(dependency.key, block, blockEl);
		}
	};

	const handleFieldVisibility = (fieldKey, block, blockEl) => {
		let dependency = block.fieldDependencies.find(obj => obj.key === fieldKey);
		let globalSetting = dependency.global_setting;
		let preset = dependency.user_setting.global_presets_field ? dependency.user_setting.global_presets_field.find(obj => obj.preset_name === dependency.user_setting.field_value) : null;
		let userSetting = preset ? findValueByKey(preset, dependency.controller) : null;
		let adminSetting = dependency.admin_setting.field_value;

		// Start with global setting
		let shouldShowFourthField = compareValues(dependency.operator, globalSetting, dependency.value);/* condition based on globalSetting */

		// Override with user setting if it's set
		if (userSetting && userSetting !== '') {
			shouldShowFourthField = compareValues(dependency.operator, userSetting, dependency.value);/* condition based on userSetting */
		}

		// Finally, override with admin setting if it's set
		if (adminSetting && adminSetting !== '') {
			shouldShowFourthField = compareValues(dependency.operator, adminSetting, dependency.value);/* condition based on adminSetting */
		}

		if (shouldShowFourthField) {
			blockEl.find('[data-key=' + fieldKey + ']').removeClass('acf-hidden');
		} else {
			blockEl.find('[data-key=' + fieldKey + ']').addClass('acf-hidden');
		}
	};

	const getACFFieldValue = (fieldKey, fieldGroup, controller) => {
		if(fieldKey) {
			let field = acfObj.globalSettings.find(obj => obj.key === fieldKey);
			return field.value;
		}

		if(fieldGroup) {
			let field = acfObj.globalSettings.find(obj => obj.key === fieldGroup);
			return findValueByKey(field, controller);
		}
	};

	const findKeyByValue = (object, value) => {
		return Object.keys(object).find(key => object[key] === value);
	};

	// Used to walk through object and get value based on key
	const findValueByKey = (obj, keyToFind) => {
		for (let key in obj) {
			if (obj.hasOwnProperty(key)) {
				if (key === keyToFind) {
					return obj[key];
				} else if (typeof obj[key] === 'object') {
					let result = findValueByKey(obj[key], keyToFind);
					if (result) {
						return result;
					}
				}
			}
		}
		return null;
	};

	const processSettings = (settings, rule) => {
		let processed = {};
		processed.field_key = $('[data-key=' + settings.content_field + '] [data-name=' + rule.field + ']').attr('data-key');
		processed.globalSetting = getACFFieldValue(null, settings.global_field, rule.controller);
		processed.userSetting = {};
		processed.userSetting.global_presets_field = settings.presets_field;
		processed.userSetting.user_preset_field = $('[data-key=' + settings.user_field + '] [data-name=preset]').attr('data-key');
		processed.adminSetting = $('[data-key=' + settings.admin_field + '] [data-name=' + rule.controller + ']').attr('data-key');
		return processed;
	};

	const compareValues = (operator, leftValue, rightValue) => {
		switch (operator) {
			case '==':
				return leftValue === rightValue;
			case '!=':
				return leftValue !== rightValue;
			default:
				throw new Error('Unsupported operator');
		}
	};

	// eslint-disable-next-line no-undef
})(wp, acf, jQuery);
