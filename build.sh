#!/usr/bin/env bash
# Make sure the current directory is the location of this script to simplify matters
cd "$(dirname $(readlink -f $0))";
################
### Settings ###
################

# The name of this project
project_name="Pepperminty Wiki";

# The path to the lantern build engine git submodule
lantern_path="./lantern-build-engine";

###
# Custom Settings
###

# Put any custom settings here.

# The file to store the development server's PID in.
server_pid_file="/tmp/pepperminty-wiki-dev-server.pid";

###############################################################################

# Check out the lantern git submodule if needed
if [ ! -f "${lantern_path}/lantern.sh" ]; then git submodule update --init "${lantern_path}"; fi

source "${lantern_path}/lantern.sh";

if [[ "$#" -lt 1 ]]; then
	echo -e "${FBLE}${project_name}${RS} build script";
	echo -e "    by Starbeamrainbowlabs";
	echo -e "${LC}Powered by the lantern build engine, v${version}${RS}";
	echo -e "";
	echo -e "${CSECTION}Usage${RS}";
	echo -e "    ./build ${CTOKEN}{action}${RS} ${CTOKEN}{action}${RS} ${CTOKEN}{action}${RS} ...";
	echo -e "";
	echo -e "${CSECTION}Available actions${RS}";
	echo -e "    ${CACTION}setup${RS}           - Perform initial setup, check the environment (skip if only building Pepperminty Wiki itself)";
	echo -e "    ${CACTION}build${RS}           - Build Pepperminty Wiki";
	echo -e "    ${CACTION}docker${RS}          - Build the Docker image";
	echo -e "    ${CACTION}themes${RS}          - Rebuild the theme index";
	echo -e "    ${CACTION}docs${RS}            - Build the documentation";
	echo -e "    ${CACTION}docs-livereload${RS} - Start the documentation livereload server";
	echo -e "    ${CACTION}start-server${RS}    - Start a development server";
	echo -e "    ${CACTION}stop-server${RS}     - Stop the development server";
	echo -e "    ${CACTION}sign${RS}            - Sign the current build with SHA256 & GPG";
	echo -e "    ${CACTION}clean${RS}           - Delete all build outputs (WARNING: THIS WILL DELETE ALL WIKI DATA)";
	echo -e "";
	
	exit 1;
fi

# Tests to see if a PHP module is installed.
# $1 - The name of the module to check for
# $2 - The mode of operation. Values: optional, required
# $3 - The error message to show
test_php_module() {
	module_name="${1}";
	mode="${2}";
	reason="${3}";
	
	subtask_begin "Checking for ${module_name} PHP module";
	php -m | grep -q "${module_name}";
	exit_code="${?}";
	if [[ "${mode}" = "optional" ]] && [[ "${exit_code}" -ne 0 ]]; then
		echo "${FYEL}${HC}Warning: The PHP module ${module} was not found. It is needed to ${reason}.${RS}";
	fi
	subtask_end "${exit_code}";
}

###############################################################################

task_setup() {
	task_begin "Checking Environment";
	
	check_command git true;
	check_command npm true;
	check_command php true;
	test_php_module "mbstring" "required" "handle utf-8 characters correctly";
	test_php_module "imagick" "optional" "generate image previews";
	test_php_module "fileinfo" "optional" "properly check the mime type of uploaded files";
	test_php_module "zip" "optional" "compressing exports";
	test_php_module "intl" "required" "transliteration in the search engine and when sending emails when utf-8 is disabled";
	test_php_module "sqlite" "optional" "store the inverted search index";
	check_command jq true optional;
	[[ "$?" -eq 0 ]] || echo -e "${FYEL}${HC}Warning: jq is required to update the theme index.${RS}";
	check_command firefox true optional;
	[[ "$?" -eq 0 ]] || echo -e "${FYEL}${HC}Warning: firefox is required to generate theme previews.${RS}";
	check_command convert true optional;
	[[ "$?" -eq 0 ]] || echo -e "${FYEL}${HC}Warning: The convert imagemagick command is required to generate theme previews.${RS}";
	check_command nproc true optional;
	[[ "$?" -eq 0 ]] || echo -e "${FYEL}${HC}Warning: nproc is required to generate theme previews.${RS}";
	
	task_end $?;
	
	task_begin "Initialising submodules";
	git submodule update --init;
	task_end $?;
	
	task_begin "Installing packages";
	npm install;
	task_end $?;
	
	task_begin "Creating build folders";
	mkdir -p build/_tmp;
	echo "This folder contains build tools automatically downloaded." >build/_tmp/README.txt;
	task_end $?;
}

