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
			
			var_dump($env->page);
			var_dump($source);
			echo("source length: $source_length\n");
			
			$basepos = 0;
			$scanpos = $basepos;
			while($basepos < $source_length)
			{
				$word = "";
				do {
					// Break if we reach the end of the source text
					if($scanpos >= $source_length) break;
					$word .= $source[$scanpos];
					$scanpos++;
				} while(strpos($breakable_chars, $source[$scanpos]) === false);
				
				// Move the base position up to the scan position (plus one to
				// skip over the breakable character), saving the old base
				// position for later
				$word_start_pos = $basepos;
				$basepos = $scanpos + 1;
				// Continue if the word is empty
				if(strlen($word) === 0) continue;
				// Normalise the word to be lowercase
				$word = strtolower($word);
				
				var_dump($word);
				
				// Initialise the entry in the index if it doesn't exist
				if(!isset($index[$word]))
				{
					$index[$word] = [
						"freq" => 0,
						"offsets" => []
					];
				}
				// Update the index entry
				$index[$word]["freq"]++;
				$index[$word]["offsets"][] = $word_start_pos;
			}
			
			var_dump($index);
		});
	}
]);

?>
