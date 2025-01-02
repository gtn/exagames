<?php
/**
 * This page prints a particular instance of exagames
 *
 * @author
 * @version $Id: view.php,v 1.6 2007/09/03 12:23:36 jamiesensei Exp $
 * @package exagames
 **/

require_once("inc.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // exagames ID
$action  = optional_param('action', '', PARAM_TEXT);

// Moodle has many Notice|Deprecation errors in the own code, so disable PHP errors if it is an ajax requests for Flash or other...
// useful id the Moodle is configured in 'developer' mode
if (in_array($action, ['data', 'translations', ])) {
    ini_set('display_errors', '0');
    error_reporting(E_ERROR);
    define('NO_DEBUG_DISPLAY', true);
}

// from moodle 2.2 on we have to use optional_param_array, optional_param won't accept arrays
$out = array();
global $COURSE, $CFG, $DB, $USER, $PAGE;
$img_files = array();
$responses = function_exists('optional_param_array') ? optional_param_array('responses', array(), PARAM_TEXT) : optional_param('responses', array(), PARAM_RAW);

if ($id) {
	if (! $cm = $DB->get_record("course_modules", array("id" => $id))) {
		exagames_print_error("Course Module ID was incorrect");
	}



	if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
		exagames_print_error("Course is misconfigured");
	}


	if (! $game = exagames_get_game_instance($cm->instance)) {
        exagames_print_error("Game not found");
	}

} else {
    $exagame = $DB->get_record('exagames', ['id' => @$PAGE->cm->instance]);
	if (! $game = $DB->get_record("exagames", array("id" => $exagame->id))) {
		exagames_print_error("Course module is incorrect");
	}
	if (! $course = $DB->get_record("course", array("id" => $game->course))) {
		exagames_print_error("Course is misconfigured");
	}
	if (! $cm = get_coursemodule_from_instance("exagames", $game->id, $course->id)) {
		exagames_print_error("Course Module ID was incorrect");
	}
}

require_login($course->id);

if ($game->gametype != "gamelabs") {
    $quiz = exagames_load_quiz($game->quizid);
}


if ($action == 'translations') {
	require dirname(__FILE__).'/lib/Pro/SimpleXMLElement.php';

	$xmlResult = Pro_SimpleXMLElement::create('translations');
	$strings = array('true', 'false', 'question', 'startagain', 'mark');
	foreach ($strings as $string) {
		$xmlResult->$string = exagames_get_string($string, 'quiz');
	}

	$xmlResult->continue = exagames_get_string('continue');
	$xmlResult->savingdata = exagames_get_string('savingdata');

	$xmlResult->score = exagames_get_string('score', 'search');
	$xmlResult->returntocourse = exagames_get_string('returntocourse', 'lesson');

	header('Content-Type: text/xml; charset=utf-8');
	echo $xmlResult->asPrettyXml();

	exit;
}

