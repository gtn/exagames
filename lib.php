<?php  // $Id: lib.php,v 1.8 2007/12/12 00:09:46 stronk7 Exp $
/**
 * Library of functions and constants for module precheck
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the precheck specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted precheck record
 **/
function precheck_add_instance($game)
{
	global $DB;
    $game->timecreated = time();
	if (!$game->introformat) $game->introformat = '';

    if (!$game->id = $DB->insert_record("precheck", $game)) {
        return false;
    }

	return $game->id;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function precheck_update_instance($game)
{
    global $DB, $CFG, $COURSE;
	
	if($game->gametype == "gamelabs" && !$game->url)
		return false;
	
    $game->timemodified = time();
    $game->id = $game->instance;
	if (!$game->introformat) $game->introformat = '';

    if (!$DB->update_record("precheck", $game)) {
        return false;  // some error occurred
    }
    
    
    $fileInfo = $DB->get_record_sql("
				SELECT itemid
				FROM {files}
				WHERE contextid = ? AND filename = 'test.zip'
                ORDER BY timecreated DESC LIMIT 0, 1",
        array(5));
    
    $fs = get_file_storage();
        $fileinfo = array(
            'contextid' => 5,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => intval($fileInfo->itemid),
            'filepath' => '/',
            'filename' => 'test.zip');
        
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
        $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'],
        $fileinfo['filename']);
    $localfilename = 'test.zip';
    $pathname = $CFG->tempdir . '/' . $localfilename;
    
    $file->copy_content_to($pathname);
    
    $zip = new ZipArchive;
    $res = $zip->open($pathname);
    if ($res === TRUE) {
        $zip->extractTo($CFG->tempdir .'/prechecks');
        $zip->close();
        $contents = scandir($CFG->tempdir . '/prechecks',1);
        directory_copy($CFG->tempdir . '/prechecks/'. $contents[0], $CFG->dirroot . '/../moodle/mod/precheck/html5/'. $contents[0]);
        rrmdir($CFG->tempdir . '/prechecks');
    }

    
    //extract_zip_subdir($pathname, "checkers",  $CFG->tempdir, $CFG->tempdir);
    
    
    if (!$file) {
        return false; // The file does not exist.
    }
    


		precheck_after_add_or_update($game);

    return true;
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function precheck_delete_instance($id) {
	global $DB;
	
    if (! $game = $DB->get_record("precheck", array("id"=>$id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! $DB->delete_records("precheck", array("id"=>$game->id))) {
        $result = false;
    }

    precheck_grade_item_delete($game);

	return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function precheck_user_outline($course, $user, $mod, $precheck) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function precheck_user_complete($course, $user, $mod, $precheck) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in precheck activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function precheck_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function precheck_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int precheckid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function precheck_grades($precheckid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of precheck. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $precheckid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function precheck_get_participants($precheckid) {
    return false;
}

/**
 * This function returns if a scale is being used by one precheck
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $precheckid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function precheck_scale_used ($precheckid,$scaleid) {
    $return = false;
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of precheck.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any precheck
 */
function precheck_scale_used_anywhere($scaleid) {
    if ($scaleid and record_exists('precheck', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function precheck_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function precheck_uninstall() {
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other precheck functions go here.  Each of them must have a name that 
/// starts with precheck_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.


function precheck_print_tabs($game, $currenttab)
{
	global $CFG, $USER, $DB, $COURSE, $cm;

	$tabs = array();
	$row  = array();
	$inactive = array();
	$activated = array();

    $row[] = new tabobject('show', $CFG->wwwroot.'/mod/precheck/view.php?id='.$cm->id, get_string('show'));

	$context = context_module::instance($cm->id);
	if (has_capability('moodle/course:manageactivities', $context)) {
		$url = $CFG->wwwroot.'/course/mod.php?update='.$cm->id.'&return=1&sesskey='.$USER->sesskey;
		$row[] = new tabobject('edit', $url, get_string('edit'));

		

	    $row[] = new tabobject('edit', $CFG->wwwroot.'/grade/report/index.php?id='.$COURSE->id, get_string('grades'));
	}
	
	$row[] = new tabobject('result', $CFG->wwwroot.'/mod/precheck/result.php?cmid='.$cm->id, get_string('result', 'precheck'));

	if (count($row) > 1) {
		$tabs[] = $row;
	}

	print_tabs($tabs, $currenttab, $inactive, $activated);
}




function precheck_get_game_instance($instanceid)
{
	global $DB;
	$game = $DB->get_record("precheck", array("id" => $instanceid));
	

		$game->hasHighscore = false;
		$game->width = 800;
		$game->height = 600;

	
	return $game;
}

/**
 * Delete grade item for given game
 *
 * @param object $quiz object
 * @return object quiz
 */
function precheck_grade_item_delete($game)
{
    global $CFG;
    require_once $CFG->libdir.'/gradelib.php';

    return grade_update('mod/precheck', $game->course, 'mod', 'precheck', $game->id, 0, NULL, array('deleted'=>1));
}

/**
 * This function is called at the end of precheck_add_instance
 * and precheck_update_instance, to do the common processing.
 *
 * @param object $game the game object.
 */
function precheck_after_add_or_update($game)
{
    //update related grade item
    precheck_grade_item_update($game);
}

/**
 * Create grade item for given game
 *
 * @param object $quiz object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function precheck_grade_item_update($game, $grades=NULL)
{
    global $CFG;
	require_once $CFG->libdir.'/gradelib.php';


	$params = array('itemname'=>$game->name);
	$params['gradetype'] = GRADE_TYPE_VALUE;
	$params['grademax']  = 100;
	$params['grademin']  = 0;

	if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

	return grade_update('mod/precheck', $game->course, 'mod', 'precheck', $game->id, 0, $grades, $params);
}



function precheck_html_to_text($text)
{
	$text = str_replace(array("\r", "\n"), '', $text);
	$text = preg_replace("!<(p|br)[^a-z]*>!iU", "\n", $text);
	$text = strip_tags($text);
	$text = trim($text);

	return $text;
}

if (!function_exists('printr')) {
	function printr($var)
	{
		echo "<pre>";
		if (is_array($var)) {
			print_r($var);
		} elseif (is_object($var)) {
			print_r($var);
			echo 'methods of class '.get_class($var).': ';
			print_r(get_class_methods($var));
		} else {
			var_dump($var);
		}
		echo "</pre>";
	}
}

function precheck_get_string($string, $library = null)
{
	$manager = get_string_manager();

	if ($manager->string_exists($string, "precheck"))
		return $manager->get_string($string, 'precheck');

	return $manager->get_string($string, $library);
}

function precheck_save_data($string, $itemid){
    global $USER, $DB;
    
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->itemid = $itemid;
    $record->data = $string;
    $DB->delete_records('precheck_data', array("userid"=>$USER->id, "itemid"=>$itemid));
    $DB->insert_record('precheck_data', $record);
    
}


function directory_copy($source, $destionation) {
    $dir = opendir($source);
    @mkdir($destionation);
    // Loop through the files in source directory
    while ($file = readdir($dir)) {
        if (($file != '.') && ($file != '..')) {
            if ( is_dir($source.'/'.$file)) {
                directory_copy($source . '/' . $file, $destionation . '/' . $file);
            } else {
                copy($source.'/'.$file, $destionation.'/'.$file);
            }
        }
    }
    closedir($dir);
}

function rrmdir($source, $removeOnlyChildren = false)
{
    if(empty($source) || file_exists($source) === false)
    {
        return false;
    }
    
    if(is_file($source) || is_link($source))
    {
        return unlink($source);
    }
    
    $files = new \RecursiveIteratorIterator
    (
        new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
        );
    //fileInfo (SplFileInfo)
    foreach($files as $fileinfo)
    {
        if($fileinfo->isDir())
        {
            if(rrmdir($fileinfo->getRealPath()) === false)
            {
                return false;
            }
        }
        else
        {
            if(unlink($fileinfo->getRealPath()) === false)
            {
                return false;
            }
        }
    }
    
    if($removeOnlyChildren === false)
    {
        return rmdir($source);
    }
    
    return true;
}

function getGames(){
    global $CFG;
    $contents = scandir($CFG->dirroot . '/../moodle/mod/precheck/html5/',1);
    $retContent = array();
    foreach($contents as $content){
        if($content != "css" && $content != "js" && $content != "." && $content != ".."){
            $retContent[$content] = $content;
        }
    }
    return $retContent;
}

