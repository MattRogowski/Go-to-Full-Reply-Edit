<?php
/**
 * Go to Full Reply/Edit 1.1

 * Copyright 2016 Matthew Rogowski

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 ** http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
**/

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("global_start", "gotofull_reply");
$plugins->add_hook("showthread_start", "gotofull_reply_button");
$plugins->add_hook("editpost_end", "gotofull_edit");

function gotofull_info()
{
	return array(
		"name" => "Go to Full Reply/Edit",
		"description" => "Allows you to quickly go from quick reply to full reply or quick edit to full edit whilst saving any text you had previously entered.",
		"website" => "https://github.com/MattRogowski/Go-to-Full-Reply-Edit",
		"author" => "Matt Rogowski",
		"authorsite" => "https://matt.rogow.ski",
		"version" => "1.1",
		"compatibility" => "16*,18*",
		"guid" => "43a080234dcf1acbb6e38dc0dd843279"
	);
}

function gotofull_activate()
{
	global $db;
	
	require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
	
	gotofull_deactivate();
	
	$templates = array();
	$templates[] = array(
		"title" => "gotofull_reply",
		"template" => "<input type=\"submit\" class=\"button\" name=\"gotofullreply\" value=\"{\$lang->gotofull_reply}\" tabindex=\"3\" />"
	);
	foreach($templates as $template)
	{
		$insert = array(
			"title" => $db->escape_string($template['title']),
			"template" => $db->escape_string($template['template']),
			"sid" => "-1",
			"version" => "1800",
			"dateline" => TIME_NOW
		);
		$db->insert_query("templates", $insert);
	}
	
	find_replace_templatesets("showthread_quickreply", "#".preg_quote('<input type="submit" class="button" name="previewpost" value="{$lang->preview_post}" tabindex="3" />')."#i", '<input type="submit" class="button" name="previewpost" value="{$lang->preview_post}" tabindex="3" />{$gotofull_reply}');
}

function gotofull_deactivate()
{
	global $db;
	
	require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
	
	$templates = array(
		"gotofull_reply"
	);
	$templates = "'" . implode("','", $templates) . "'";
	$db->delete_query("templates", "title IN ({$templates})");
	
	find_replace_templatesets("showthread_quickreply", "#".preg_quote('{$gotofull_reply}')."#i", '', 0);
}

function gotofull_reply_button()
{
	global $lang, $templates, $gotofull_reply;
	
	$lang->load("gotofull");
	
	eval("\$gotofull_reply = \"".$templates->get('gotofull_reply')."\";");

	$script = "<script>
	jQuery(document).ready(function() {
		jQuery('body').on('click', '.postbit_edit', function() {
			quick_edit_id = jQuery(this).next('.popup_menu').find('.quick_edit_button').attr('id');
			post_id = quick_edit_id.match(/\d+$/)[0];
			jQuery('#'+quick_edit_id).click(function() {
				that = this;
				setTimeout(function() {
					form = jQuery(that).parents('.post').find('form');
					if(!jQuery(form).find('.gotofulledit').length)
					{
						button = jQuery('<button/>', {'type':'submit','text':'".$lang->gotofull_reply."','name':'gotofulledit','class':'gotofulledit','value':'1'});
						jQuery(form).find('button:last').after(button);
						button.on('click', function() {
							jQuery(form).attr('method','post').attr('action','editpost.php?pid='+post_id).unbind('submit').find('.gotofulledit').click();
						});
					}
				}, 10);
			});
		});
	});
	</script>";
	eval("\$gotofull_reply .= \"".$script."\";");
}

function gotofull_reply()
{
	global $mybb;
	
	if(THIS_SCRIPT == "newreply.php" && $mybb->input['action'] == "do_newreply" && $mybb->input['method'] == "quickreply" && $mybb->input['gotofullreply'])
	{
		$mybb->input['action'] = "newreply";
	}
}

function gotofull_edit()
{
	global $mybb, $message;
	
	if($mybb->input['gotofulledit'])
	{
		$message = $mybb->input['value'];
	}
}
?>