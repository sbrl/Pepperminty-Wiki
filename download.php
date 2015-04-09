<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Pepperminty Wiki Download</title>
	</head>
	<body>
		<h1>Pepperminty Wiki Download</h1>
		
		<!-------------->
		<h2>Module selector</h2>
		<p>Choose the modules that you want to include in your installation of Pepperminty Wiki.</p>
		
		<table>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Description</th>
				<th>Author</th>
				<th>Version</th>
				<th>Last Updated</th>
			</tr>
			<?php
			$module_index = json_decode(file_get_contents("module_index.json"));
			foreach($module_index as $module)
			{
			echo("<tr>
				<td><input type='checkbox' id='$module->id' /></td>
				<td><label for='$module->id'>$module->name</label></td>
				<td>$module->description</td>
				<td>$module->author</td>
				<td>$module->version</td>
				<td>" . date("r", $module->lastupdate) . "</td>
			</tr>");
				
			}
			?>
		</table>
		
		<script>
			document.getElementById("download_button").addEventListener("click", function(event) {
				
			});
		</script>
		<link rel="stylesheet" href="//starbeamrainbowlabs.com/theme/basic.css" />
	</body>
</html>