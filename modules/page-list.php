<?php
register_module([
	"name" => "Page list",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that lists all the pages in the index along with their metadata.",
	"id" => "page-list",
	"code" => function() {
		add_action("list", function() {
			global $pageindex, $settings;
			
			$title = "All Pages";
			$content = "	<h1>$title on $settings->sitename</h1>";
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			
			$content .= generate_page_list(array_keys($sorted_pageindex));
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		add_action("list-tags", function() {
			global $pageindex, $settings;
			
			if(!isset($_GET["tag"]))
			{
				// Render a list of all tags
				$all_tags = [];
				foreach($pageindex as $entry)
				{
					if(!isset($entry->tags)) continue;
					foreach($entry->tags as $tag)
					{
						if(!in_array($tag, $all_tags)) $all_tags[] = $tag;
					}
				}
				
				$content = "<h1>All tags</h1>
				<ul class='tag-list'>\n";
				foreach($all_tags as $tag)
				{
					$content .= "			<li><a href='?action=list-tags&tag=" . rawurlencode($tag) . "' class='mini-tag'>$tag</a></li>\n";
				}
				$content .= "</ul>";
				
				exit(page_renderer::render("All tags - $settings->sitename", $content));
			}
			$tag = $_GET["tag"];
			
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			
			$pagelist = [];
			foreach($pageindex as $pagename => $pagedetails)
			{
				if(empty($pagedetails->tags)) continue;
				if(in_array($tag, $pagedetails->tags))
					$pagelist[] = $pagename;
			}
			
			$content = "<h1>$tag</h1>\n";
			$content .= generate_page_list($pagelist);
			
			exit(page_renderer::render("$tag - Page List - $settings->sitename", $content));
		});
	}
]);

function generate_page_list($pagelist)
{
	global $pageindex;
	
	$result = "<table class='page-list'>
		<tr>
			<th>Page Name</th>
			<th>Size</th>
			<th>Last Editor</th>
			<th>Last Edit Time</th>
			<th>Tags</th>
		</tr>\n";
	foreach($pagelist as $pagename)
	{
		// Construct a list of tags that are attached to this page ready for display
		$tags = "";
		// Make sure that this page does actually have some tags first
		if(isset($pageindex->$pagename->tags))
		{
			foreach($pageindex->$pagename->tags as $tag)
			{
				$tags .= "<a href='?action=list-tags&tag=" . rawurlencode($tag) . "' class='mini-tag'>$tag</a>, ";
			}
			$tags = substr($tags, 0, -2); // Remove the last ", " from the tag list
		}
		
			$result .= "\t\t<tr>
			<td><a href='index.php?page=$pagename'>$pagename</a></td>
			<td>" . human_filesize($pageindex->$pagename->size) . "</td>
			<td>" . $pageindex->$pagename->lasteditor . "</td>
			<td>" . human_time_since($pageindex->$pagename->lastmodified) . " <small>(" . date("l jS \of F Y \a\\t h:ia T", $pageindex->$pagename->lastmodified) . ")</small></td>
			<td>$tags</td>
	
	</tr>\n";
	}
	$result .= "	</table>";
	
	return $result;
}

?>
