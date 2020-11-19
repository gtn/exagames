

<?php //$Id: mod_form.php,v 1.3 2008/08/10 08:05:15 mudrd8mz Exp $
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
    $mform->addElement('text', 'name', get_string('exagamesname', 'precheck'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
		$this->add_intro_editor(false);


		
		$games = getGames();

			//element type, key, language, options

		$mform->addElement('select', 'gametype', get_string('gametype', 'precheck'), $games);
		$mform->addHelpButton('gametype', 'gametype', 'precheck');
		$mform->addElement('filemanager', 'attachments', get_string('attachment', 'moodle'), null,
		    array('return_types'=> FILE_INTERNAL | FILE_EXTERNAL));


		$url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];




//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
		$this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

	}
}