if ($action == 'data') {
	// flash handlers
	$isguest = isguestuser();

    //not working at the moment
	if (!isguestuser() && $responses) {
		// save game responses
		
		// calc grade
		
		$grade = exagames_calc_grade_from_responses($quiz, $responses);
		require_once $CFG->dirroot.'/mod/quiz/locallib.php';
		$attemptgrade = quiz_rescale_grade($grade, $quiz);

		// save grade
		$updateGrade = new StdClass;
		$updateGrade->rawgrade = $grade;
		$updateGrade->userid = $USER->id;
        echo "<script>alert('$grade')</script>";
		exagames_grade_item_update($game, $updateGrade);
		exagames_quiz_attempt($game, $updateGrade);

		/*
		<result>
			<score percent="0.5" sumgrades="44" grade="22" />
			<feedback>text</feedback>
		</result>
		*/

		require dirname(__FILE__).'/lib/Pro/SimpleXMLElement.php';

		$xmlResult = Pro_SimpleXMLElement::create('result');
		$xmlResult->addChild('score')->setAttributes(array(
			'percent' => $grade / $quiz->sumgrades * 100,
			'sumgrades' => $quiz->sumgrades,
			'grade' => $grade
		));
		// Todo: Feedback

		$context = context_block::instance(CONTEXT_MODULE, $cm->id);
		$xmlResult->feedback = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
        echo "<script>console.log('$xmlResult->feedback')</script>";

		$json = json_encode($xmlResult);
		$xmlArr = json_decode($json,TRUE);
		
		$scoreDb = new stdClass();
		$scoreDb->userid = $USER->id;
		$scoreDb->gameid = $game->id;
		$scoreDb->gametype = $game->gametype;
		$scoreDb->score = $xmlArr['score']['@attributes']['percent'];
        $scoreDb->time = time();
		
		$DB->insert_record('exagames_scores', $scoreDb);
		
		header('Content-Type: text/xml; charset=utf-8');
		echo $xmlResult->asPrettyXML();

		exit;

	} elseif (!isguestuser() && $game->hasHighscore && (($score = optional_param('score', -9999, PARAM_FLOAT)) != -9999)) {

		$scoreDb = new stdClass();
		$scoreDb->userid = $USER->id;
		$scoreDb->gameid = $game->id;
		$scoreDb->gametype = $game->gametype;
		$scoreDb->score = intval($score * 100);
		$scoreDb->time = time();

		$DB->insert_record('exagames_scores', $scoreDb);

        // Update completion state.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC
	        && $game->completionminscore
            ) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

		echo 'ok=1';

		exit;
	} else {
		// output gamedata as xml for flash

		require dirname(__FILE__).'/lib/Pro/SimpleXMLElement.php';

		$xmlQuiz = Pro_SimpleXMLElement::create('quiz');
		$xmlQuiz->setAttribute('sumgrades', $quiz->sumgrades);

		$xmlUser = $xmlQuiz->addChild('user')->setAttribute('id', $USER->id);
		$xmlUser->addChild('name', fullname($USER));
		$xmlQuiz->intro = exagames_html_to_text(@$quiz->intro);

		if ($game->gametype == 'tiles') {
			$xmlQuiz->rules = null;
			$xmlQuiz->rules->addCData(get_string("game_tiles_rules", "exagames"));
		}

		$xmlQuestions = $xmlQuiz->addChild('questions');

		// randomize questions, regarding game settings and module settings
        $randomizequestions = (isset($game->randomizequestions) ? $game->randomizequestions : -1);
		if ($randomizequestions == -1) {
            $randomizequestions = (isset($CFG->exagames_randomize_questions) ? $CFG->exagames_randomize_questions : 0);
		}
        $randomizeanswers = (isset($game->randomizeanswers) ? $game->randomizeanswers : -1);
        if ($randomizeanswers == -1) {
            $randomizeanswers = (isset($CFG->exagames_randomize_answers) ? $CFG->exagames_randomize_answers : 0);
        }

        $questions = $quiz->questions;
		if ($randomizequestions) {
            $questions = array_values($questions);
            shuffle($questions);
        }

		foreach ($questions as $question) {

			$xmlQuestion = $xmlQuestions->addChild('question');
			$xmlQuestion->setAttributes(array(
				'id' => $question->id,
				'type' => $question->get_type_name(),
				'grade' => $question->maxmark
			));
			// $xmlQuestion->name = $question->name;
			$xmlQuestion->text = exagames_html_to_text($question->questiontext);

			if ($game->gametype == 'tiles') {
				$xmlQuestion->config->tile_size = $question->tile_size;
				$xmlQuestion->config->difficulty = $question->difficulty;
				$xmlQuestion->config->content_url = $question->content_url;
				$xmlQuestion->config->display_order = $question->display_order;
			}

			$questionAnswers = $question->answers;
			if ($randomizeanswers) {
                $questionAnswers = array_values($questionAnswers);
                shuffle($questionAnswers);
			}

			$xmlQuestion->feedbacks->general = exagames_html_to_text($question->generalfeedback);
            /** @var qtype_multichoice_base $question */
			if ($question->get_type_name() == 'multichoice') {
				$xmlQuestion->setAttributes(array(
//					'single' => (int) ($question instanceof qtype_multichoice_single_question)
					'single' => (int) $question->single
				));

				$answers = $xmlQuestion->addChild('answers');
				foreach ($questionAnswers as $answer) {
					$xmlAnswer = $answers->addChild('answer')->setAttributes(array('id'=>$answer->id, 'fraction'=>$answer->fraction));
					$xmlAnswer->text = exagames_html_to_text($answer->answer);
					$xmlAnswer->feedback = exagames_html_to_text($answer->feedback);
				}

				$xmlQuestion->feedbacks->correct = exagames_html_to_text($question->correctfeedback);
				$xmlQuestion->feedbacks->partiallycorrect = exagames_html_to_text($question->partiallycorrectfeedback);
				$xmlQuestion->feedbacks->incorrect = exagames_html_to_text($question->incorrectfeedback);

			} elseif ($question->get_type_name() == 'truefalse') {
				/** @var qtype_truefalse_question $question */
				// This $rightanswer is not working in new Moodle versions.
//                $rightanswer = $question->rightanswer;
				// Get it manually:
                foreach ($questionAnswers as $answer) {
                    if ((float) $answer->fraction > 0.99) {
						// convert to bool
                        $rightanswer = filter_var($answer->answer, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
                }

				$xmlQuestion->setAttributes(array(
					'correctanswer' => (int) $rightanswer,
					'randomizeanswers' => (int) $randomizeanswers // for JS randomizing
				));

				$xmlQuestion->feedbacks->truefeedback = exagames_html_to_text($question->truefeedback);
				$xmlQuestion->feedbacks->falsefeedback = exagames_html_to_text($question->falsefeedback);
			} else {
				exagames_print_error("Questiontype is not supported! Please use truefalse, or multichoice!");
			}
		}
		header('Content-Type: text/xml; charset=utf-8');

		echo $xmlQuiz->asXML();

		exit;
	}
}

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$context = context_module::instance($cm->id);
/*if (has_capability('moodle/course:manageactivities', $context) && ($action == 'configure_questions') && ($questionId = optional_param('questionid', '', PARAM_INT)) && isset($quiz->questions[$questionId]) && ($content_url = optional_param('content_url', '', PARAM_TEXT))) {
	$questionConfig = new stdClass();
	$questionConfig->id = $questionId;
	$questionConfig->content_url = $content_url;
	$questionConfig->tile_size = optional_param('tile_size', '', PARAM_TEXT);
	$questionConfig->difficulty = optional_param('difficulty', '', PARAM_TEXT);
	$questionConfig->display_order = optional_param('display_order', '', PARAM_TEXT);

	if (!$DB->record_exists('exagames_question', array('id'=>$questionId))) {
		$DB->Execute("INSERT INTO {$CFG->prefix}exagames_question (id, tile_size, content_url, difficulty, display_order) VALUES ({$questionConfig->id}, '', '', '', '')");
	}
	$DB->update_record('exagames_question', $questionConfig);

	echo "ok=1";
	exit;
}*/

/// Print the page header
$strexagamess = get_string("modulenameplural", "exagames");
$strexagames  = get_string("modulename", "exagames");

$navlinks = array();
$navlinks[] = array('name' => $strexagamess, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($game->name), 'link' => '', 'type' => 'activityinstance');

//$navigation = build_navigation($navlinks);
$partUrl = explode("/", $_SERVER['PHP_SELF'], 2);
$pos = strpos($partUrl[1], "/");
$urlparams = ['id' => $id];
$url = new moodle_url(
	substr($partUrl[1], $pos),
    $urlparams
);
$PAGE->set_url($url);
$PAGE->requires->js('/mod/exagames/js/swfobject.js', true);

switch ($game->gametype) {
    case 'braingame':
		// Add CSS
        $PAGE->requires->css('/mod/exagames/html5/braingame/braingame.css');
		// Add JS
        $PAGE->requires->js('/mod/exagames/html5/js/jquery.min.js', true);
        $PAGE->requires->js('/mod/exagames/html5/js/phaser.js', true);
        $PAGE->requires->js('/mod/exagames/html5/braingame/js/braingame.js', true);
}



$stringman = get_string_manager();
$strings = $stringman->load_component_strings('mod_exagames', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'mod_exagames');

echo $OUTPUT->header();

// Sometimes there is a problems if the user has disabled caching.
// Here is a trying to solve this situation - preload images before the game will ask them:
// Also with this solution we can get files from File storage if we will need
if ($game->gametype == 'braingame') {
    $preloadedimages = [
        'baseBackground.png',
        'stairway_basic.png',
        'cloud_base.png',
        'brain_table_basic.png',
        'brain.png',
        'einstein_thumbs-up.png',
        'lamp.png',
        'clipboard.png',
        'crow.png',
        'sky3.png',
        'startScreen.png',
        'water.png',
        'brain_indicator.png',
        'einstein-flying.png',
        'einstein_smirk.png',
        'einstein_mad.png',
        'stairway_destroyed.png',
        'einstein_splash.png',
    ];
    echo '<div style="display: none;">';
    foreach ($preloadedimages as $imgname) {
        echo '<img
            id = "preloaded_'.$imgname.'"
            src="' . $CFG->wwwroot . '/mod/exagames/html5/braingame/assets/brain/' . $imgname . '">';
    }
    echo '</div>';
}

//$context = get_context_instance(CONTEXT_COURSE, $game->course);
$context = context_module::instance($cm->id);
/*
if (has_capability('moodle/course:manageactivities', $context) && $action == 'configure_question_file') {
	$questionId  = optional_param('questionid', '', PARAM_INT);

	if (!isset($quiz->questions[$questionId])) {
		exagames_print_error('wrong question');
	}

	$question = $quiz->questions[$questionId];

	require_once($CFG->dirroot.'/lib/formslib.php');

	class configure_question_file_form extends moodleform {

		// Define the form
		function definition () {
			global $CFG, $COURSE;
			$mform =& $this->_form;

			/// Print the required moodle fields first
			$mform->addElement('filemanager', 'file', 'File', null,  array('maxfiles' => 1));

			$this->add_action_buttons();
		}
	}

	$questionform = new configure_question_file_form($_SERVER['REQUEST_URI'], null);

	$draftitemid = file_get_submitted_draft_itemid('file');
	file_prepare_draft_area($draftitemid, $context->id, 'mod_exagames', 'questions', $question->id);
	$question->file = $draftitemid;

	if ($questionform->is_cancelled()) {
		if ($question->content_url)
			redirect(new moodle_url('/mod/exagames/view.php?action=configure_questions&id='.$id.'&questionid='.$question->id));
		else
			redirect(new moodle_url('/mod/exagames/view.php?action=configure_questions&id='.$id));
	} else if ($formdata = $questionform->get_data()) {

		var_dump($formdata);

		$context = get_context_instance(CONTEXT_MODULE, $cm->id);
		file_save_draft_area_files($formdata->file, $context->id, 'mod_exagames', 'questions', $question->id);

		$fs = get_file_storage();
		$file = $fs->get_area_files($context->id, 'mod_exagames', 'questions', $question->id);

		$file = reset($file);
		$path = '/'.$file->get_contextid().'/mod_exagames/questions/'.$file->get_filepath().$file->get_filename();
		$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);

		echo $fullurl;

		var_dump($file);
		exit;
	} else {
		$context = get_context_instance(CONTEXT_MODULE, $cm->id);

		$fs = get_file_storage();
		$file = $fs->get_area_files($context->id, 'mod_exagames', 'questions', $question->id);

		$file = reset($file);
		if ($file) {
			var_dump($file->get_filesize());
			var_dump(get_class_methods($file));
			var_dump($file);
			$path = '/'.$file->get_contextid().'/mod_exagames/file/'.$file->get_filepath().$file->get_filename();
			var_dump($path);
			$fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);

			echo $fullurl;
		}

		$questionform->set_data($question);

		print_header_simple(format_string($game->name), "", $navigation, "", "", true,
				  update_module_button($cm->id, $course->id, $strexagames), navmenu($course, $cm));
		exagames_print_tabs($game, 'configure_questions');

		$questionform->display();

		/// Finish the page
		echo $OUTPUT->footer();
	}

	exit;
}

*/
//print_header_simple(format_string($game->name), "", $navigation, "", "", true,
			//  update_module_button($cm->id, $course->id, $strexagames), navmenu($course, $cm));

	//$moduleId = $DB->get_field('modules', 'id', array('name'=>'exagames'));
	//$courseContextId = context_course::instance($COURSE->id)->id;
	//$result = $DB->get_records('folder', array('course'=> $COURSE->id));

	//echo "SELECT * FROM mdl_files f JOIN mdl_context ctx ON f.contextid = ctx.id having f.contextid IN ($ids)";

	//$fs = get_file_storage();
	/*$files = $fs->get_area_files(, 'mod_folder', 'content');
	//var_dump($files);
	foreach ($files as $f) {
    // $f is an instance of stored_file
		$fName = $f->get_filename();
		if(substr($fName, -3) == 'jpg' || substr($fName, -3) == 'png' || substr($fName, -3) == 'gif')
	}*/
	/*
if (has_capability('moodle/course:manageactivities', $context) && $action == 'configure_questions') {

	exagames_print_tabs($game, 'configure_questions');

	$questionId  = optional_param('questionid', '', PARAM_INT);

	if (isset($quiz->questions[$questionId])) {
		$question = $quiz->questions[$questionId];

		$flashvars = array(
			'save_url' => $_SERVER['REQUEST_URI'],
			'back_url' => $_SERVER['PHP_SELF'].'?action=configure_questions&id='.$cm->id,
			'content_url' => $question->content_url,
			'tile_size' => $question->tile_size,
			'difficulty' => $question->difficulty,
			'display_order' => $question->display_order,
			'selectable_images' => $img_files
		);

		?>
		 <script src="html5/js/jquery.min.js"></script>

		<script type="text/javascript">

			var flashvars = <?php echo json_encode($flashvars) ?>;
			var params = {};
			var attributes = {};
			//swfobject.embedSWF(<?php echo json_encode($CFG->wwwroot.'/mod/exagames/swf/tiles_editor.swf'); ?>, "GameContent", "940", "535", "9.0.0", false, flashvars, params, attributes);
			$(document).ready(function(){
				$( "#GameContent" ).empty();
				//$( "#GameContent" ).append('<iframe height="900" width= "1200" src="./html5/tiles_editor/tiles.html">Could not load iframe</iframe>');
				$( "#GameContent" ).load('./html5/tiles_editor/tiles.html');
			});
	</script>
		<div id="Game" style="width: 940px; margin: 0 auto;">
			<div id="GameContent" style="width: 940px; margin: 0 auto;">
				<a href="http://www.adobe.com/go/getflashplayer" style="display: block; padding: 40px; text-align: center;">
					<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
				</a>
			</div>
		</div>
		<?php
	} else {
		// list questions

		echo '<table border="0" align="center" cellspacing="0" cellpadding="2">';
		echo '<b>'.get_string('questions', 'quiz').':</b><br />';
		foreach ($quiz->questions as $question) {
			echo '<tr><td>';
			echo '<a href="'.$CFG->wwwroot.'/mod/exagames/view.php?action=configure_questions&id='.$cm->id.'&questionid='.$question->id.'">'.$question->name.'</a><br />';
			echo '</td><td>';
			echo get_string($question->content_url?'question_configured':'question_not_configured', 'exagames');
			echo '</td></tr>';
		}
		echo '</table>';
	}

	/// Finish the page
	echo $OUTPUT->footer();
	exit;
}
*/

/*
$globalTopScore = get_field_sql("SELECT MAX(score) FROM {$CFG->prefix}exagames_scores WHERE score>0 AND gameid='".$game->id."' AND gametype='".$game->gametype."'");
$myBestScore = get_field_sql("SELECT MAX(score) AS score FROM {$CFG->prefix}exagames_scores WHERE score>0 AND gameid='".$game->id."' AND gametype='".$game->gametype."' AND userid=".$USER->id);
*/
exagames_print_tabs($game, 'show');

// Print the main part of the page
if ($game->gametype != 'gamelabs') {
	$partUrl = explode("/", $_SERVER['PHP_SELF'], 2);
	$pos = strpos($partUrl[1], "/");
	$url = new moodle_url(
			substr($partUrl[1], $pos), 
			['id'=>$id]
	);
	$flashvars = array(
		'gameurl' => $CFG->wwwroot.'/mod/exagames/view.php?id='.$id,
		'gamedataurl' => $CFG->wwwroot.'/mod/exagames/view.php?id='.$cm->id.'&action=data&rand='.time(),
		'courseurl' => $CFG->wwwroot.'/course/view.php?id='.$course->id,
		'translationsurl' => $CFG->wwwroot.'/mod/exagames/view.php?id='.$cm->id.'&action=translations'
	);
	$gametype = $game->gametype;

    //
	if (!$quiz->questions || !count($quiz->questions)) {
        echo \html_writer::div(get_string('brain_noquestions', 'mod_exagames'), 'alert alert-danger');
	} else {

		// scripts / CSS / fonts preloader
?>

		<style>

            /* font files from own moodle server */
            /* latin-ext */
            @font-face {
                font-family: 'Luckiest Guy';
                font-style: normal;
                font-weight: 400;
                src: url('fonts/LuckiestGuy-Regular.woff2') format('woff2');
                unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
            }
            /*!* latin *!*/
            @font-face {
                font-family: 'Luckiest Guy';
                font-style: normal;
                font-weight: 400;
                src: url('fonts/LuckiestGuy-Regular.woff2') format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }

		</style>

 <script src="html5/js/jquery.min.js"></script>
<!-- <script src="html5/js/phaser.min.js"></script>-->
 <script src="html5/js/phaser.js"></script>

<script type="text/javascript">
	var flashvars = <?php echo json_encode($flashvars) ?>;
	var gameType = <?php echo json_encode($gametype) ?>;
	//var flashvars = <?php echo json_encode(array_map('urlencode', $flashvars)) ?>;
	const EVALUATION_DISPLAY_DURATION = <?php echo @$CFG->exagames_braingamedurationtime ?: 4000 ?>;
	var params = {};
	var attributes = {};
	//swfobject.embedSWF(<?php echo json_encode($game->swf); ?>, "GameContent", <?php echo $game->width; ?>, <?php echo $game->height; ?>, "9.0.0", false, flashvars, params, attributes);
	if (gameType == 'braingame') {
		$(document).ready(function(){
				$( "#GameContent" ).empty();
				$( "#GameContent" ).load('./html5/braingame/braingame.html'/*, function (response, status, xhr) {
                    if (status === "success") {
                        console.log("Game    loaded successfully!");
                    } else if (status === "error") {
                        console.error("Error loading content:", xhr.status, xhr.statusText);
                    }
                }*/);
		});
	} else if (gameType == 'tiles') {
		$(document).ready(function(){
				$( "#GameContent" ).empty();
				$( "#GameContent" ).load('./html5/blurrygame/blurrygame.html');
		});
	}

    $(document).ready(function(){
        if (typeof loadGameQuestions === 'function') {
            loadGameQuestions("<?php echo $flashvars['gamedataurl']; ?>");
            // gameInit(); // look inside loadGameQuestions()
        }
    });

	</script>
<div id="Game" style="width: 1200px; margin: 0 auto;">
	<div style="visibility:hidden; font-family: 'Luckiest Guy';">Hack for preload font</div>
	<div id="GameContent" style="width: 1200px; margin: 0 auto;">
		<a href="http://www.adobe.com/go/getflashplayer" style="display: block; padding: 40px; text-align: center;">
			<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
		</a>
	</div>
</div>

<?php
    }
}
// Gameslab.at Frame einbinden
else {
/*
	$gameslab = file_get_contents($game->url);
	$pos1 = strpos($gameslab,'<div id="Adventure_Player" class="Adventure_Player">');
	$pos2 = strpos($gameslab,'so.write("Adventure_Player");
</script>
</div>');

 	$aplay_pos = strpos($gameslab,'Adventure_Player');
 	$jsbegin = strpos($gameslab,'<script type="text/javascript">',$aplay_pos);
 	$jsend = strpos($gameslab,'</script>',$jsbegin) + strlen('</script>');

 	echo '<script type="text/javascript" src="http://gamelabs.at/fileadmin/gamelabs/tmpl/js/swfobject.js"></script>';
 	echo '<div id="Adventure_Player" class="Adventure_Player"></div>';
	$flashcontent = substr($gameslab,$jsbegin,$jsend-$jsbegin);

	$flashcontent = str_replace('new SWFObject("fileadmin/', 'new SWFObject("http://gamelabs.at/fileadmin/', $flashcontent);
	//$flashcontent = str_replace('fileadmin/', 'http://gamelabs.at/fileadmin/', $flashcontent);
	echo $flashcontent;
*/
echo '
<iframe src="'.$game->url.'&type=5" width="740" height="520" name="gamelabs.at">
  <p>iframe is not working</p>
</iframe>';
}

// 5 TOP scores.
$showtopscores = $game->showtopresults;
$showtop = false;
switch ($showtopscores) {
	case 'all':
        $userids = []; // for ALL users
        $showtop = true;
		break;
	case 'ownCohorts':
    case 'selectedCohort':
        $showtop = true;
        require_once ($CFG->dirroot.'/cohort/lib.php');
		if ($showtopscores == 'ownCohorts') {
			// all player cohorts
            $cohorts = cohort_get_user_cohorts($USER->id);
            $cohortids = array_map(function($item) {return $item->id;}, $cohorts);
		} else {
			// only selected cohort
            $cohortids = [$game->showtopresultscohort];
		}
		// get users by cohorts:
        $userids = [];
		if ($cohortids) {
            $sql = "SELECT u.id
        				FROM {cohort_members} cm
        					JOIN {user} u ON cm.userid = u.id
        				WHERE cm.cohortid IN (" . implode(',', array_fill(0, count($cohortids), '?')) . ")";
            $users = $DB->get_records_sql($sql, $cohortids);
            $userids = array_map(function($item) {return $item->id;}, $users);
        }
		break;
	case 'ownGroups':
    case 'selectedGroup':
        $showtop = true;
        if ($showtopscores == 'ownGroups') {
            $groups = groups_get_user_groups($game->course, $USER->id);
            $groupids = [];
            foreach ($groups as $grouping => $tempgroupids) {
                $groupids = array_merge($groupids, $tempgroupids);
            }
        } else {
            $groupids = [$game->showtopresultsgroup];
        }
	    // get users by groups:
	    $userids = [];
        $sql = "SELECT u.id
        			FROM {user} u
        				JOIN {groups_members} gm ON u.id = gm.userid
        			WHERE gm.groupid IN (" . implode(',', array_fill(0, count($groupids), '?')) . ")";
        $users = $DB->get_records_sql($sql, $groupids);
	    $userids = array_map(function($item) {return $item->id;}, $users);
		break;
	case 'none':
	default:
		break;
}

if ($showtop) {

	// Note: only the same gametype
    $whereforusers = '';
	if ($userids) {
        $whereforusers = ' AND userid IN ('.implode(', ', $userids).') ';
	}
	// NOTE: shown MAX score for the user
    $sql = 'SELECT s.id, MAX(s.score) AS score, u.firstname, u.lastname, s.userid 
        FROM {exagames_scores} s 
            JOIN {user} u ON u.id=s.userid 
        WHERE 
            score > 0 
            AND gameid = \''.$game->id.'\'
            AND gametype = \''.$game->gametype.'\'
            '.$whereforusers.'
        GROUP BY s.userid, u.firstname, u.lastname 
        ORDER BY score DESC, time ASC 
        LIMIT 0, 5 ';
    $results = $DB->get_records_sql($sql);

	if ($results) {
		echo html_writer::tag('h4', get_string('showtopresults.header', 'exagames'));
		$shownuserscounter = 0; // for limiting to 5 shown users. (not in SQL cause 'teacher' filtering)

        $table = new html_table();
		$table->size = ['50%', '50%'];
//    $table->head = ['Name', 'Score']; // TODO: needed?
        $table->data = []; // This will hold the rows of the table
        foreach ($results as $user) {
	        if ($shownuserscounter >= 5) {
				break;
	        }
            // filter by 'teacher'
            if ($game->hideteachers) {
				if (exagames_is_teacher($game->course, $user->userid)) {
					continue;
				}
            }

            if ($game->securenames) {
				// Use first lettaer of the fisrt name and random '*' strings
                $fullname = substr($user->firstname, 0, 1) . str_repeat('*', rand(4, 7)) . ' ' . str_repeat('*', rand(4, 9));
			} else {
				// Use real names
                $fullname = $user->firstname . ' ' . $user->lastname;
            }
            $table->data[] = [
                $fullname,
                $user->score,
            ];
            $shownuserscounter++;
        }
        echo html_writer::table($table);
    }

}

// Finish the page
echo $OUTPUT->footer();
