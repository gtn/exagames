<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   exagames
 * @copyright  2024 GTN Solutions GmbH (www.gtn-solutions.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/exagames/lib.php');

    // MAIN settings

    $settings->add(new admin_setting_heading('gamesettings', get_string('modsettings.gameparams.header', 'exagames'),
        get_string('modsettings.gameparams.header_help', 'exagames')));

    // Randomize questions
    $settings->add(new admin_setting_configselect('exagames_randomize_questions', get_string('modsettings.gameparams.randomizequestions', 'exagames'),
        get_string('modsettings.gameparams.randomizequestions_description', 'exagames'), EXAGAMES_OPTION_ENABLED, exagames_randomize_options()));
    // Randomize answers
    $settings->add(new admin_setting_configselect('exagames_randomize_answers', get_string('modsettings.gameparams.randomizeanswers', 'exagames'),
        get_string('modsettings.gameparams.randomizeanswers_description', 'exagames'), EXAGAMES_OPTION_ENABLED, exagames_randomize_options()));

    // TOP SCORES settings

    $settings->add(new admin_setting_heading('topscoressettings', get_string('modsettings.topscoresparams.header', 'exagames'),
        get_string('modsettings.topscoresparams.header_help', 'exagames')));

    // Duration time on question changing
    $settings->add(new admin_setting_configtext('exagames_topscoreslimit', get_string('modsettings.topscoresparams.limit', 'exagames'),
        get_string('modsettings.topscoresparams.limit_description', 'exagames'), 5, PARAM_INT));


    // BRAIN GAME settings

    $settings->add(new admin_setting_heading('braingamesettings', get_string('modsettings.braingameparams.header', 'exagames'),
        get_string('modsettings.braingameparams.header_help', 'exagames')));
    // Duration time on question changing
    $settings->add(new admin_setting_configtext('exagames_braingamedurationtime', get_string('modsettings.braingameparams.durationtime', 'exagames'),
        get_string('modsettings.braingameparams.durationtime_description', 'exagames'), 4000, PARAM_INT));


}

