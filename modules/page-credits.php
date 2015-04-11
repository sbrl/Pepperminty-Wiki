<?php
register_module([
	"name" => "Credits",
	"version" => "0.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the credits page. You *must* have this module :D",
	"id" => "page-credits",
	"code" => function() {
		add_action("credits", function() {
			global $settings, $version;
			
			$title = "Credits - $settings->sitename";
			$content = "<h1>$settings->sitename credits</h1>
	<p>$settings->sitename is powered by Pepperminty Wiki - an entire wiki packed inside a single file, which was built by <a href='//starbeamrainboowlabs.com'>Starbeamrainbowlabs</a>, and can be found <a href='//github.com/sbrl/Pepperminty-Wiki/'>on github</a> (contributors will ablso be listed here in the future).</p>
	<p>A slightly modified version of slimdown is used to parse text source into HTML. Slimdown is by <a href='https://github.com/jbroadway'>Johnny Broadway</a>, which can be found <a href='https://gist.github.com/jbroadway/2836900'>on github</a>.</p>
	<p>The default favicon is from <a href='//openclipart.org'>Open Clipart</a> by bluefrog23, and can be found <a href='https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23'>here</a>.</p>
	<p>Administrators can update $settings->sitename here: <a href='?action=update'>Update $settings->sitename</a>.</p>
	<p>$settings->sitename is currently running on Pepperminty Wiki <code>$version</code></p>";
			exit(renderpage($title, $content));
		});
	}
]);

?>
