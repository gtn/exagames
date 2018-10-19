

<?php //$Id: mod_form.php,v 1.3 2008/08/10 08:05:15 mudrd8mz Exp $

/**
 * This file defines de main exagames configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             exagames type (index.php) and in the header
 *             of the exagames main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once ('moodleform_mod.php');

class mod_exagames_mod_form extends moodleform_mod {


	function definition() {

		global $COURSE, $CFG, $DB, $OUTPUT, $PAGE, $USER;
		$mform    =& $this->_form;


//-------------------------------------------------------------------------------


    /// Adding the "general" fieldset, where all the common settings are showed
    $mform->addElement('header', 'general', get_string('general', 'form'));
    /// Adding the standard "name" field
    $mform->addElement('text', 'name', get_string('exagamesname', 'exagames'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
		$this->add_intro_editor(false);
    /// Adding the optional "intro" and "introformat" pair of fields
		/*
    	$mform->addElement('htmleditor', 'intro', get_string('exagamesintro', 'exagames'));
		$mform->setType('intro', PARAM_RAW);
		$mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'introformat', get_string('format'));
		*/

	/// Quiz Dropdown
		$quizzes = array();
		$quizzes_questionNames = array();
		$firstQuizId = null;
		$urlQuizId = optional_param('quizId', 0, PARAM_INT); // Course Module ID, or
		if($urlQuizId != null) $firstQuizId = $urlQuizId;
		if ($recs = $DB->get_records_select('quiz', "course='$COURSE->id'", null, 'name', 'id,name')) {
						foreach ($recs as $rec) {
							 if($firstQuizId == null) {
								 $firstQuizId = $rec->id;

							 }
                $quizzes[$rec->id] = $rec->name;
								$quizObj = quiz::create($rec->id, $USER->id);
								$quizObj->preload_questions();
								$quizObj->load_questions();
								$qDetails = new stdClass();
								$qNameArr = array();
								foreach($quizObj->get_questions() as $q) {
									$curDetails = $DB->get_record('exagames_question', array ('id'=>$q->id), $fields='difficulty, display_order, content_url', $strictness=IGNORE_MISSING);
									$qDetails->difficulty = $curDetails->difficulty;
									$qDetails->display_order = $curDetails->display_order;
									$qDetails->content_url = $curDetails->content_url;
									$qDetails->name = $q->name;
									$qDetails->id = $q->id;


									$qNameArr[] = clone $qDetails;
								}
								$qObject = new stdClass();
								$qObject->questionDetails = $qNameArr;
							//	$temp = $DB->get_record('exagames_question', array ('id'=>), $fields='*', $strictness=IGNORE_MISSING);

								$qObject->quizName = $rec->name;

								$quizzes_questionNames[$rec->id] = $qObject;
            }
    }

		/*if (!$quizzes) {
			// dirty as moodle: link to add quiz if no quiz was found in this course!
			$return  = optional_param('return', 0, PARAM_BOOL);
		    $type    = optional_param('type', '', PARAM_ALPHANUM);
			$section = required_param('section', PARAM_INT);

			$a = new StdClass;
			$a->linkTag = '<a href="'.$CFG->wwwroot.'/course/modedit.php?add=quiz&type='.$type.'&course='.$COURSE->id.'&section='.$section.'&return='.$return.'">';
			$redirect = $CFG->wwwroot.'/course/modedit.php?add=quiz&type='.$type.'&course='.$COURSE->id.'&section='.$section.'&return='.$return;
			print_error('noquizzesincourse', 'exagames', $redirect, $a);
		}*/


        $mform->addElement('select', 'quizid', get_string('modulename', 'quiz'), $quizzes);
        $mform->addHelpButton('quizid', 'quizid', 'exagames');
		$mform->addRule('quizid', null, 'required', null, 'client');

		$games = array(
			'braingame' => get_string('game_braingame', 'exagames'),
			'tiles' => get_string('game_tiles', 'exagames')
		);

			//element type, key, language, options

		$mform->addElement('select', 'gametype', get_string('gametype', 'exagames'), $games);
		$mform->addHelpButton('gametype', 'gametype', 'exagames');

		$quizLen = count($quizzes_questionNames[$firstQuizId]->questionDetails);

			foreach ($quizzes_questionNames as $quizKey => $quizzes) {
				foreach($quizzes->questionDetails as $questKey => $qDetails) {
					$content_url =  $qDetails->content_url;
					$display_order = $qDetails->display_order;
					$difficulty = $qDetails->difficulty;
					$question_id = $qDetails->id;

					$tilesEditor = [];

					$urlParams = '?';
					$urlParams .= isset($content_url) && $content_url != null ? "content_url=$content_url&" : "";
					$urlParams .= isset($display_order) && $display_order != null ? "display_order=$display_order&" : "";
					$urlParams .= isset($difficulty) && $difficulty != null ? "difficulty=$difficulty&" : "";
					$urlParams .= isset($question_id) && $question_id != null ? "question_id=$question_id" : "";

					$tilesEditor[] = $mform->createElement(html, '
											<div id="tileEditor-' . $quizKey . '-quest-' . $questKey . '" style="width: 940px; height:600px">
												<iframe id="editorframe-' . $quizKey . '-quest-' . $questKey . '" frameborder="0" style="height:600px;width:940px" src="' . $CFG->wwwroot . '/mod/exagames/html5/form_editor/tiles.html' . $urlParams . '"></iframe></br>
											</div>');
					$tilesEditor[] =	$mform->createElement('filemanager', 'attachments', get_string('attachment', 'exagames'), null,
													array('subdirs' => 0, 'maxbytes' => $maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
																'accepted_types' => array('.jpg', '.png', '.gif'), 'return_types'=> FILE_INTERNAL | FILE_EXTERNAL));

					$mform->addGroup($tilesEditor, 'Editor', get_string('question', 'exagames') . '"' . $qDetails->name . '"');
				}
			}







				$url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		  //	$_SERVER['REQUEST_SCHEME'] . '//' . $_SERVER['SERVER_NAME']



		?>
		<script src="<?php echo json_decode($CFG->wwwroot); ?>/mod/exagames/html5/js/jquery.min.js"></script>

		<script>
		let quizzes = <?php echo json_encode($quizzes_questionNames); ?>;

		let url_string = window.location.href;
		let url = new URL(url_string);
		var cur_quizid = url.searchParams.get("quizId");
		var loc = <?php echo json_encode($url); ?>;

		$(document).ready(function() {



			handleGameTypeParam();
			handleQuizSelectParam();

			$('#id_gametype').on('change', function() {
				handleGameTypeParam();
			});

			$('#id_quizid').on('change', function() {
				handleQuizSelectParam();
			});

		});

		function handleGameTypeParam() {
			switch($('#id_gametype').val()) {
				case 'tiles':
						handleQuizSelectParam();
						break;
				case 'braingame':
						$("div[id*=tileEditor]").parent().parent().css('display', 'none');
						break;
			}
		}

		function handleQuizSelectParam() {
			$("div[id*=tileEditor]").parent().parent().css('display', 'none');
			if($('#id_gametype').val() == 'tiles') {
				$("div[id*=tileEditor-" +	$('#id_quizid').val() + "]").parent().parent().css('display', '');
				setTimeout(function() {
					$('#id_quizid').trigger('change');
				}, 500);
			}
		}

		function getLoadSwitchRef() {
			return 	$('#id_quizid');

		}

		function a(frameId) {
			return $('#' + frameId).parent().parent().find(".fm-content-wrapper > .fp-content");
		}


	 	 function getFMImage(frameId) {

		 	 let fileElement = $('#' + frameId).parent().parent().find('.fp-file .fp-thumbnail > img');
		 	 let imagePath = $($(fileElement)[0]).attr('src');
		 	 imagePath = trimURLParamsFromMedia(imagePath);
		 	 return imagePath;
	  }

	  function trimURLParamsFromMedia(str) {
	 	 if(str.includes(".png")) {
	 			 return str.substring(0, str.indexOf('.png') + 4);
	 	 } else if (str.includes(".jpg")) {
	 			 return str.substring(0, str.indexOf('.jpg') + 4);
	 	 } else if (str.includes(".gif")) {
	 			 return str.substring(0, str.indexOf('.gif') + 4);
	 	 } else {
	 		 return str;
	 	 }
	  }

		function getWindowLoc() {
			return	window.location.href;
		}

		function swapElement(a, b) {
	  // create a temporary marker div
		  var aNext = $('<div>').insertAfter(a);
		  a.insertAfter(b);
		  b.insertBefore(aNext);
		  // remove marker div
		  aNext.remove();
		}

		function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
	}

 </script>
		<?php

		if ((optional_param('func', '', PARAM_TEXT) == 'configure_question') && ($questionId = optional_param('questionid', '', PARAM_INT)) && ($content_url = optional_param('content_url', '', PARAM_TEXT))) {
			$questionConfig = new stdClass();
			$questionConfig->id = $questionId;
			$questionConfig->content_url = $content_url;
			$questionConfig->tile_size = optional_param('tile_size', '', PARAM_TEXT);
			$questionConfig->difficulty = optional_param('difficulty', '', PARAM_TEXT);
			$questionConfig->display_order = optional_param('display_order', '', PARAM_TEXT);
			var_dump($questionConfig);

			if (!$DB->record_exists('exagames_question', array('id'=>$questionId))) {
				$DB->Execute("INSERT INTO {$CFG->prefix}exagames_question (id, tile_size, content_url, difficulty, display_order) VALUES ({$questionConfig->id}, '', '', '', '')");
			}

			$DB->update_record('exagames_question', $questionConfig);
			echo "ok=1";
			exit;
		}

		/*
		$mform->addElement('text', 'url', get_string('url', 'exagames'), array('size'=>'64'));
		$mform->addHelpButton('url', 'url', 'exagames');*/
//-------------------------------------------------------------------------------
    /// Adding the rest of exagames settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic
	/*
        $mform->addElement('static', 'label1', 'exagamessetting1', 'Your exagames fields go here. Replace me!');

        $mform->addElement('header', 'exagamesfieldset', get_string('exagamesfieldset', 'exagames'));
        $mform->addElement('static', 'label2', 'exagamessetting2', 'Your exagames fields go here. Replace me!');
	*/

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
		$this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

	}
}
