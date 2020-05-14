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
$string['pluginadministration'] = 'Administración de Exabis Games';

$string['question'] = 'Pregunta: ';
$string['configure_quiz'] = 'Configura el cuestionario';
$string['configure_questions'] = 'Configura las preguntas';
$string['question_configured'] = 'Pregunta configurada';
$string['question_not_configured'] = 'Pregunta no configurada';
$string['modulename_help'] = 'El módulo Exabis Games importa cuestionarios del tipo "Elección múltiple" o "Verdadero/Falso y los convierte en juegos para dar soporte a la ejecución de esas tareas con animaciones dentro de un contexto de juego.

En estos momentos hay 2 tipos de juegos que se pueden escoger:

* BrainGame: El objetivo es contestar correctamente, ayudando así al científico a alcanzar su meta de volar al espacio!

* Exaclicks: Según va pasando el tiempo, una imagen se haciendo más y más visible, ayudando al estudiante a escoger la respuesta correcta a las preguntas.

NOTA: El tipo de juego Exaclicks se configura dentro de una actividad Exabis Game. Cualquier imagen subida a este curso (como recursos o carpetas internas) puede ser usada para la configuración de las preguntas.';

// Edit Module Instance
$string['exagamesintro'] = 'Introducción';
$string['exagamesname'] = 'Nombre';
$string['noquizzesincourse'] = 'Por favor, crea {$a->linkTag}un nuevo cuestionario</a> primero, antes de que puedas crear un Exabis Game!';
$string['savingdata'] = 'Guardando datos...';
$string['gametype'] = 'Tipo de juego';
$string['quizid'] = 'Cuestionario';
$string['gametype_help'] = 'Exabis-Games incluye actualmente dos tipos de actividades:

* braingame - Aquí se admite la implementación de pruebas con animaciones Flash. El objetivo es ayudar al científico con las respuestas correctas para volar al espacio!
* exaclick - Aquí una imagen se va volviendo más y más visible conforme pasa el tiempo, lo cual ayuda a responder la correspondiente pregunta. Cuando hace clic en una de las posibles soluciones, el tiempo se detiene y se evalúa la respuesta. Las preguntas para este juego deben configurarse previamente en la pestaña "Configurar preguntas".<br /><br /> NOTA: Sólo estos tipos de imágenes (jpg, png, gif) se admiten en el configurador, que se almacenan en el mismo curso que un recurso o en una carpeta en la que también tiene lugar la actividad Exagames.';
$string['quizid_help'] = 'Seleccione un cuestionario para usar en este juego Exagames.';
$string['url'] = 'Url';
$string['url_help'] = 'Pega tu aventura gamelabs.at aquí para incrustarla.<br /><br />Esta opción funcionará solamente con un enlace gamelabs-game y no necesita preguntas de Moodle.';
// Games
$string['game_braingame'] = 'braingame';
$string['game_tiles'] = 'exaclick';
$string['game_gamelabs'] = 'gamelabs.at';
$string['game_tiles_rules'] = 'Estás preparado para el reto Exaclick? Intenta reconoces lo que ves en estas imágenes y contesta las preguntas. Esto es lo que tienes que hacer:<br />1. Haz clic en “Start” y aparecerá una imagen escondida. Los azulejos se irán cayendo progresivamente revelando cada vez más partes de la imagen.<br />2. Cuando creas que ya sabes la respuesta, haz clic en “Stop”. cuanto antes hagas clic en “Stop”, tu puntuación será más alta. – Pero ¡cuidado!  Si haces clic demasiado pronto podrías no ver lo suficiente para contestar a la pregunta!<br />3. Conesta a la pregunta antes de que se acabe el tiempo.  Tienes 40 segundos para contestar cada pregunta. Conseguirás más puntos por las preguntas más difíciles y cuanto antes contestes. Si das una respuesta incorrecta perderás una vida.<br />4. Sigue hasta completar el juego<br />Las preguntas irán aumentando en dificultad progresivamente. Ten cuidado porque sólo tienes 3 vidas. Sólo los más valientes e inteligentes podrán alcanzar o superar la máxima puntuación. ¡Que la fuerza te acompañe!';

// Config
$string['version_5.2.0_needed'] = 'Exagames requiere al menos la versión 5.2.0 de PHP';

$string['brain_istrue'] = 'Verdadero';
$string['brain_isfalse'] = 'Falso';
$string['brain_noquestions'] = "Aun no se ha configurado ninguna pregunta!";
$string['brain_continue'] = "Aceptar";

$string['tiles_difficultyLabel'] = 'Fade';
$string['tiles_difficultyLabel_easy'] = 'Slow';
$string['tiles_difficultyLabel_medium'] = 'Average';
$string['tiles_difficultyLabel_hard'] = 'Fast';
$string['tiles_randomizeButton'] = 'Randomize';
$string['tiles_simulateButton'] = 'Simulate';
$string['tiles_resetButton'] = 'Reset';
$string['tiles_saveButton'] = 'Save';
$string['tiles_saveText'] = 'Configuration saved!';