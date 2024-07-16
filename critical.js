/**
 * critical.js - Generate Critical Path CSS
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
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

import fs from 'fs';
import { generate } from 'critical';
import readline from 'readline';
import fetch from 'node-fetch';

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
	const url = new URL('/wp-json/lyquix/v3/critical', baseUrl);
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
	for (const template of criticalCssCfg.templates) {
		const basePath = `./css/critical/${template.type}${template.type === 'page' ? `-${template.slug}` : ''}`;
		const criticalCssPath = `${basePath}.css`;

		if (fs.existsSync(criticalCssPath)) fs.unlinkSync(criticalCssPath);

		await new Promise((resolve, reject) => {
			const options = {
				inline: false,
				src: `${template.url}${template.url.includes('?') ? '&' : '?'}no-critical-path-css`,
				dimensions: criticalCssCfg.viewports,
				ignoreInlinedStyles: true,
				penthouse: {
					blockJSRequests: false
				}
			};

			if (credentials.username || credentials.password) {
				options.user = credentials.username;
				options.pass = credentials.password;
			}

			generate(options, (err, output) => {
				if (err) {
					console.error(`Failed to generate critical CSS for ${template.url}:`, err);
					reject(err);
				} else {
					fs.appendFileSync(criticalCssPath, output.css);
					console.log(`Critical CSS generated for ${template.url}`);
					resolve(output);
				}
			});
		});
	}
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
