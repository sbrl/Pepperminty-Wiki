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
if [ ! -d "${lantern_path}" ]; then git submodule update --init "${lantern_path}"; fi

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
	echo -e "    ${CACTION}docs${RS}            - Build the documentation";
	echo -e "    ${CACTION}docs-livereload${RS} - Start the documentation livereload server";
	echo -e "    ${CACTION}start-server${RS}    - Start a development server";
	echo -e "    ${CACTION}stop-server${RS}     - Stop the development server";
	echo -e "";
	
	exit 1;
fi

###############################################################################

task_setup() {
	task_begin "Checking Environment";
	
	check_command git true;
	check_command npm true;
	check_command npm true;
	
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

task_docs() {
	task_begin "Building HTTP API Docs";
	node_modules/apidoc/bin/apidoc -o './docs/RestApi/' --config apidoc.json -f '.*\.php' -e 'index.php|ModuleApi'
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
	
	php ./buihttps://phpdoc.org/ld/_tmp/phpdoc run \
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
	task_begin "Starting Server";
	if [ -f "${server_pid_file}" ]; then
		task_end 1 "${FRED}${HC}Error: A development server appears to be running already. Try running the 'stop-server' task before starting it again.${RS}";
	fi
	php -S [::]:35623 -t build/ &
	exit_code=$?; pid=$!;
	echo "${pid}" >"${server_pid_file}";
	task_end "${exit_code}" "";
	
	task_begin "Opening Browser";
	sensible-browser [::]:35623;
	task_end $?;
}

task_stop-server() {
	task_begin "Stopping Server";
	
	kill "$(cat "${server_pid_file}")";
	rm "${server_pid_file}";
	
	task_end $?;
}
###############################################################################

tasks_run $@;
