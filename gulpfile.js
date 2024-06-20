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
import { spawn, execSync } from 'child_process';

function runTask(command, args, done) {
	const task = spawn(command, args);

	task.stdout.on('data', (data) => {
		console.log(`${data}`);
	});

	task.stderr.on('data', (data) => {
		console.error(`${data}`);
	});

	task.on('close', (code) => {
		if (code !== 0) {
			done(new Error(`Task failed with code ${code}`));
		} else {
			done();
		}
	});
}

// Compile TailwindCSS for frontend
gulp.task('tailwind-frontend', (done) => {
	runTask('npx', ['tailwindcss', '-i', 'css/custom.css', '-c', 'css/tailwind/config.js', '-o', 'css/styles.css'], done);
});

// Compile TailwindCSS for editor
gulp.task('tailwind-editor', (done) => {
	runTask('npx', ['tailwindcss', '-i', 'css/tailwind/editor.css', '-c', 'css/tailwind/editor.config.js', '-o', 'css/editor.css'], done);
});

// Minify CSS
gulp.task('css', () => {
	for (let i = 0; i < 5; i++) {
		const data = fs.readFileSync('css/styles.css', 'utf8');
		const themeRegex = /theme\s*\(\s*['"][^'"]*['"]\s*\)/g;
		if (!themeRegex.test(data)) break;
		execSync('npx tailwindcss -i css/styles.css -c css/tailwind/config.js -o css/styles.css');
	}
	const postCSSPlugins = [
		cssnano({ preset: 'default' }),
		autoprefixer()
	];
	return gulp.src('css/styles.css')
		.pipe(sourcemaps.init())
		.pipe(postcss(postCSSPlugins))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('css'))
		.on('end', () => {
			livereload.reload();
			console.log('\x1b[41m\x1b[37m%s\x1b[0m', '  >>> PAGE RELOADED <<<  ');
		});
});

// Minify JS
gulp.task('lyquixjs', () => {
	return gulp.src('js/lyquix.js')
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});

gulp.task('scriptsjs', () => {
	return gulp.src('js/scripts.js')
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});

gulp.task('vuejs', () => {
	return gulp.src('js/vue.js')
		.pipe(sourcemaps.init())
		.pipe(terser({ output: { comments: false } }))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('js'));
});

// Livereload
gulp.task('livereload', () => {
	livereload.listen(35729);

	// Watch for changes and execute tasks in order
	gulp.watch([
		'css/custom.css',
		'js/*.js',
		'!js/lyquix.min.js',
		'!js/scripts.min.js',
		'!js/vue.min.js',
		'page-templates/*.php',
		'php/**/*.php',
		'css/tailwind/whitelist.html'
	], gulp.series('tailwind-frontend', 'tailwind-editor', 'css'));

	// Minify JS files
	gulp.watch('js/lyquix.js', gulp.series('lyquixjs'));
	gulp.watch('js/scripts.js', gulp.series('scriptsjs'));
	gulp.watch('js/vue.js', gulp.series('vuejs'));
});

// Default task
gulp.task('default', gulp.parallel('livereload'));