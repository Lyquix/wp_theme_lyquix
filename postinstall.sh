#!/bin/bash

#  postinstall.sh - Post-installation script for Lyquix WordPress theme

#  @version     3.0.0
#  @package     wp_theme_lyquix
#  @author      Lyquix
#  @copyright   Copyright (C) 2015 - 2018 Lyquix
#  @license     GNU General Public License version 2 or later
#  @link        https://github.com/Lyquix/wp_theme_lyquix

#   .d8888b. 88888888888 .d88888b.  8888888b.   888
#  d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
#  Y88b.         888    888     888 888    888  888
#   "Y888b.      888    888     888 888   d88P  888
#      "Y88b.    888    888     888 8888888P"   888
#        "888    888    888     888 888         Y8P
#  Y88b  d88P    888    Y88b. .d88P 888          "
#   "Y8888P"     888     "Y88888P"  888         888

# DO NOT MODIFY THIS FILE!

# Get the current directory and the directory of the script
CURRDIR="${PWD}"
SCRIPTDIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)
cd ${SCRIPTDIR}

# Check for files that need to be created
if [ ! -f custom.php ]; then
	cp custom.dist.php custom.php
fi
if [ ! -f css/custom/custom.scss ]; then
	cp css/custom/custom.dist.scss css/custom/custom.scss
fi
if [ ! -f css/custom.css ]; then
	echo "/* This file will be overwritten when SCSS is compiled */" > css/custom.css
fi
if [ ! -f css/custom/editor.css ]; then
	echo "/* This file will be overwritten when SCSS is compiled */" > css/custom/editor.css
fi
if [ ! -f css/tailwind/theme.js ]; then
	cp css/tailwind/theme.dist.js css/tailwind/theme.js
fi
if [ ! -f php/custom/templates/404.php ]; then
	cp php/custom/templates/404.dist.php php/custom/templates/404.php
fi
if [ ! -f php/custom/templates/search.php ]; then
	cp php/custom/templates/search.dist.php php/custom/templates/search.php
fi

function handle_files {
	DIR=$1
	FILES=("${@:2}")

	# Check if the directory exists, if not create it
	if [ ! -d "css/custom/$DIR" ]; then
		mkdir -p "css/custom/$DIR"
	fi

	for FILE in "${FILES[@]}"; do
		# Check if the file exists in the css/custom/$DIR directory
		if [ ! -f "css/custom/$DIR/_$FILE.scss" ]; then
			# Copy it from the css/lib/$DIR directory
			cp "css/lib/$DIR/_$FILE.dist.scss" "css/custom/$DIR/_$FILE.scss"
		fi
	done
}

ABSTRACTS=("index" "mixins" "variables")
BASE=("forms" "index" "print" "reset" "tables" "typography")
COMPONENTS=("accordion" "alerts" "banner" "buttons" "cards" "cta" "filters" "gallery" "hero" "index" "logos" "popup" "modal" "slider" "social" "tabs")
LAYOUTS=("footer" "header" "index" "layout")
PAGES=("404" "contact" "home" "index" "search")
THEMES=("index" "theme")
VENDORS=("index" "swiper")

handle_files "abstracts" "${ABSTRACTS[@]}"
handle_files "base" "${BASE[@]}"
handle_files "components" "${COMPONENTS[@]}"
handle_files "layouts" "${LAYOUTS[@]}"
handle_files "pages" "${PAGES[@]}"
handle_files "themes" "${THEMES[@]}"
handle_files "vendors" "${VENDORS[@]}"

cd ${CURRDIR}
