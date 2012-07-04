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
$responses = optional_param('responses', array(), PARAM_RAW);

if ($id) {
	if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
		print_error("Course Module ID was incorrect");
	}

	if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
		print_error("Course is misconfigured");
	}

	if (! $game = exagames_get_game_instance($cm->instance)) {
		error("Game not found");
	}

} else {
	if (! $game = $DB->get_record("exagames", array("id"=>$a))) {
		print_error("Course module is incorrect");
	}
	if (! $course = $DB->get_record("course", array("id"=>$game->course))) {
		print_error("Course is misconfigured");
	}
	if (! $cm = get_coursemodule_from_instance("exagames", $game->id, $course->id)) {
		print_error("Course Module ID was incorrect");
	}
}

require_login($course->id);

if($game->gametype != "gamelabs")
	$quiz = exagames_load_quiz($game->quizid);



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
			'percent' => $quiz->sumgrades ? $grade / $quiz->sumgrades : 0,
			'sumgrades' => $quiz->sumgrades,
			'grade' => $grade
		));
		// Todo: Feedback

		$context = get_context_instance(CONTEXT_MODULE, $cm->id);
		$xmlResult->feedback = exagames_html_to_text(quiz_feedback_for_grade($attemptgrade, $quiz, $context));

		header('Content-Type: text/xml; charset=utf-8');
		echo $xmlResult->asPrettyXML();

		exit;

	} elseif (!isguestuser() && $game->hasHighscore && (($score = optional_param('score', -9999, PARAM_INT)) != -9999)) {
	
		$scoreDb = new stdClass();
		$scoreDb->userid = $USER->id;
		$scoreDb->gameid = $game->id;
		$scoreDb->gametype = $game->gametype;
		$scoreDb->score = $score;
		$scoreDb->time = time();

		$DB->insert_record('exagames_scores', $scoreDb);
		
		echo 'ok=1';
		
		exit;
	} else {
		// output gamedata as xml for flash

		require dirname(__FILE__).'/lib/Pro/SimpleXMLElement.php';

		$xmlQuiz = Pro_SimpleXMLElement::create('quiz');
		$xmlQuiz->setAttribute('sumgrades', $quiz->sumgrades);

		$xmlUser = $xmlQuiz->addChild('user')->setAttribute('id', $USER->id);
		$xmlUser->addChild('name', fullname($USER));
		$xmlQuiz->intro = exagames_html_to_text($quiz->intro);

		if ($game->gametype == 'tiles') {
			$xmlQuiz->rules = null;
			$xmlQuiz->rules->addCData(get_string("game_tiles_rules", "exagames"));
		}

		$xmlQuestions = $xmlQuiz->addChild('questions');

		foreach ($quiz->questions as $question) {
			
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

			$xmlQuestion->feedbacks->general = exagames_html_to_text($question->generalfeedback);

			if ($question->get_type_name() == 'multichoice') {

				$xmlQuestion->setAttributes(array(
					'single' => (int) ($question instanceof qtype_multichoice_single_question)
				));

				$answers = $xmlQuestion->addChild('answers');
				foreach ($question->answers as $answer) {
					$xmlAnswer = $answers->addChild('answer')->setAttributes(array('id'=>$answer->id, 'fraction'=>$answer->fraction));
					$xmlAnswer->text = exagames_html_to_text($answer->answer);
					$xmlAnswer->feedback = exagames_html_to_text($answer->feedback);
				}

				$xmlQuestion->feedbacks->correct = exagames_html_to_text($question->correctfeedback);
				$xmlQuestion->feedbacks->partiallycorrect = exagames_html_to_text($question->partiallycorrectfeedback);
				$xmlQuestion->feedbacks->incorrect = exagames_html_to_text($question->incorrectfeedback);

			} elseif ($question->get_type_name() == 'truefalse') {
				
				$xmlQuestion->setAttributes(array(
					'correctanswer' => (int) $question->rightanswer
				));

				$xmlQuestion->feedbacks->truefeedback = exagames_html_to_text($question->truefeedback);
				$xmlQuestion->feedbacks->falsefeedback = exagames_html_to_text($question->falsefeedback);
			}
		}

		header('Content-Type: text/xml; charset=utf-8');
		echo $xmlQuiz->asXML();

		exit;
	}
}

