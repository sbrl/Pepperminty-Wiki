<?php
register_module([
	"name" => "Command-line interface",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows interaction with Pepperminty Wiki on the command line.",
	"id" => "feature-cli",
	"code" => function() {
		global $settings;
		
		cli_register("version", "Shows the current version of Pepperminty Wiki", function(array $_args) : int {
			echo("$version-".substr($commit, 0, 7)."\n");
			return 0;
		});
		
		cli_register("help", "Displays this message", function(array $_args) : int {
			global $version, $commit, $cli_commands;
			echo("***** Pepperminty Wiki CLI *****
$version-".substr($commit, 0, 7)."

This is the command-line interface for Pepperminty Wiki.

Commands:
");
			
			foreach($cli_commands as $name => $data) {
				echo("    $name    {$data->description}\n");
			}
			
			return 0;
		});
		
		cli_register("shell", "Starts the Pepperminty Wiki shell", function(array $_args) : int {
			cli_shell();
			return 0;
		});
		
		cli_register("exit", "Exits the Pepperminty Wiki shell", function(array $args) {
			$exit_code = 0;
			if(!empty($args)) $exit_code = intval($args[0]);
			exit($exit_code);
		});
		
		add_help_section("999-cli", "Command Line Interface", "<p>System administrators can interact with $settings->sitename via a command-line interface if they have console or terminal-level access to the server that $settings->sitename runs on.</p>
		<p>To do this, system administrators can display the CLI-specific help by changing directory (with the <code>cd</code> command) to be next to <code>index.php</code>, and executing the following:</p>
		<pre><code>php index.php</code></pre>");
	}
]);

/**
 * Ensures that the current execution environment is the command-line interface.
 * This function will not return if thisthe current execution environment is not the CLI.
 * @return void
 */
function ensure_cli() {
	global $settings;
	if(php_sapi_name() == "cli") return true;
	
	header("content-type: text/plain");
	exit("Oops! Somewhere along the way Pepperminty Wiki's command-line interface was invoked by accident.
This is unfortunately an unrecoverable fatal error. Please get in touch with $settings->admindetails_name, $settings->sitename's administrator (their email address si $settings->admindetails_email).
");
}

/**
 * Parses $_SERVER["argv"] and provides a command-line interface.
 * This function kill the process if the current execution environment is not the CLI.
 * @return void
 */
function cli() {
	global $version, $commit;
	ensure_cli();
	
	$args = array_slice($_SERVER["argv"], 1);
	
	switch($args[0] ?? "") {
		case "version":
		case "shell":
			exit(cli_exec($args[0]));
		
		case "exec":
			file_put_contents("php://stderr", "Executing {$args[1]}\n");
			exit(cli_exec($args[1]) ? 0 : 1);
			break;
		
		case "help":
		default:
			echo("***** Pepperminty Wiki CLI *****
$version-".substr($commit, 0, 7)."

This is the command-line interface for Pepperminty Wiki.

Usage:
php ./index.php {subcommand}

Commands:
    help                  Displays this message
    version               Shows the current version of Pepperminty Wiki
    shell                 Starts the Pepperminty Wiki shell
    exec \"{command}\"    Executes a Pepperminty Wiki shell command
");
			break;
	}
	
	exit(0);
}

/**
 * Starts the Pepperminty Wiki CLI Shell.
 * This function kill the process if the current execution environment is not the CLI.
 * @return [type] [description]
 */
function cli_shell() {
	global $settings;
	ensure_cli();
	
	echo(wordwrap("Welcome to the Pepperminty Wiki CLI shell!
Type \"help\" (without quotes) to get help.

Be warned that you are effectively the superuser for your wiki right now, with completely unrestricted access!

"));
	
	while(true) {
		$next_line = readline($settings->cli_prompt);
		if($next_line == false) { echo("\nexit\n"); exit(0); }
		if(strlen($next_line) == 0) continue;
		
		$exit_code = cli_exec($next_line);
		echo("<<<< $exit_code <<<<\n");
	}
}

/**
 * Executes a given Pepperminty Wiki shell command.
 * This function kill the process if the current execution environment is not the CLI.
 * The returned exit code functions as a normal shell process exit code does.
 * @param	string	$string		The shell command to execute.
 * @return	int		The exit code of the command executed.
 */
function cli_exec(string $string) : int {
	global $settings, $cli_commands;
	ensure_cli();
	
	$parts = preg_split("/\s+/", $string);
	
	if(!isset($cli_commands->{$parts[0]})) {
		echo("Error: The command with the name {$parts[0]} could not be found (try the help command instead).\n");
		return 1;
	}
	
	// Apparently you still have to assign a callable to a variable in order to call it dynamically like this. Ref: core/100-run.php
	$method = $cli_commands->{$parts[0]}->code;
	return $method(array_slice($parts, 1));
}

$cli_commands = new stdClass();

/**
 * Registers a new CLI command.
 * Throws an error if a CLI command with the specified name already exists.
 * @param  string   $name        The name of command.
 * @param  string   $description The description of the command.
 * @param  callable $function    The function to execute when this command is executed. An array is passed as the first and only argument containing the arguments passed when the command was invoked.
 * @return void
 */
function cli_register(string $name, string $description, callable $function) {
	global $cli_commands;
	
	if(isset($cli_commands->$name))
		throw new Exception("Error: A CLI command with the name $name has already been registered (description: {$cli_commands->$name->description})");
	
	$cli_commands->$name = (object) [
		"description" => $description,
		"code" => $function
	];
}

?>
