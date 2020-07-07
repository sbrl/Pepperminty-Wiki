<?php
register_module([
	"name" => "Redirect pages",
	"version" => "0.3.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds support for redirect pages. Uses the same syntax that Mediawiki does.",
	"id" => "feature-redirect",
	"code" => function() {
		global $settings;
		
		register_save_preprocessor("update_redirect_metadata");
		
		$help_html = "<p>$settings->sitename supports redirect pages. To create a redirect page, enter something like <code># REDIRECT [[pagename]]</code> on the first line of the redirect page's content. This <em>must</em> appear as the first line of the page, with no whitespace before it. You can include content beneath the redirect if you want, too (such as a reason for redirecting the page).</p>";
		if($settings->redirect_absolute_enable == true) $help_html .= "<p>$settings->sitename also has absolute redirects enabled (e.g. if you want to make your main page point to the all pages list). To make a page an absolute redirect page, enter the following on the first line: <code># REDIRECT [all pages](?action=list)</code>. This example will cause the page to become a redirect to the all pages list. Of course, you  can change the <code>?action=list</code> bit to be any regular URL you like (relative or absolute)</p>";
		
		// Register a help section
		add_help_section("25-redirect", "Redirect Pages", $help_html);
	}
]);

/**
 * Updates the metadata associated with redirects in the pageindex entry
 * specified utilising the provided page content.
 * @package	redirect
 * @param	object	$index_entry	The page index entry object to update.
 * @param	string	$pagedata		The page content to operate on.
 */
function update_redirect_metadata(&$index_entry, &$pagedata) {
	$matches = [];
	if(preg_match("/^# ?REDIRECT ?\[\[([^\]]+)\]\]/i", $pagedata, $matches) === 1)
	{
		//error_log("matches: " . var_export($matches, true));
		// We have found a redirect page!
		// Update the metadata to reflect this.
		$index_entry->redirect = true;
		$index_entry->redirect_target = $matches[1];
		$index_entry->redirect_absolute = false;
	}
	// We don't disable absolute redirects here, because it's the view action that processes them - we only register them here. Checking here would result in pages that are supposed to be redirects being missed if redirect_absolute_enable is turned on after such a page is created.
	elseif(preg_match("/^# ?REDIRECT ?\[[^\]]+\]\(([^)]+)\)/", $pagedata, $matches) === 1) {
		$index_entry->redirect = true;
		$index_entry->redirect_target = $matches[1];
		$index_entry->redirect_absolute = true;
	}
	else
	{
		// This page isn't a redirect. Unset the metadata just in case.
		if(isset($index_entry->redirect))
			unset($index_entry->redirect);
		if(isset($index_entry->redirect_target))
			unset($index_entry->redirect_target);
		if(isset($index_entry->redirect_absolute))
			unset($index_entry->redirect_absolute);
	}
}

?>
