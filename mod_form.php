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

		global $COURSE, $CFG, $DB, $OUTPUT;
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
		if ($recs = $DB->get_records_select('quiz', "course='$COURSE->id'", null, 'name', 'id,name')) {
            foreach ($recs as $rec) {
                $quizzes[$rec->id] = $rec->name;
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
		
		if($quizzes) {
        	$mform->addElement('select', 'quizid', get_string('modulename', 'quiz'), $quizzes);
        	$mform->addHelpButton('quizid', 'quizid', 'exagames');
		}
		
		$games = exagames_get_available_games($quizzes);
		if (count($games)>=1) {
			$mform->addElement('select', 'gametype', get_string('gametype', 'exagames'), $games);
			$mform->addHelpButton('gametype', 'gametype', 'exagames');
		}
		$mform->addElement('text', 'url', get_string('url', 'exagames'), array('size'=>'64'));
		$mform->addHelpButton('url', 'url', 'exagames');
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
