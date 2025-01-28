<?php
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

// Main
$string['pluginname'] = 'Exabis Games';
$string['modulename'] = 'Exabis Games';
$string['modulenameplural'] = 'Exabis Games';
$string['pluginadministration'] = 'Exabis Games administration';

$string['question'] = 'Question: ';
$string['configure_quiz'] = 'Configure Quiz';
$string['configure_questions'] = 'Configure Questions';
$string['question_configured'] = 'Question configured';
$string['question_not_configured'] = 'Question not configured';
$string['modulename_help'] = 'The exabis games module imports quizzes of the type "multiple choice" and "true/false" to games to support the execution of these tasks with animations within a game context.

There are 2 games to choose from currently:

* BrainGame: The goal is to answer questions correctly and help the scientist to reach his goal of flying into space!

* Exaclicks: As time goes by, an image becomes more and more visible and supports the student on picking the correct answers to the questions.

NOTE: The Exaclicks game type is configured within the exabis game activity. Any Images uploaded to this course (as resources or inside folders) can be used for the configuration of the questions.';

// Edit Module Instance
$string['attachment'] = 'Filemanager';
$string['exagamesintro'] = 'Intro';
$string['exagamesname'] = 'Name';
$string['noquizzesincourse'] = 'Please create {$a->linkTag}a new quiz</a> first, before you add an Exabis Game!';
$string['savingdata'] = 'Saving data...';
$string['gametype'] = 'Game-Type';
$string['quizid'] = 'Quiz';
$string['gametype_help'] = 'Exabis-Games beinhaltet derzeit 2 Spiele:

* braingame - Hier wird die Durchführung von Tests mit Flash-Animationen begleitet. Ziel ist es, den Wissenschaftler durch die korrekten Antworten dabei zu helfen, ins All zu fliegen!
* exaclick - Hier wird ein Bild mit der Zeit sichtbarer, welches für die Beantwortung der jeweiligen Frage bedeutend ist. Bei Klick auf eine der Lösungsalternativen stoppt die Zeit und die Antwort wird ausgewertet. Die Fragen müssen für dieses Spiel im Vorhinein im "Fragen konfigurieren"-tab konfiguriert werden.<br /><br />HINWEIS: Im Konfigurator werden nur jene Bilder (jpg, png, gif) gelistet, welche im selben Kurs als Ressource oder in einem Ordner abgelegt sind, in der auch die Exagames-Aktivität statt findet.';
$string['quizid_help'] = 'Wählen Sie ein Quiz aus, welches für diese Spiel-Instanz von exagames verwendet werden soll.';
$string['url'] = 'Url';
$string['url_help'] = 'paste your gamelabs.at-adventure game here to embed it.<br /><br />this option will only work with a gamelabs-game-link and does not need Moodle-questions.';
// completion
$string['completion_mingrade'] = 'Minimum score to pass';
$string['completiondetail:minscore'] = 'Get at least {$a} scores';
$string['completionminscore'] = 'Minimum score to pass (%)';

// Games
$string['game_braingame'] = 'braingame';
$string['game_tiles'] = 'exaclick';
$string['game_gamelabs'] = 'gamelabs.at';
$string['game_tiles_rules'] = 'Are you ready for the exaclick challenge? Try to recognize what you see on these pictures and answer the questions. Here is what you need to do:<br />1. Click “Start” and a hidden image will appear. Tiles will gradually drop revealing more and more of the image/clip.<br />2. If you think you know enough, click “Stop”, the earlier you click “Stop” the higher your score will be. – But watch out!  If you click too early you might not yet see enough to answer the question!<br />3. Answer the pertaining question before time runs out.  You have 40 seconds to answer each question. You will get a higher score for more difficult questions and the faster you answer. Give a wrong answer and you will lose a life.<br />4. Keep on going until the end of the game<br />The questions will get more and more difficult. Be careful, you only have three lives. Only the most courageous and clever learners will be able to break the high-score. May the force be with you!';

// Config
$string['version_5.2.0_needed'] = 'Exagames requires at least PHP-Version 5.2.0';

$string['brain_istrue'] = 'True';
$string['brain_isfalse'] = 'False';
$string['brain_noquestions'] = "No questions configured yet!";
$string['brain_continue'] = "Continue";

$string['tiles_difficultyLabel'] = 'Fade';
$string['tiles_difficultyLabel_easy'] = 'Slow';
$string['tiles_difficultyLabel_medium'] = 'Average';
$string['tiles_difficultyLabel_hard'] = 'Fast';
$string['tiles_randomizeButton'] = 'Randomize';
$string['tiles_simulateButton'] = 'Simulate';
$string['tiles_resetButton'] = 'Reset';
$string['tiles_saveButton'] = 'Save';
$string['tiles_saveText'] = 'Configuration saved!';
$string['tiles_noConfig'] = 'No config saved!';
$string['tiles_editConfig'] = 'In progress (not saved)';
$string['tiles_noSquareSelected'] = 'No squares were selected!';
$string['tiles_noDifficultySelected'] = 'No difficulty was selected!';
$string['configurationSaved'] = 'The configuration was saved!';
$string['noConfigurationSaved'] = 'The configuration wasn\'t saved!';
$string['simulateButton'] = 'Simulate';
$string['randomizeButton'] = 'Randomize';
$string['resetButton'] = 'Reset';
$string['saveButton'] = 'Save';
$string['easyDifficulty'] = 'Easy';
$string['interDifficulty'] = 'Medium';
$string['hardDifficulty'] = 'Hard';

