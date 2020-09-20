<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8' />
		<title>peppermint.json configuration guide - Pepperminty Wiki</title>
	</head>
	<body>
		<h1><img src="https://starbeamrainbowlabs.com/images/logos/peppermint.png" class="logo" /> <code>peppermint.json</code> Configuration Guide</h1>
		<p>This page contains a comprehensive guide to all the settings present in <code>peppermint.json</code>. If anything's missing or unclear, please <a href="https://github.com/sbrl/Pepperminty-Wiki/issues/new">open an issue</a>!</p>
		
		<p><strong>Current Pepperminty Wiki Version: <?php echo(trim(file_get_contents("version"))); ?></strong></p>
		<p><small><em>Note that settings added after the last stable release may not be shown on <a href='https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php'>the version on starbeamrainbowlabs.com</a> until the next release.</em></small></p>
		
		<h2>Type Legend</h2>
		<table>
			<tr>
				<th>Type</th>
				<th>Meaning</th>
			</tr>
			<tr>
				<td><code>text</code></td>
				<td>A string of text, which may or may or may not allow HTML. Consult individual descriptions for more specific information.</td>
			</tr>
			<tr>
				<td><code>textarea</code></td>
				<td>A longer string of text that may or may not allow HTML.</td>
			</tr>
			<tr>
				<td><code>array</code></td>
				<td>An array of strings.</td>
			</tr>
			<tr>
				<td><code>url</code></td>
				<td>A url to a remote resource.</td>
			</tr>
			<tr>
				<td><code>checkbox</code></td>
				<td>A boolean value - i.e. either <code>true</code> or <code>false</code>.</td>
			</tr>
			<tr>
				<td><code>email</code></td>
				<td>An email address.</td>
			</tr>
			<tr>
				<td><code>number</code></td>
				<td>A numerical value that may or may not be floating-point.</td>
			</tr>
			<tr>
				<td><code>usertable</code></td>
				<td>An object that contains the users' usernames and passwords.</td>
			</tr>
			<tr>
				<td><code>nav</code></td>
				<td>A complex array of items that should appear as a navigation bar. Consult the description for <a href='#config_nav_links'><code>nav_links</code></a> for more information.</td>
			</tr>
			<tr>
				<td><code>map</code></td>
				<td>An object that maps a set of values onto another set of values.</td>
			</tr>
		</table>
		
		<hr />
		
		
		<h2>Configuration Guide</h2>
		<p>We have <?php echo(count(array_keys(json_decode(file_get_contents("peppermint.guiconfig.json"), true)))); ?> configurable settings so far!</p>
		
		<table class="main">
			<colgroup>
				<col span="1" style="width: 5%;" />
				<col span="1" style="width: 20%;" />
				<col span="1" style="width: 7%;" />
				<col span="1" style="width: 41%;" />
				<col span="1" style="width: 27%;" />
			</colgroup>
			<thead>
				<tr>
					<th>🔗</th>
					<th>Key</th>
					<th>Type</th>
					<th>Description</th>
					<th>Default Value</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$config = json_decode(file_get_contents("peppermint.guiconfig.json"));
			foreach($config as $config_key => $config_info) {
				echo("<tr id='config_$config_key'>");
				echo("<td class='centre'><a href='#config_".rawurlencode($config_key)."'>🔗</a></td>");
				echo("<td><code>".htmlentities($config_key)."</code></td>");
				echo("<td><code>".htmlentities($config_info->type)."</code></td>");
				echo("<td>$config_info->description</td>");
				echo("<td><pre><code>" . json_encode($config_info->default, true) . "</code></pre></td>");
				echo("</tr>\n");
			}
			?>
			</tbody>
		</table>
		
		<!---------------->
		
		<link rel="stylesheet" href="//starbeamrainbowlabs.com/theme/basic.css" />
		<style>
			:root {
				--bg-main: #eee8f2;
				--bg-table-row: rgba(68, 39, 113, 0.25);
				
				--text-main: #442772;
				--text-link: #9e7eb4;
			}
			@media (prefers-color-scheme: dark) {
				:root {
					--bg-main: hsl(270, 29%, 28%);
					--bg-table-row: hsla(268, 72%, 42%, 0.3);
					
					--text-main: hsl(277, 38%, 89%);
					
				}
			}
			body			{ padding: 1rem; color: var(--text-main); background-color: var(--bg-main); } /* syntaxtic gets confused sometimes */
			
			h1				{ text-align: center;	}
			h2				{ margin-top: 2em;		}
			
			hr				{ margin: 3em 0;		}
			table			{ border-collapse: collapse;	}
			table.main		{ width: 100%; table-layout: fixed;	}
			tr:nth-child(even), thead
							{ background: var(--bg-table-row);	}
			
			pre, code		{ white-space: pre-wrap; word-wrap: break-word; font-size: 1.2em;	}
			
			a 				{ color: var(--text-link);	}
			.largebutton	{ font-size: 2rem;	}
			
			.logo			{ max-width: 1.25em; vertical-align: middle;	}
			.centre			{ text-align: center; }
		</style>
		
	</body>
</html>
