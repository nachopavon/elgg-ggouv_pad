<?php
/**
 * View for pad object
 *
 * @package ElggPad
 *
 * @uses $vars['entity']    The pad object
 * @uses $vars['full_view'] Whether to display the full view
 */


$full = elgg_extract('full_view', $vars, FALSE);
$etherpad = elgg_extract('entity', $vars, FALSE);
$timeslider = elgg_extract('timeslider', $vars, FALSE);

if (!$etherpad || !elgg_instanceof($etherpad, 'object', 'etherpad')) {
	return TRUE;
}

$etherpad = new ElggPad($etherpad->guid);

// pages used to use Public for write access
if ($etherpad->write_access_id == ACCESS_PUBLIC) {
	// this works because this metadata is public
	$etherpad->write_access_id = ACCESS_LOGGED_IN;
}

//link to owners pages only if pages integration is enabled. Else link to owners pads. 
$editor = get_entity($etherpad->owner_guid);
$editor_link = elgg_view('output/url', array(
	'href' => "pad/owner/$editor->username",
	'text' => $editor->name,
	'is_trusted' => true,
));

$date = elgg_view_friendly_time($etherpad->time_created);
$editor_text = elgg_echo('pages:strapline', array($date, $editor_link));
$tags = elgg_view('output/tags', array('tags' => $etherpad->tags));
$categories = elgg_view('output/categories', $vars);

$comments_count = $etherpad->countComments();
//only display if there are commments
if ($comments_count != 0) {
	$text = elgg_echo("comments") . " ($comments_count)";
	$comments_link = elgg_view('output/url', array(
		'href' => $etherpad->getURL() . '#comments',
		'text' => $text,
		'is_trusted' => true,
	));
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('etherpad', array(
	'entity' => $vars['entity'],
	'handler' => 'etherpad',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-entity elgg-menu-hz',
));

$subtitle = "$editor_text $comments_link $categories";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full) {
	try {
		$body .= elgg_view('output/iframe', array(
			'value' => $etherpad->getPadPath($timeslider),
			'class' => 'etherpad mtm',
			'width' => '100%',
			'height' => '400px',
			'frameborder' => '0'
		));
	} catch(Exception $e) {
		$body .= $e->getMessage();
	}
	$params = array(
		'entity' => $etherpad,
		'metadata' => $metadata,
		'title' => false,
		'subtitle' => $subtitle,
		'tags' => $tags,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	echo elgg_view('object/elements/full', array(
		'entity' => $etherpad,
		'summary' => $summary,
		'body' => $body,
	));

} else {
	// brief view

	$excerpt = elgg_get_excerpt($etherpad->description);

	$params = array(
		'entity' => $etherpad,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'tags' => $tags,
		'content' => $excerpt,
	);
	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($etherpad_icon, $list_body);
}