// Show top results
$string['showtopresults.header'] = '5 Top Scores:';
$string['showtopresults.settingsHeader'] = 'Show top results';
$string['showtopresults.settingsHeader_help'] = 'Here you can select options for showing of top scores';
$string['showtopresults.select'] = 'Show from';
$string['showtopresults.select_help'] = '**Do not show** The results will not be shown at all

**From all players** Will be shown the top scores from all players who have done this game

**From all player cohorts** Will be shown the top scores from all cohorts the player belongs

**From selected cohort** Will be shown the top scores from the selected cohort

**From all player groups** Will be shown the top scores from all groups the player belongs

**From selected group** Will be shown the top scores from the selected group';
$string['showtopresults.selectCohort'] = 'Choose the group';
$string['showtopresults.selectCohort_help'] = '';
$string['showtopresults.selectGroup'] = 'Choose the group';
$string['showtopresults.selectGroup_help'] = '';

$string['showtopresults.item.none'] = 'Do not show';
$string['showtopresults.item.all'] = 'From all players';
$string['showtopresults.item.byOwnCohorts'] = 'From all player cohorts';
$string['showtopresults.item.bySelectedCohort'] = 'From selected cohort';
$string['showtopresults.item.byOwnGroups'] = 'From all player groups';
$string['showtopresults.item.bySelectedGroup'] = 'From selected group';
$string['showtopresults.noanycohort'] = 'You have no any cohort';
$string['showtopresults.noanygroup'] = 'You have no any group';

// Hide from results
$string['showtopresults.hideUserName'] = 'Hide names';
$string['showtopresults.hideUserName_help'] = 'Hides user names by asterisks, for example: N*** *****';
$string['showtopresults.hideUserName.item.0'] = 'Show full name (John Doe)';
$string['showtopresults.hideUserName.item.1'] = 'Show only first name (John)';
$string['showtopresults.hideUserName.item.2'] = 'Mask the last name (John D***)';
$string['showtopresults.hideUserName.item.3'] = 'Mask first and last names (J*** D***)';


$string['showtopresults.hideTeachers'] = 'Hide teacher results';
$string['showtopresults.hideTeachers_help'] = 'Do not show results of users with \'teacher\' role';

// Main module settings
$string['modsettings.gameparams.header'] = 'Main options';
$string['modsettings.gameparams.header_help'] = '';
$string['modsettings.gameparams.randomizequestions'] = 'Randomize questions';
$string['modsettings.gameparams.randomizequestions_description'] = 'Questions in the game will be randomized by default. Every game has own parameter to change this behavior';
$string['modsettings.gameparams.randomizeanswers'] = 'Randomize answers';
$string['modsettings.gameparams.randomizeanswers_description'] = 'Answers in the game questions will be randomized by default. Every game has own parameter to change this behavior';

// Top scores params
$string['modsettings.topscoresparams.header'] = 'Top scores options';
$string['modsettings.topscoresparams.header_help'] = '';
$string['modsettings.topscoresparams.limit'] = 'Limit to show';
$string['modsettings.topscoresparams.limit_description'] = '';

// Braingame settings
$string['modsettings.braingameparams.header'] = 'Braingame';
$string['modsettings.braingameparams.header_help'] = 'Options for games with "Braingame" type ';
$string['modsettings.braingameparams.durationtime'] = 'Duration time (ms)';
$string['modsettings.braingameparams.durationtime_description'] = 'The time between questions and after the last question. In milliseconds';

$string['questionsettingshdr.settingsHeader'] = 'Question settings';
$string['questionsettingshdr.settingsHeader_help'] = 'Here you can select some options for questions';
$string['randomizeoptions.item.-1'] = 'As default in exagames module settings';
$string['randomizeoptions.item.0'] = 'Disabled';
$string['randomizeoptions.item.1'] = 'Enabled';
$string['questionsettingshdr.randomizequestions.select'] = 'Randomize questions';
$string['questionsettingshdr.randomizequestions.select_help'] = 'Do you need to randomize questions?

**Default** Use the value from "exagames" module settings

**Disabled** Use ordering as in the questions bank

**Enabled** Randomize questions for every game
';
$string['questionsettingshdr.randomizeanswers.select'] = 'Randomize answers';
$string['questionsettingshdr.randomizeanswers.select_help'] = 'Do you need to randomize answers?

**Default** Use the value from "exagames" module settings

**Disabled** Use answer ordering as in the question 

**Enabled** Randomize answers for every question
';

// options for diff options
$string['optionsList.asDefault'] = 'As the default value in module settings';
$string['optionsList.disable'] = 'Disable';
$string['optionsList.enable'] = 'Enable';

$string['exagamestoplistlimit'] = 'Limit of shown scores';
$string['exagamestoplistlimit_help'] = 'Which limit of shown records in the Top scores list (0 - to use default limit from module settings)';

$string['timer.settingsHeader'] = 'Game timers';
$string['timer.settingsHeader_help'] = '';
$string['timer.type'] = 'Used timer type';
$string['timer.type_help'] = '';
$string['timer.type.item.0'] = 'Do not use the timer';
$string['timer.type.item.1'] = 'Timer for every question';
$string['timer.type.item.2'] = 'Timer for whole game';
$string['timer.duration'] = 'Duration (in seconds)';
$string['timer.duration_help'] = '';