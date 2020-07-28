<?php 

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);   // course
$PAGE->set_url('/mod/precheck/index.php', array('id'=>$id));
if (! $course = $DB->get_record("course", array("id"=>$id))) {
	print_error("Course ID is incorrect");
}

require_login($course->id);

add_to_log($course->id, "precheck", "view all", "index.php?id=$course->id", "");


/// Get all required strings

$strprechecks = get_string("modulenameplural", "precheck");
$strprecheck  = get_string("modulename", "precheck");


/// Print the header

$navlinks = array();
$navlinks[] = array('name' => $strprechecks, 'link' => '', 'type' => 'activity');

$PAGE->navbar->add($course->fullname, new moodle_url('', array('id' => $course->id)));


/// Get all the appropriate data

if (! $prechecks = get_all_instances_in_course("precheck", $course)) {
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

foreach ($prechecks as $precheck) {
	if (!$precheck->visible) {
		//Show dimmed if the mod is hidden
		$link = "<a class=\"dimmed\" href=\"view.php?id=$precheck->coursemodule\">$precheck->name</a>";
	} else {
		//Show normal if the mod is visible
		$link = "<a href=\"view.php?id=$precheck->coursemodule\">$precheck->name</a>";
	}

	if ($course->format == "weeks" or $course->format == "topics") {
		$table->data[] = array ($precheck->section, $link);
	} else {
		$table->data[] = array ($link);
	}
}

echo "<br />";

// Display the table.
echo html_writer::table($table);

// Finish the page
echo $OUTPUT->footer();
