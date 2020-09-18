<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 exabis internet solutions <info@exabis.at>
 *
 *  You can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This module is based on the Collaborative Moodle Modules from
 *  NCSA Education Division (http://www.ncsa.uiuc.edu)
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once("inc.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or


$out = array();
 global $COURSE, $CFG, $DB, $USER;
$img_files = array();
$responses = function_exists('optional_param_array') ? optional_param_array('responses', array(), PARAM_TEXT) : optional_param('responses', array(), PARAM_RAW);
if ($id) {
	if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
		print_error("Course Module ID was incorrect");
	}



	if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
		print_error("Course is misconfigured");
	}


	if (! $game = precheck_get_game_instance($cm->instance)) {
		error("Game not found");
	}

} else {
		print_error("Course module is incorrect");
}

require_login($course->id);

	


	//--------------------------------------------------------------------pool3 action
	$json_string = file_get_contents('./result.json');
	
	// problem mit umlauten
	$json_a = json_decode($json_string, true);
	
	$updateGrade = new StdClass;
	$updateGrade->rawgrade = $json_a['TrainingsResultInPercent'];
	$updateGrade->feedback = "Attempts: ".$json_a['Attempts']."\nTime needed: ". $json_a['TotalTimeInSeconds'];
	$updateGrade->userid = $USER->id;
	
	precheck_grade_item_update($game, $updateGrade);
	precheck_save_data($json_string, $cm->instance);
	
	




$context = get_context_instance(CONTEXT_COURSE, $game->course);



add_to_log($course->id, "precheck", "view", "view.php?id=$cm->id", "$game->id");

/// Print the page header
$strexagamess = get_string("modulenameplural", "precheck");
$strexagames  = get_string("modulename", "precheck");

$navlinks = array();
$navlinks[] = array('name' => $strexagamess, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($game->name), 'link' => '', 'type' => 'activityinstance');


$PAGE->set_url($_SERVER['REQUEST_URI']);
$PAGE->requires->js('/mod/precheck/js/swfobject.js', true);

$stringman = get_string_manager();
$strings = $stringman->load_component_strings('mod_precheck', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'mod_precheck');

echo $OUTPUT->header();

//$context = get_context_instance(CONTEXT_COURSE, $game->course);
// $context = context_module::instance($cm->id);

precheck_print_tabs($game, 'show');

/// Print the main part of the page


	$url = new moodle_url($_SERVER['PHP_SELF'], array('id'=>$id));
$flashvars = array(
	'gameurl' => $url->out(),
	'courseurl' => $CFG->wwwroot.'/course/view.php?id='.$course->id,
);
$gametype = $game->gametype;

?>

 <script src="html5/js/jquery.min.js"></script>
 <script src="html5/js/phaser.js"></script>

<script type="text/javascript">
	var flashvars = <?php echo json_encode($flashvars) ?>;
	var gameType = <?php echo json_encode($gametype) ?>;
	//var flashvars = <?php echo json_encode(array_map('urlencode', $flashvars)) ?>;
	console.log(gameType);
	var params = {};
	var attributes = {};
	//swfobject.embedSWF(<?php echo json_encode($game->swf); ?>, "GameContent", <?php echo $game->width; ?>, <?php echo $game->height; ?>, "9.0.0", false, flashvars, params, attributes);
	if(gameType == 'precheck') {
		$(document).ready(function(){
				$( "#GameContent" ).empty();
				$( "#GameContent" ).load('./html5/precheck/index.html');
		});
	}

	</script>
<div id="Game" style="width: 1200px; margin: 0 auto;">
	<div id="GameContent" style="width: 1200px; margin: 0 auto;">
		<a href="http://www.adobe.com/go/getflashplayer" style="display: block; padding: 40px; text-align: center;">
			<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
		</a>
	</div>
</div>

<?php

/// Finish the page
echo $OUTPUT->footer();
