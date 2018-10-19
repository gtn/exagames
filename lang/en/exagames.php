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
// Games
$string['game_braingame'] = 'braingame';
$string['game_tiles'] = 'exaclick';
$string['game_gamelabs'] = 'gamelabs.at';
$string['game_tiles_rules'] = 'Are you ready for the exaclick challenge? Try to recognize what you see on these pictures and answer the questions. Here is what you need to do:<br />1. Click “Start” and a hidden image will appear. Tiles will gradually drop revealing more and more of the image/clip.<br />2. If you think you know enough, click “Stop”, the earlier you click “Stop” the higher your score will be. – But watch out!  If you click too early you might not yet see enough to answer the question!<br />3. Answer the pertaining question before time runs out.  You have 40 seconds to answer each question. You will get a higher score for more difficult questions and the faster you answer. Give a wrong answer and you will lose a life.<br />4. Keep on going until the end of the game<br />The questions will get more and more difficult. Be careful, you only have three lives. Only the most courageous and clever learners will be able to break the high-score. May the force be with you!';

// Config
$string['version_5.2.0_needed'] = 'Exagames requires at least PHP-Version 5.2.0';
