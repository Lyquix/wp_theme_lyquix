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
	echo "# This file will be overwritten when SCSS is compiled" > css/custom.css
fi
if [ ! -f css/custom/editor.css ]; then
	echo "# This file will be overwritten when SCSS is compiled" >  css/custom/editor.css
fi
if [ ! -f css/tailwind/theme.js ]; then
	cp css/tailwind/theme.dist.js css/tailwind/theme.js
fi

# Copy sample styles from css/lib to css/custom
FILES=("vars" "base" "text" "lists" "tables" "forms" "accordion" "alerts" "banner" "cards" "cta" "gallery" "hero" "layout" "lyqbox" "popup" "slider" "social" "tabs")
for FILE in "${FILES[@]}"; do
	# Check if the file exists in the css/custom directory
	if [ ! -f "css/custom/$FILE.scss" ]; then
		# If it does not exist, copy it from the css/lib directory
		cp "css/lib/$FILE.dist.scss" "css/custom/$FILE.scss"
	fi
done

# Create empty files for common styling structure
FILES=("utility" "buttons" "pagination" "breadcrumbs" "header" "footer" "filters" "home" "search" "404" "page" "blog" "blog-archive")
for FILE in "${FILES[@]}"; do
	# Check if the file exists in the css/custom directory
	if [ ! -f "css/custom/$FILE.scss" ]; then
		# If it does not exist, create a new one
		# Capitalize the first letter of the file name
		echo "/* $(tr '[:lower:]' '[:upper:]' <<< ${FILE:0:1})${FILE:1} styles */" >  "css/custom/$FILE.scss"
	fi
done

cd ${CURRDIR}