task_build() {
	if [ -f "./build/index.php" ]; then
		task_begin "Deleting old build result";
		rm build/index.php;
		task_end "$?";
	fi
	
	task_begin "Building";
	php build.php
	task_end $?;
}

task_docker() {
	task_build;
	
	task_begin "Building Docker image";
	if [[ -n "${DO_DOCKER_SUDO}" ]]; then
		sudo docker build --tag pepperminty-wiki .;
	else
		docker build --tag pepperminty-wiki .;
	fi
	task_end "$?" "Failed to build Docker image";
}

task_themes() {
	if [[ ! -f "${server_pid_file}" ]]; then
		NO_BROWSER=true tasks_run start-server;
	fi
	
	stage_begin "Updating theme index";
	
	
	task_begin "Preparing";
	
	[ -f "themes/themeindex.json" ] && cp "themes/themeindex.json" "themes/themeindex.json.old";
	
	# Temporary firefox profile directory
	tmp_profile="$(mktemp -d /tmp/peppermint-firefox-profile-XXXXXXX)";
	
	# Temporary file for theme index items
	tmp_themeindex_parts="$(mktemp /tmp/peppermint-themeindex-items-XXXXXXX)";
	
	task_end $?;
	
	preview_regen=false;
	
	while read -r filename; do
		task_begin "Processing ${filename}";
		
		hash="$(sha256sum "${filename}" | cut -d' ' -f1)";
		read -r -d "" awk_script <<'AWK'
BEGIN {
	items[0] = "\"hash\": \"" prop_hash "\"";
	count=1;
}
/\s+\*\s+@/ {
	atrule=$2;
	gsub(/@/, "", atrule);
	gsub(/\s*\*\s*@[a-z\_]+\s+/, "", $0);
	items[count] = "\"" atrule "\": \"" $0 "\"";
	count++;
}
END {
	result="{";
	for(i = 0; i < count; i++) {
		result = result items[i];
		if(i < count - 1) result = result ",";
	}
	print(result "}");
}
AWK
		# TODO: Consider mapping it out as TSV, then using JQ to generate the object
		subtask_begin "Generating index entry";
		awk -v prop_hash="${hash}" "${awk_script}" <"${filename}" >>"${tmp_themeindex_parts}";
		subtask_end "$?";
		
		
		# Capture the screenshot
		if [ -f "themes/themeindex.json.old" ]; then 
			theme_id="$(awk '/@id/ { print $3 }' <"${filename}")";
			old_hash="$(jq --raw-output --arg theme_id "${theme_id}" '.[] | select(.id == $theme_id).hash' <"themes/themeindex.json")";
			
			# If the hash is the same as last time, don't bother to retake the screenshot
			if [[ "${hash}" = "${old_hash}" ]]; then
				continue;
			fi
		fi
		preview_regen=true;
		
		screenshot_loc_full="$(dirname "${filename}")/preview_large.png";
		screenshot_loc_small="$(dirname "${filename}")/preview_small.png";
		
		# Set the theme
		cp "build/peppermint.json" "build/peppermint.json.bak";
		tmp_file="$(mktemp /tmp/peppermint-json-XXXXXXX)";
		jq --arg theme_css "$(cat "${filename}")" '.css = $theme_css' <"build/peppermint.json" >"${tmp_file}";
		mv "${tmp_file}" "build/peppermint.json";
		
		# Capture the full-res screenshot
		execute firefox --new-instance --headless --profile "${tmp_profile}" --window-size 1920,1080 --screenshot "${screenshot_loc_full}" "http://[::1]:35623/index.php";
		
		# Resize to get the smaller preview
		execute convert "${screenshot_loc_full}" -resize 512x512 "${screenshot_loc_small}";
		
		mv "build/peppermint.json.bak" "build/peppermint.json";
		
		task_end "$?";
	done < <(find themes -type f -name "theme.css");
	
	task_begin "Optimising new previews";
	find "themes/" -iname "*.png" -print0 | xargs -0 -P"$(nproc)" -n1 optipng -preserve;
	task_end "$?";
	
	task_begin "Generating theme index";
	jq --tab --slurp . <"${tmp_themeindex_parts}" >"themes/themeindex.json"
	task_end "$?";
	
	# Clean up
	task_begin "Cleaning up";
	[[ -d "${tmp_profile}" ]] && rm -r "${tmp_profile}";
	[[ -f "themes/themeindex.json.old" ]] && rm "themes/themeindex.json.old";
	task_end 0;
	
	stage_end 0;
}

