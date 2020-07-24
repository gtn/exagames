

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

class mod_precheck_mod_form extends moodleform_mod {


	function definition() {

		global $COURSE, $CFG, $DB, $OUTPUT, $PAGE, $USER;
		$mform    =& $this->_form;


//-------------------------------------------------------------------------------
$stringman = get_string_manager();
$strings = $stringman->load_component_strings('mod_precheck', $CFG->lang);

$PAGE->requires->strings_for_js(array_keys($strings), 'mod_precheck');


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


		$games = array(
			'precheck' => get_string('game_precheck', 'exagames'),
		);

			//element type, key, language, options

		$mform->addElement('select', 'gametype', get_string('gametype', 'exagames'), $games);
		$mform->addHelpButton('gametype', 'gametype', 'exagames');




				$url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		  //	$_SERVER['REQUEST_SCHEME'] . '//' . $_SERVER['SERVER_NAME']



		?>
		<!--<script src="/mod/exagames/html5/js/jquery.min.js"></script>-->
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
		<script>
		
		$(function(){
    $('.editor_frames').on('load', function(){
			$("iframe").contents().find('#difficultyLabel').html("<?php echo get_string('tiles_difficultyLabel', 'mod_exagames'); ?>");
			$("iframe").contents().find('#saveButton').html("<?php echo get_string('tiles_saveButton', 'mod_exagames'); ?>");
			$("iframe").contents().find('#randomizeButton').html("<?php echo get_string('tiles_randomizeButton', 'mod_exagames'); ?>");
			$("iframe").contents().find('#simulateButton').html("<?php echo get_string('tiles_simulateButton', 'mod_exagames'); ?>");
			$("iframe").contents().find('#saveText').html("<?php echo get_string('tiles_saveText', 'mod_exagames'); ?>");
			$("iframe").contents().find('#resetButton').html("<?php echo get_string('tiles_resetButton', 'mod_exagames'); ?>");
			$("iframe").contents().find("#difficultyForm input[value='easy']")
				.next()
				.html("<?php echo get_string('tiles_difficultyLabel_easy', 'mod_exagames'); ?>");
			$("iframe").contents().find("#difficultyForm input[value='intermediate']")
				.next()
				.html("<?php echo get_string('tiles_difficultyLabel_medium', 'mod_exagames'); ?>");
			$("iframe").contents().find("#difficultyForm input[value='hard']")
				.next()
				.html("<?php echo get_string('tiles_difficultyLabel_hard', 'mod_exagames'); ?>");
$("iframe").contents().find('#difficultyLabel').html("<?php echo get_string('tiles_difficultyLabel', 'mod_exagames'); ?>");});
        
  
});

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
