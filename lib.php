<?php  // $Id: lib.php,v 1.8 2007/12/12 00:09:46 stronk7 Exp $
/**
 * Library of functions and constants for module exagames
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the exagames specific functions, needed
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
 * @return int The id of the newly inserted exagames record
 **/
function exagames_add_instance($game)
{
	global $DB;
    $game->timecreated = time();
	if (!$game->introformat) $game->introformat = '';

    if (!$game->id = $DB->insert_record("exagames", $game)) {
        return false;
    }
	if($game->gametype != "gamelabs")
		exagames_after_add_or_update($game);

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
function exagames_update_instance($game)
{
	global $DB;
	
	if($game->gametype == "gamelabs" && !$game->url)
		return false;
	
    $game->timemodified = time();
    $game->id = $game->instance;
	if (!$game->introformat) $game->introformat = '';

    if (!$DB->update_record("exagames", $game)) {
        return false;  // some error occurred
    }

	// Do the processing required after an add or an update.
	if($game->gametype != "gamelabs")
		exagames_after_add_or_update($game);

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
function exagames_delete_instance($id) {
	global $DB;
	
    if (! $game = $DB->get_record("exagames", array("id"=>$id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! $DB->delete_records("exagames", array("id"=>$game->id))) {
        $result = false;
    }

	exagames_grade_item_delete($game);

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
function exagames_user_outline($course, $user, $mod, $exagames) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function exagames_user_complete($course, $user, $mod, $exagames) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in exagames activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function exagames_print_recent_activity($course, $isteacher, $timestart) {
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
function exagames_cron () {
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
 * @param int $exagamesid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function exagames_grades($exagamesid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of exagames. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $exagamesid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function exagames_get_participants($exagamesid) {
    return false;
}

/**
 * This function returns if a scale is being used by one exagames
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $exagamesid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function exagames_scale_used ($exagamesid,$scaleid) {
    $return = false;

    //$rec = get_record("exagames","id","$exagamesid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of exagames.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any exagames
 */
function exagames_scale_used_anywhere($scaleid) {
    if ($scaleid and record_exists('exagames', 'grade', -$scaleid)) {
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
function exagames_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function exagames_uninstall() {
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other exagames functions go here.  Each of them must have a name that 
/// starts with exagames_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.


function exagames_print_tabs($game, $currenttab)
{
	global $CFG, $USER, $DB, $COURSE, $cm;

	$tabs = array();
	$row  = array();
	$inactive = array();
	$activated = array();

    $row[] = new tabobject('show', $CFG->wwwroot.'/mod/exagames/view.php?id='.$cm->id, get_string('show'));

	$context = context_module::instance($cm->id);
	if (has_capability('moodle/course:manageactivities', $context)) {
		$url = $CFG->wwwroot.'/course/mod.php?update='.$cm->id.'&return=1&sesskey='.$USER->sesskey;
		$row[] = new tabobject('edit', $url, get_string('edit'));

		if ($game->quizid && $game->gametype != 'gamelabs') {
			$quizInstanceId = $DB->get_field_sql("SELECT cm.id
						   FROM {$CFG->prefix}course_modules cm,
								{$CFG->prefix}modules md
						   WHERE md.name = 'quiz' AND
								 cm.instance = '".$game->quizid."' AND
								 md.id = cm.module");

			if ($quizInstanceId) {
				$row[] = new tabobject('configure_quiz', $CFG->wwwroot.'/mod/quiz/edit.php?cmid='.$quizInstanceId, get_string('configure_quiz', 'exagames'));
			}
		}
		

		if ($game->gametype != 'tiles' && $game->gametype != 'gamelabs') {
			$row[] = new tabobject('edit', $CFG->wwwroot.'/grade/report/index.php?id='.$COURSE->id, get_string('grades'));
		}
	}

	if (count($row) > 1) {
		$tabs[] = $row;
	}

	print_tabs($tabs, $currenttab, $inactive, $activated);
}


function exagames_load_quiz($quizid) {
	global $CFG, $DB, $USER;

	$quizid = (int)$quizid;

	if (!$quiz = $DB->get_record("quiz", array("id"=>$quizid))) {
		print_error('quiznotfound');
	}

	
	/*if ($quiz->shufflequestions) {
		require_once $CFG->dirroot.'/mod/quiz/locallib.php';
		$quiz->questions = quiz_repaginate($quiz->questions, 0, true);
	}*/

	

	// read questions accoridng to the sorting
	$quiz->questions = array();
	$quiz->sumgrades = 0;
	
	$quizobj = quiz::create($quiz->id, $USER->id);

	$quizobj->preload_questions();
    $quizobj->load_questions();
    foreach ($quizobj->get_questions() as $i => $questiondata)
	{
		$question = question_bank::make_question($questiondata);

		// only load multichoice and truefalse
		if (!($question instanceof qtype_multichoice_base) and !($question instanceof qtype_truefalse_question))
			continue;
			
		$question->maxmark = $questiondata->maxmark ? $questiondata->maxmark : $question->defaultmark;
		
		$quiz->sumgrades += $question->maxmark;
		$quiz->questions[$question->id] = $question;
		
		$questionExtraData = $DB->get_record('exagames_question', array('id'=>$question->id));
		$question->tile_size = $questionExtraData ? $questionExtraData->tile_size : '';
		$question->difficulty = $questionExtraData ? $questionExtraData->difficulty : '';
		$question->content_url = $questionExtraData ? $questionExtraData->content_url : '';
		$question->display_order = $questionExtraData ? $questionExtraData->display_order : '';
	}

	foreach ($quiz->questions as $question) {
        // shuffle answers?
		if (!empty($question->answers) && $quiz->shuffleanswers && !empty($question->shuffleanswers)) {
			$question->answers = swapshuffle_assoc($question->answers);
        }
	}
	//echo "<pre>";
	//var_dump($quiz);
	return $quiz;
}

function exagames_get_game_instance($instanceid)
{
	global $DB;
	$game = $DB->get_record("exagames", array("id" => $instanceid));
	
	// test for correct gametype
	if (!in_array($game->gametype, array_keys(exagames_get_available_games()))) {
		$game->gametype = exagames_get_default_game();
	}
	
	$game->swf = exagames_get_swf_url($game->gametype);
	
	if ($game->gametype == 'tiles') {
		$game->hasHighscore = true;
		$game->width = 940;
		$game->height = 535;
	} else {
		$game->hasHighscore = false;
		$game->width = 800;
		$game->height = 600;
	}
	
	return $game;
}

function exagames_get_available_games($quizzes = true)
{
	if ($quizzes) return array(
		'braingame' => get_string('game_braingame', 'exagames'),
		'tiles' => get_string('game_tiles', 'exagames'),
		'gamelabs' => get_string('game_gamelabs','exagames')
	);
	else return array(
		'gamelabs' => get_string('game_gamelabs','exagames')
	);
}
function exagames_get_default_game()
{
	return 'braingame';
}
function exagames_get_default_swf()
{
	return 'braingame';
}
function exagames_get_swf_url($gametype)
{
	global $CFG;
	
	if (file_exists($CFG->dirroot.'/mod/exagames/swf/'.$gametype.'.swf')) {
		return $CFG->wwwroot.'/mod/exagames/swf/'.$gametype.'.swf';
	} else {
		return $CFG->wwwroot.'/mod/exagames/swf/'.exagames_get_default_swf().'.swf';
	}
}

/**
 * Delete grade item for given game
 *
 * @param object $quiz object
 * @return object quiz
 */
function exagames_grade_item_delete($game)
{
    global $CFG;
    require_once $CFG->libdir.'/gradelib.php';

    return grade_update('mod/exabisgaems', $game->course, 'mod', 'exagames', $game->id, 0, NULL, array('deleted'=>1));
}

/**
 * This function is called at the end of exabisgaems_add_instance
 * and exabisgaems_update_instance, to do the common processing.
 *
 * @param object $game the game object.
 */
function exagames_after_add_or_update($game)
{
    //update related grade item
    exagames_grade_item_update($game);
}

/**
 * Create grade item for given game
 *
 * @param object $quiz object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function exagames_grade_item_update($game, $grades=NULL)
{
    global $CFG;
	require_once $CFG->libdir.'/gradelib.php';

	$quiz = exagames_load_quiz($game->quizid);

	$params = array('itemname'=>$game->name);
	$params['gradetype'] = GRADE_TYPE_VALUE;
	$params['grademax']  = $quiz->sumgrades;
	$params['grademin']  = 0;

	if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

	return grade_update('mod/exagames', $game->course, 'mod', 'exagames', $game->id, 0, $grades, $params);
}

function exagames_quiz_attempt($game, $grade)
{
	global $DB, $COURSE, $USER;
	
	$quiz = exagames_load_quiz($game->quizid);

	$attemptnum = 1 + $DB->get_field_sql('SELECT MAX(attempt) FROM {quiz_attempts} WHERE quiz=? AND userid=?', array($game->quizid, $grade->userid));
	// $uniqueid = 1 + $DB->get_field_sql('SELECT MAX(uniqueid) FROM {quiz_attempts}');
	
	//preview made from teacher or higher has always attemptnum = 1, so this attemptnum is reserved
	$context_course = get_context_instance(CONTEXT_COURSE, $COURSE->id);
	$roles = get_user_roles($context_course, $USER->id);
	
	foreach($roles as $role){
		if($role->roleid <= 4 && $attemptnum == 1)	$attemptnum = 2;
	}

	// copied from question/engine/datalib.php: public function insert_questions_usage_by_activity(question_usage_by_activity $quba)
	// create a new question usage, and use that id to create a quiz attempt
	// then we can delete the question usage.
	$cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course, false, MUST_EXIST);
	$context = context_module::instance($cm->id);
	$record = new stdClass();
	$record->contextid = $context->id;
	$record->component = 'mod_quiz';
	$record->preferredbehaviour = 'deferredfeedback';
	$newid = $DB->insert_record('question_usages', $record);
	// $DB->delete_records("question_usages", array("id"=>$newid));

	
	$attempt = new StdClass;
	$attempt->quiz = $game->quizid;
	$attempt->userid = $grade->userid;
	$attempt->attempt = $attemptnum;
	$attempt->sumgrades = $grade->rawgrade;
	$attempt->timestart = time();
	$attempt->timefinish = time();
	$attempt->timemodified = time();
	$attempt->layout = '';
	$attempt->state = 'finished';
	$attempt->uniqueid = $newid;
	
	$DB->insert_record('quiz_attempts', $attempt);
	
	$quiz_grade = new StdClass;
	$quiz_grade->quiz = $game->quizid;
	$quiz_grade->userid = $grade->userid;
	$quiz_grade->grade = $grade->rawgrade / $quiz->sumgrades * $quiz->grade;
	$quiz_grade->timemodified = time();
	
	if ($db_quiz_grade = $DB->get_record('quiz_grades', array('quiz'=>$game->quizid, 'userid'=>$grade->userid))) {
		$quiz_grade->id = $db_quiz_grade->id;
		$DB->update_record('quiz_grades', $quiz_grade);
	} else {
		$DB->insert_record('quiz_grades', $quiz_grade);
	}
}

		
//http://localhost/mod/exagames/view.php?id=10&responses[5]=false&responses[6]=16
function exagames_calc_grade_from_responses($quiz, $responses)
{
	$overallGrade = 0;

	foreach ($quiz->questions as $question) {
		if (isset($responses[$question->id])) {
			$response = $responses[$question->id];
			if ($question instanceof qtype_multichoice_single_question) {
				$fraction = isset($question->answers[$response]) ? $question->answers[$response]->fraction : 0;
			
			} elseif ($question instanceof qtype_multichoice_multi_question) {
				$response = explode(',', $response);

				$fraction = 0;
				foreach ($response as $ansid) {
					if (isset($question->answers[$ansid]))
						$fraction += $question->answers[$ansid]->fraction;
					
				}

				$fraction = min(max(0, $fraction), 1.0);

			} elseif ($question instanceof qtype_truefalse_question) {
				$fraction = (int) ($question->rightanswer == (bool)$response);
			
			} else {
				die('wrong question type');
			}
			
			$overallGrade += $fraction * $question->maxmark;
		}
	}

	return $overallGrade;
}

function exagames_html_to_text($text)
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

function exagames_get_string($string, $library = null)
{
	$manager = get_string_manager();

	if ($manager->string_exists($string, "exagames"))
		return $manager->get_string($string, 'exagames');

	return $manager->get_string($string, $library);
}
