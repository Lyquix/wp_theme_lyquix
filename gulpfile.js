/**
 * gulpfile.js - Watch and automatically process CSS and JS
 *
 * @version     2.3.3
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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
import sourcemaps from 'gulp-sourcemaps';
import rename from 'gulp-rename';
import terser from 'gulp-terser';

// Minify CSS
gulp.task('css', () => {
	const postCSSPlugins = [
		cssnano({ preset: 'default' }),
	];
	return gulp.src('css/styles.css')
		.pipe(sourcemaps.init())
		.pipe(postcss(postCSSPlugins))
		.pipe(rename({ suffix: '.min' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('css'));
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
	// If css/custom/editor.css doesn't exists create it with a default background color
	if (!fs.existsSync('css/custom/editor.css')) fs.writeFileSync('css/custom/editor.css', '/* Custom styles for WYSYWIG editor */');

	// Minify CSS
	gulp.watch('css/styles.css', gulp.parallel('css'));

	// Minify JS
	gulp.watch('js/lyquix.js', gulp.parallel('lyquixjs'));
	gulp.watch('js/scripts.js', gulp.parallel('scriptsjs'));
	gulp.watch('js/vue.js', gulp.parallel('vuejs'));

	// Watch CSS, JS and PHP files
	gulp.watch(['css/*.css', 'js/**/*.js', './**/*.php']).on('change', livereload.changed);
});

// Default task
gulp.task('default', gulp.parallel('livereload'));

