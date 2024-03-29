#!/bin/bash
###
#
# css.dist.sh - Bash script to process SCSS files, run autoprefixer, and chunk files
#
# @version     2.4.1
# @package     wp_theme_lyquix
# @author      Lyquix
# @copyright   Copyright (C) 2015 - 2018 Lyquix
# @license     GNU General Public License version 2 or later
# @link        https://github.com/Lyquix/wp_theme_lyquix
#
###

# Get script directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" > /dev/null && pwd )"
cd $DIR

npx sass ./styles.scss > ./styles.css
npx postcss -u autoprefixer -r ./styles.css
npx uglifycss ./styles.css > ./styles.min.css
rm -f ../dist/*.css
