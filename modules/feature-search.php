<?php
register_module([
	"name" => "Search",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki. Note that this module, at the moment, just contains test code while I figure out how best to write a search engine.",
	"id" => "feature-search",
	"code" => function() {
		add_action("index", function() {
			global $settings, $env;
			
			$breakable_chars = "\r\n\t .,\\/!\"£$%^&*[]()+`_~#";
			
			header("content-type: text/plain");
			
			$source = file_get_contents("$env->page.md");
			$source_length = strlen($source);
			
			$index = [];
			
			// Regex from 
			$terms = preg_split("/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/", $source, -1, PREG_SPLIT_NO_EMPTY);
			$i = 0;
			foreach($terms as $term)
			{
				$nterm = strtolower($term);
				if(!isset($index[$nterm]))
				{
					$index[$nterm] = [ "freq" => 0, "offsets" => [] ];
				}
				
				$index[$nterm]["freq"]++;
				$index[$nterm]["offsets"][] = $i;
				
				$i++;
			}
			
			var_dump($env->page);
			var_dump($source);
			echo("source length: $source_length\n");
			
			var_dump($index);
		});
	}
]);

?>
