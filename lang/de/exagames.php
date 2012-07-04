<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 exabis internet solutions <info@exabis.at>
*  All rights reserved
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

// Edit Module Instance
$string['exagamesintro'] = 'Einleitung';
$string['exagamesname'] = 'Name';
$string['noquizzesincourse'] = 'Bitte erstelle zuerst {$a->linkTag}einen neuen Test</a>, bevor du ein Exabis Game erstellst!';
$string['savingdata'] = 'Speichere Daten...';
$string['gametype'] = 'Spiel-Typ';
$string['quizid'] = 'Quiz';
$string['gametype_help'] = 'Die Exabis-Games bestehen derzeit aus drei verschiedenen Spielen: 

* BrainGame - hier werden Moodle-Tests mit einer lustigen Flash-Animation zusammengefügt. Ziel ist es, den Wissenschaftler in das Weltall zu katapultieren, sonst landet er im Wasser!
* ExaClick deckt für den Spieler ein verstecktes Bild/Clip schrittweise auf. Klickt der Spieler auf "Stop", bleibt die Zeit stehen und eine Moodle-Frage, die zuvor konfiguriert werden muss, erscheint. Die Fragekonfiguration erfolgt über den Editor und dem Tab "Frage konfigurieren".<br /><br />ACHTUNG: das Spiel akzeptiert nur Links zu Dateien die in Moodle für Kursteilnehmer/innen freigegeben wurden, z.B. innerhalb eines Kursverzeichnisses. Das Bild muss zuvor upgeloadet werden.
* gamelabs.at - diese Spieleplattform macht den Spieleentwicklungsprozess erlebbar! Erstellte Spiele (von Trainer/innen bzw. Teilnehmer/innen) können direkt im Kurs eingebettet werden.';
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