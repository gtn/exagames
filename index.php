<?php 

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);   // course
$PAGE->set_url('/mod/webgl/index.php', array('id'=>$id));
if (! $course = $DB->get_record("course", array("id"=>$id))) {
	print_error("Course ID is incorrect");
}

require_login($course->id);

add_to_log($course->id, "webgl", "view all", "index.php?id=$course->id", "");


/// Get all required strings

$strwebgls = get_string("modulenameplural", "webgl");
$strwebgl  = get_string("modulename", "webgl");


/// Print the header

$navlinks = array();
$navlinks[] = array('name' => $strwebgls, 'link' => '', 'type' => 'activity');

$PAGE->navbar->add($course->fullname, new moodle_url('', array('id' => $course->id)));


/// Get all the appropriate data

if (! $webgls = get_all_instances_in_course("webgl", $course)) {
	die;
}

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname  = get_string("name");
$strweek  = get_string("week");
$strtopic  = get_string("topic");

$table = new html_table();

if ($course->format == "weeks") {
	$table->head  = array ($strweek, $strname);
	$table->align = array ("center", "left");
} else if ($course->format == "topics") {
	$table->head  = array ($strtopic, $strname);
	$table->align = array ("center", "left", "left", "left");
} else {
	$table->head  = array ($strname);
	$table->align = array ("left", "left", "left");
}

foreach ($webgls as $webgl) {
	if (!$webgl->visible) {
		//Show dimmed if the mod is hidden
		$link = "<a class=\"dimmed\" href=\"view.php?id=$webgl->coursemodule\">$webgl->name</a>";
	} else {
		//Show normal if the mod is visible
		$link = "<a href=\"view.php?id=$webgl->coursemodule\">$webgl->name</a>";
	}

	if ($course->format == "weeks" or $course->format == "topics") {
		$table->data[] = array ($webgl->section, $link);
	} else {
		$table->data[] = array ($link);
	}
}

echo "<br />";

// Display the table.
echo html_writer::table($table);

// Finish the page
echo $OUTPUT->footer();