task_docs() {
	task_begin "Building HTTP API Docs";
	node_modules/apidoc/bin/apidoc -o './docs/RestApi/' --config apidoc.json --input . -f '.*\.php' -e 'index.php|ModuleApi'
	exit_code="$?";
	rm -rf doc/;
	task_end "${exit_code}";
	
	task_begin "Building PHP Module API Docs";
	if [ ! -f "./build/_tmp/phpdoc" ]; then
		subtask_begin "Downloading PHPDoc";
		# Create the temporary directory if it doesn't exist yet
		[ -d "./build/_tmp" ] || mkdir -p "./build/_tmp/";
			
		curl -sSL https://phpdoc.org/phpDocumentor.phar -o ./build/_tmp/phpdoc
		subtask_end $?;
	fi
	
	php ./build/_tmp/phpdoc run \
		--directory . \
		--target docs/ModuleApi\
		--cache-folder build/_tmp/ModuleApiCache \
		--ignore build/,Parsedown*,*.html \
		--title "Pepperminty Wiki Module API" \
		--visibility public;
	task_end $?;
	
	task_begin "Building Main Documentation";
	node_modules/.bin/nightdocs -c nightdocs.toml
	task_end $?;
}

task_docs-livereload() {
	task_begin "Listening for changes to docs";
	while :; do
		inotifywait -qr -e modify --format '%:e %f' ./docs/ nightdocs.toml;
		node_modules/.bin/nightdocs -c nightdocs.toml;
	done
	task_end $?;
}

task_start-server() {
	task_begin "Starting server";
	if [ -f "${server_pid_file}" ]; then
		task_end 1 "${FRED}${HC}Error: A development server appears to be running already. Try running the 'stop-server' task before starting it again.${RS}";
	fi
	php -S [::]:35623 -t build/ &
	local exit_code=$?; local pid=$!;
	echo "${pid}" >"${server_pid_file}";
	
	task_end "${exit_code}" "";
	
	task_begin "Starting theme server";
	php -S [::]:35624 -t themes/ &
	exit_code=$?; pid=$!;
	echo "${pid}" >"${server_pid_file}.themes";
	task_end "${exit_code}";
	
	if [[ -z "${NO_BROWSER}" ]]; then
		task_begin "Opening Browser";
		# sensible-browser isn't opening the right browser :-/
		xdg-open "http://[::]:35623";
		task_end $?;
	fi
}

task_stop-server() {
	task_begin "Stopping server";
	kill "$(cat "${server_pid_file}")";
	rm "${server_pid_file}";
	task_end $?;
	
	task_begin "Stopping theme server";
	kill "$(cat "${server_pid_file}.themes")";
	rm "${server_pid_file}.themes";
	task_end "$?";
}

task_sign() {
	task_begin "Preparing to sign release";
	if [[ ! -f "build/index.php" ]]; then
		task_end 1 "Error: build/index.php doesn't exist";
	fi
	
	temp_dir="$(mktemp --tmpdir -d "pepperminty-wiki-XXXXXXX")";
	cp "build/index.php" "${temp_dir}";
	task_end "$?";
	
	task_begin "Signing release";
	pushd "${temp_dir}" || { echo "Error: Failed to cd to temporary directory"; exit 1; };
	# Generate hashes
	find . -type f -not -name "*.SHA256" -print0 | xargs -0 -I{} -P"$(nproc)" sha256sum -b "{}" >HASHES.SHA256;
	# Generate GPG signature
	gpg --sign --detach-sign --armor HASHES.SHA256;
	popd || { echo ""; exit 1; };
	task_end "$?";
	
	task_begin "Finalising";
	cp "${temp_dir}/HASHES.SHA256" "./build";
	cp "${temp_dir}/HASHES.SHA256.asc" "./build";
	echo -e "Written output files to ${HC}$(display_url "${PWD}/build/HASHES.SHA256" "HASHES.SHA256") ${RS}and ${HC}$(display_url "${PWD}/build/HASHES.SHA256.asc" "HASHES.SHA256.asc").${RS}";
	rm -r "${temp_dir}";
	task_end "$?" "Failed to finalise!";
}

task_clean() {
	task_begin "Clearing out build outputs";
	
	if [[ "${PEPPERMINT_REALLY_CLEAN}" != "yes" ]]; then
		echo -e "Are you SURE you want to continue? This will delete ALL your wiki data!";
		echo -e "Set the environment variable PEPPERMINT_REALLY_CLEAN to 'yes' (without quotes) to actually do the deletion.\n\n";
		task_end 1 "Aborted.";
		exit 1; # Just in case
	fi
	
	echo "[PEPPERMINT_REALLY_CLEAN] I sure hope you know what you're doing.";
	
	rm -rf build module_index.json themes/themeindex.json node_modules __nightdocs ;
	find themes -type f -name "preview_large.png" -delete;
	find themes -type f -name "preview_small.png" -delete;
	
	task_end "$?" "Failed to completely clear out all build outputs";
}
###############################################################################

tasks_run $@;
