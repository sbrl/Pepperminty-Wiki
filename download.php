<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Pepperminty Wiki Download</title>
	</head>
	<body>
		<h1><img src="https://starbeamrainbowlabs.com/images/logos/peppermint.png" class="logo" /> Pepperminty Wiki Downloader</h1>
		
		<!-------------->
		<h2>Module selector</h2>
		<p>Choose the modules that you want to include in your installation of Pepperminty Wiki <?php echo(trim(file_get_contents("version"))); ?>.</p>
		
		<p>
			<button onclick="select(true);">Select All</button>
			<button onclick="select(false);">Select None</button>
		</p>
		
		<table>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Description</th>
				<th>Author</th>
				<th>Version</th>
				<th style="width: 9rem;">Last Updated</th>
			</tr>
			<?php
			$module_index = json_decode(file_get_contents("module_index.json"));
			foreach($module_index as $module)
			{
			$checkedText = (isset($module->optional) && $module->optional === true) ? "" : " checked";
			echo("<tr>
				<td><input type='checkbox' id='$module->id'$checkedText /></td>
				<td><label for='$module->id'>$module->name</label></td>
				<td>$module->description</td>
				<td>$module->author</td>
				<td>$module->version</td>
				<td>" . date("D jS M Y", $module->lastupdate) . "</td>
			</tr>");
				
			}
			?>
		</table>
		
		<br />
		<br />
		
		<button onclick="download()" class="largebutton">Download</button>
		
		<hr />
		
		<p>
			Pepperminty Wiki was built by <a href="https://starbeamrainbowlabs.com/"><img src="https://starbeamrainbowlabs.com/images/sbrl/SBRL-Small-64.png" class="logo" /> Starbeamrainbowlabs</a>. The code is available on <a href="//github.com/sbrl/pepperminty-wiki">GitHub</a>.
		</p>
		<p>
			Other contributors: <a href="https://github.com/ikisler"><img src="https://avatars2.githubusercontent.com/u/12506147?v=3&s=32" class="logo" /> @ikisler</a>
		</p>
		
		<!------------------->
		<link rel="stylesheet" href="//starbeamrainbowlabs.com/theme/basic.css" />
		<style>
			body			{ padding: 1rem; color: #442772; background-colour: #eee8f2; } /* syntaxtic gets confused sometimes */
			a 				{ color: #9e7eb4;	}
			.largebutton	{ font-size: 2rem;	}
			
			.logo			{ max-width: 1.25em; vertical-align: middle;	}
		</style>
		
		<script>
			function select(state)
			{
				var checkboxes = document.querySelectorAll("input[type=checkbox]");
				for(var i = 0; i < checkboxes.length; i++)
				{
					checkboxes[i].checked = state;
				}
			}
			
			function download()
			{
				var url = "pack.php?web=true&modules=",
					checkboxes = document.querySelectorAll("input[type=checkbox]");
				for(var i = 0; i < checkboxes.length; i++) {
					if(!checkboxes[i].checked) continue;
					url += encodeURIComponent(checkboxes[i].id) + ",";
				}
				location.href = url;
			}
		</script>
		
	</body>
</html>
