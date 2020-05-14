<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 exabis internet solutions <info@exabis.at>
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

$string['configure_quiz'] = 'Quiz konfigurieren';
$string['configure_questions'] = 'Frage konfigurieren';
$string['question_configured'] = 'Frage konfiguriert';
$string['question_not_configured'] = 'Frage nicht konfiguriert';
$string['modulename_help'] = 'The exabis games module imports quizzes of the type "multiple choice" and "true/false" to games to support the execution of these tasks with animations within a game context.

There are 2 games to choose from currently:

* BrainGame: The goal is to answer questions correctly and help the scientist to reach his goal of flying into space!

* Exaclicks: As time goes by, an image becomes more and more visible and supports the student on picking the correct answers to the questions.

NOTE: The Exaclicks game type is configured within the exabis game activity. Any Images uploaded to this course (as resources or inside folders) can be used for the configuration of the questions.';
$string['editor'] = 'Editor';

// Edit Module Instance
$string['attachment'] = 'Filemanager';

$string['exagamesintro'] = 'Einleitung';
$string['exagamesname'] = 'Name';
$string['noquizzesincourse'] = 'Bitte erstelle zuerst {$a->linkTag}einen neuen Test</a>, bevor du ein Exabis Game erstellst!';
$string['savingdata'] = 'Speichere Daten...';
$string['gametype'] = 'Spiel-Typ';
$string['quizid'] = 'Quiz';
$string['question'] = 'Question: ';

$string['gametype_help'] =
'Exabis-Games beinhaltet derzeit 2 Spiele:

* BrainGame - Hier wird die Durchführung von Tests mit Flash-Animationen begleitet. Ziel ist es, den Wissenschaftler durch die korrekten Antworten dabei zu helfen, ins All zu fliegen!
* Exaclick - Hier wird ein Bild mit der Zeit sichtbarer, welches für die Beantwortung der jeweiligen Frage bedeutend ist. Bei Klick auf eine der Lösungsalternativen stoppt die Zeit und die Antwort wird ausgewertet. Hierfür wählen Sie ein Bild (jpg, png, gif) aus dem Repository über den Filemanager, der bei jeder Frage des gewählten Quiz unten gelistet ist.';
$string['quizid_help'] = 'Hier muss der entsprechende Test ausgewählt werden, auf dem das braingame bzw. exaclick aufbauen soll.';
$string['url'] = 'Url';
$string['url_help'] = 'Füge hier den Url zum gamelabs.at-Adventure-Spiel ein um es einzubetten.<br /><br />Diese Option wird nur für Spiele der OpenSource-Plattform gamelabs.at benötigt. gamelabs.at benötigt keine Moodle-Fragen.';
// Games
$string['game_braingame'] = 'BrainGame';
$string['game_tiles'] = 'ExaClick';
$string['game_gamelabs'] = 'gamelabs.at';
$string['game_tiles_rules'] = 'Bist du bereit für die exaclick-Challenge? Versuche zu erkennen was sich hinter den Kacheln versteckt und beantworte die Frage. Folgende Schritte sind erforderlich:<br />1. Klicke “Start” - ein verdecktes Bild erscheint. Die Kacheln fallen laufend herunter und geben mehr und mehr des Bildes/Clips preis.<br />2. Sobald du meinst das Bild zu erkennen, klicke auf “Stop”. Je früher du das machst, desto höher wird dein Score – aber aufgepasst! Wenn du zu bald klickst kannst du die Frage vielleicht noch nicht beantworten.<br />3. Beantworte die Frage bevor die Zeit um ist. Du hast 40 Sekunden Zeit für jede einzelne Frage. Je schwieriger die Frage, desto höher ist dein Score. Bei einer falschen Antwort verlierst du ein Herz.<br />4. Spiel weiter solange du kannst,<br />die Fragen werden immer schwieriger. Es gibt lediglich drei Leben. Nur die Mutigsten schaffen es weiter!';

// Config
$string['version_5.2.0_needed'] = 'Exagames benötigt mindestens PHP-Version 5.2.0';

$string['brain_istrue'] = 'Wahr';
$string['brain_isfalse'] = 'Falsch';
$string['brain_noquestions'] = "Noch keine Fragen definiert!";
$string['brain_continue'] = "Weiter";

$string['tiles_difficultyLabel'] = 'Geschwindigkeit';
$string['tiles_difficultyLabel_easy'] = 'Langsam';
$string['tiles_difficultyLabel_medium'] = 'Mittel';
$string['tiles_difficultyLabel_hard'] = 'Schnell';
$string['tiles_randomizeButton'] = 'Zufällig';
$string['tiles_simulateButton'] = 'Simulieren';
$string['tiles_resetButton'] = 'Reset';
$string['tiles_saveButton'] = 'Speichern';
$string['tiles_saveText'] = 'Konfiguration gespeichert!';