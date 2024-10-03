/**
 * gulpfile.js - Watch and automatically process CSS and JS
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

import gulp from 'gulp';
import fs from 'fs';
import livereload from 'gulp-livereload';
import postcss from 'gulp-postcss';
import cssnano from 'cssnano';
import autoprefixer from 'autoprefixer';
import sourcemaps from 'gulp-sourcemaps';
import rename from 'gulp-rename';
import terser from 'gulp-terser';
import { exec, execSync } from 'child_process';

// Compile SCSS and Tailwind CSS
gulp.task('compile-css', (done) => {
	try {
		console.log('Running SASS compilation...');
		execSync('sass css/custom/custom.scss css/custom.css', { stdio: 'pipe', maxBuffer: 1024 * 500 });

		console.log('Running Tailwind CSS (frontend styles)...');
		execSync('tailwindcss -i css/custom.css -c css/tailwind/config.js -o css/styles.css', { stdio: 'pipe', maxBuffer: 1024 * 500 });

		console.log('Running Tailwind CSS (editor styles)...');
		execSync('tailwindcss -i css/tailwind/editor.css -c css/tailwind/editor.config.js -o css/editor.css', { stdio: 'pipe', maxBuffer: 1024 * 500 });

		for (let i = 0; i < 5; i++) {
			const data = fs.readFileSync('css/styles.css', 'utf8');
			const themeRegex = /theme\s*\(\s*['"][^'"]*['"]\s*\)/g;
			if (!themeRegex.test(data)) break;
			exec('npx tailwindcss -i css/styles.css -c css/tailwind/config.js -o css/styles.css');
		}
		const postCSSPlugins = [
			cssnano({ preset: 'default' }),
			autoprefixer()
		];

		gulp.src('css/styles.css')
			.pipe(sourcemaps.init())
			.pipe(postcss(postCSSPlugins))
			.pipe(rename({ suffix: '.min' }))
			.pipe(sourcemaps.write('.'))
			.pipe(gulp.dest('css'))
			.on('end', () => livereload.reload());

		console.log('\x1b[41m\x1b[37m%s\x1b[0m', '  >>> PAGE RELOADED <<<  ');

		done();
	} catch (err) {
		console.error('Error during CSS compilation:');
		console.log(err)

		// Convert the error output into a string
		const errorOutput = err.stderr ? err.stderr.toString() : err.stdout.toString();

		try {
			// Match the specific parts of the CssSyntaxError
			const reasonMatch = errorOutput.match(/reason:\s*'([^']+)'/);
			const fileMatch = errorOutput.match(/file:\s*'([^']+)'/);
			const lineMatch = errorOutput.match(/line:\s*(\d+)/);

			if (reasonMatch && fileMatch && lineMatch) {
				console.error(`Reason: ${reasonMatch[1]}`);
				console.error(`File: ${fileMatch[1]}`);
				console.error(`Line: ${lineMatch[1]}`);
			} else {
				// Fallback: Log the error if extraction failed
				// Convert the stderr buffer into a string
				const errorOutput = err.stderr.toString();

				// Extract and print the relevant information
				const relevantLines = errorOutput.split('\n').slice(0, 8).join('\n');
				console.error(relevantLines);
			}
		} catch (extractionError) {
			console.error('Error processing the error details:', extractionError.message);
		}

		// Stop further execution and prevent additional error logging
		done();
	}
});

// Minify JS
gulp.task('lyquixjs', () => {
	console.log('Running lyquixjs minification...');
	return gulp.src('js/lyquix.js')
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});
gulp.task('scriptsjs', () => {
	console.log('Running scriptsjs minification...');
	return gulp.src('js/scripts.js')
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});
gulp.task('vuejs', () => {
	console.log('Running vuejs minification...');
	return gulp.src('js/vue.js', { allowEmpty: true })
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});

// Livereload
gulp.task('livereload', () => {
	livereload.listen(35729);

	// Watch SCSS files and trigger the compilation sequence
	gulp.watch([
		'css/custom/**/*.scss',
		'js/lyquix.js',
		'js/scripts.js',
		'page-templates/*.php',
		'php/**/*.php',
		'custom.php',
		'tribe/**/*.php',
		'tribe-events/**/*.php',
		'css/tailwind/whitelist.html'
	], gulp.series('compile-css'));

	//Watch for changes in JS files for livereload
	gulp.watch(['js/lyquix.js', 'js/scripts.js']).on('change', (path) => {
		gulp.parallel('lyquixjs', 'scriptsjs', 'vuejs')(); // Minify JS
	});
});

// Default task
gulp.task('default', gulp.parallel('compile-css', 'livereload'));


