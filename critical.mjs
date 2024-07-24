/**
 * critical.mjs - Generate Critical Path CSS
 *
 * @version     2.4.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

import fs from 'fs';
import { generate } from 'critical';
import puppeteer from 'puppeteer';
import readline from 'readline';
import fetch from 'node-fetch';
import CleanCSS from 'clean-css';

const rl = readline.createInterface({
	input: process.stdin,
	output: process.stdout
});

const CONFIG_FILE = './critical.json';

function isValidURL(string) {
	try {
		new URL(string);
		return true;
	} catch (_) {
		return false;
	}
}

function promptForInput(question) {
	return new Promise((resolve) => {
		rl.question(question, (answer) => {
			resolve(answer);
		});
	});
}

async function promptForURL() {
	while (true) {
		const url = await promptForInput('Please enter the URL of the website: ');
		if (isValidURL(url)) {
			return url;
		} else {
			console.log('Invalid URL. Please try again.');
		}
	}
}

async function promptForCredentials() {
	const username = await promptForInput('Enter HTTP username (optional): ') || '';
	const password = await promptForInput('Enter HTTP password (optional): ') || '';
	return { username, password };
}

async function fetchCriticalCssCfg(baseUrl, credentials) {
	const url = new URL('/wp-json/lyquix/v2/critical', baseUrl);
	const options = {
		method: 'GET',
		headers: {}
	};

	if (credentials.username || credentials.password) {
		const base64Credentials = Buffer.from(`${credentials.username}:${credentials.password}`).toString('base64');
		options.headers['Authorization'] = `Basic ${base64Credentials}`;
	}

	const response = await fetch(url.toString(), options);
	if (!response.ok) {
		console.error('Full response:', response);
		console.error('Response headers:', response.headers);
		const responseText = await response.text();
		console.error('Response body:', responseText);
		throw new Error(`HTTP error! status: ${response.status}, statusText: ${response.statusText}`);
	}

	return await response.json();
}

const criticalCSS = async (criticalCssCfg, baseUrl, credentials) => {
	const browser = await puppeteer.launch();
	const page = await browser.newPage();
	const screen = { 320: 'xs', 640: 'sm', 960: 'md', 1280: 'lg', 1600: 'xl' };

	await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

	// Log browser errors
	page.on('pageerror', error => {
		console.log(`Page error: ${error.toString()}`);
		console.log(error.stack);
	});

	// Set authentication if needed
	if (credentials.username || credentials.password) {
		await page.authenticate({
			username: credentials.username,
			password: credentials.password
		});
	}

	for (const template of criticalCssCfg.templates) {
		console.log(`Processing ${template.url}`);
		const basePath = `./css/critical/${template.type}${template.type === 'page' ? `-${template.slug}` : ''}`;
		const criticalCssPath = `${basePath}.css`;
		let criticalCss = '';

		// Go to the URL
		await page.goto(`${template.url}${template.url.includes('?') ? '&' : '?'}no-critical-path-css`, {
			waitUntil: 'networkidle0'
		});

		for (const viewport of criticalCssCfg.viewports) {
			console.log(`Set viewport to ${viewport.width}x${viewport.height}`);
			await page.setViewport(viewport);
			await page.waitForSelector(`body[screen="${screen[viewport.width]}"]`);
			await page.evaluate(() => new Promise(r => setTimeout(r, 1000)));

			// Get the fully rendered HTML
			const content = await page.content();

			await new Promise((resolve, reject) => {
				const options = {
					base: baseUrl,
					html: content,
					width: viewport.width,
					height: viewport.height,
					ignoreInlinedStyles: true,
					ignore: {
						rule: [
							/#ie-alert/
						],
						decl: [
							/--wp--preset--/
						]
					},
					cleanCSS: { level: 0 },
					penthouse: {
						blockJSRequests: false,
					}
				};

				// Set authentication if needed
				if (credentials.username || credentials.password) {
					options.user = credentials.username,
					options.pass = credentials.password
				}

				generate(options, (err, output) => {
					if (err) {
						console.error(`Failed to generate critical CSS for ${template.url} on ${viewport.width}x${viewport.height}:`, err);
						resolve(err);
					} else {
						criticalCss += output.css;
						console.log(`Critical CSS generated for ${template.url} on ${viewport.width}x${viewport.height}`);
						resolve(output);
					}
				});
			});
		}

		if (criticalCss) {
			// Optimize and deduplicate the CSS using CleanCSS
			const output = new CleanCSS({
				level: {
					1: {
						all: true,
					},
					2: {
						all: false,
						removeDuplicateFontRules: true,
						removeDuplicateMediaBlocks: true,
						removeDuplicateRules: true,
						removeEmpty: true,
						mergeMedia: true
					}
				}
			}).minify(criticalCss);
			fs.writeFileSync(criticalCssPath, output.styles);
			console.log(`Critical CSS saved to ${criticalCssPath}`);
		}
	}

	await browser.close();
};

function loadConfig() {
	if (fs.existsSync(CONFIG_FILE)) {
		return JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf8'));
	}
	return null;
}

function saveConfig(config) {
	fs.writeFileSync(CONFIG_FILE, JSON.stringify(config, null, 2));
}

async function getConfig() {
	const savedConfig = loadConfig();
	if (savedConfig) {
		const useExisting = await promptForInput(`Use saved configuration? (URL: ${savedConfig.baseUrl}) (y/n): `);
		if (useExisting.toLowerCase() === 'y') return savedConfig;
	}

	const baseUrl = await promptForURL();
	const credentials = await promptForCredentials();
	const newConfig = { baseUrl, credentials };
	saveConfig(newConfig);
	return newConfig;
}

async function main() {
	try {
		const config = await getConfig();
		const criticalCssCfg = await fetchCriticalCssCfg(config.baseUrl, config.credentials);
		await criticalCSS(criticalCssCfg, config.baseUrl, config.credentials);
	} catch (error) {
		console.error('An error occurred:', error);
	} finally {
		rl.close();
	}
}

main();