$context = get_context_instance(CONTEXT_COURSE, $game->course);
if (has_capability('moodle/course:manageactivities', $context) && ($action == 'configure_questions') && ($questionId = optional_param('questionid', '', PARAM_INT)) && isset($quiz->questions[$questionId]) && ($content_url = optional_param('content_url', '', PARAM_TEXT))) {
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
}


add_to_log($course->id, "exagames", "view", "view.php?id=$cm->id", "$game->id");

/// Print the page header
$strexagamess = get_string("modulenameplural", "exagames");
$strexagames  = get_string("modulename", "exagames");

$navlinks = array();
$navlinks[] = array('name' => $strexagamess, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($game->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

$PAGE->set_url($_SERVER['REQUEST_URI']);
$PAGE->requires->js('/mod/exagames/js/swfobject.js', true);


$context = get_context_instance(CONTEXT_COURSE, $game->course);
/*
if (has_capability('moodle/course:manageactivities', $context) && $action == 'configure_question_file') {
	$questionId  = optional_param('questionid', '', PARAM_INT);

	if (!isset($quiz->questions[$questionId])) {
		print_error('wrong question');
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

print_header_simple(format_string($game->name), "", $navigation, "", "", true,
			  update_module_button($cm->id, $course->id, $strexagames), navmenu($course, $cm));

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
			'display_order' => $question->display_order
		);

		?>
		<script type="text/javascript">
			var flashvars = <?php echo json_encode(array_map('urlencode', $flashvars)) ?>;
			var params = {};
			var attributes = {};
			swfobject.embedSWF(<?php echo json_encode($CFG->wwwroot.'/mod/exagames/swf/tiles_editor.swf'); ?>, "GameContent", "940", "535", "9.0.0", false, flashvars, params, attributes);
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


/*
$globalTopScore = get_field_sql("SELECT MAX(score) FROM {$CFG->prefix}exagames_scores WHERE score>0 AND gameid='".$game->id."' AND gametype='".$game->gametype."'");
$myBestScore = get_field_sql("SELECT MAX(score) AS score FROM {$CFG->prefix}exagames_scores WHERE score>0 AND gameid='".$game->id."' AND gametype='".$game->gametype."' AND userid=".$USER->id);
*/
exagames_print_tabs($game, 'show');

/// Print the main part of the page

if($game->gametype != 'gamelabs') {
$flashvars = array(
	'gameurl' => $_SERVER['REQUEST_URI'],
	'gamedataurl' => $CFG->wwwroot.'/mod/exagames/view.php?id='.$cm->id.'&action=data&rand='.time(),
	'courseurl' => $CFG->wwwroot.'/course/view.php?id='.$course->id,
	'translationsurl' => $CFG->wwwroot.'/mod/exagames/view.php?id='.$cm->id.'&action=translations'
);

?>
<script type="text/javascript">
	var flashvars = <?php echo json_encode(array_map('urlencode', $flashvars)) ?>;
	var params = {};
	var attributes = {};
	swfobject.embedSWF(<?php echo json_encode($game->swf); ?>, "GameContent", <?php echo $game->width; ?>, <?php echo $game->height; ?>, "9.0.0", false, flashvars, params, attributes);
</script>
<div id="Game" style="width: 800px; margin: 0 auto;">
	<div id="GameContent" style="width: 800px; margin: 0 auto;">
		<a href="http://www.adobe.com/go/getflashplayer" style="display: block; padding: 40px; text-align: center;">
			<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
		</a>
	</div>
</div>

<?php
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
$sql = "SELECT s.id, s.score, u.firstname, u.lastname ".
	"FROM {exagames_scores} s JOIN {user} u ON u.id=s.userid ".
	"WHERE score>0 AND gameid='".$game->id."' AND gametype='".$game->gametype."' ORDER BY score DESC LIMIT 0,10";

$res = $DB->get_records_sql($sql);

if ($res):
?>
<div style="text-align: center; font-size: 18px;margin-top:15px;">
10 Top Scores:
</div>
<div style="text-align: center;margin:10px 0;">
<table "align=center" style="margin: 0 auto;">
<?php
foreach ($res as $rs) {
	echo "<tr><td align=left style='padding-right:15px;'>".fullname($rs)."</td><td align=right>".$rs->score."</td></tr>";
}
?>
</table>
</div>
<?php
endif;

/// Finish the page
echo $OUTPUT->footer();