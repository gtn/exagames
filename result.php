<?php

require_once("inc.php");
echo '<link rel="stylesheet" href="css/index.css">';
$id = optional_param('cmid', 0, PARAM_INT); // Course Module ID, or


$out = array();
global $COURSE, $CFG, $DB, $USER;
$img_files = array();
$responses = function_exists('optional_param_array') ? optional_param_array('responses', array(), PARAM_TEXT) : optional_param('responses', array(), PARAM_RAW);
if ($id) {
    if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
        print_error("Course Module ID was incorrect");
    }
    
    
    
    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error("Course is misconfigured");
    }
    
    
    if (! $game = precheck_get_game_instance($cm->instance)) {
        error("Game not found");
    }
    
} else {
//     print_error("Course module is incorrect");
}

require_login($course->id);




//--------------------------------------------------------------------pool3 action
$json_string = file_get_contents('./result.json');

// problem mit umlauten
$json_a = json_decode($json_string, true);

$updateGrade = new StdClass;
$updateGrade->rawgrade = $json_a['TrainingsResultInPercent'];
$updateGrade->feedback = "Attempts: ".$json_a['Attempts']."\nTime needed: ". $json_a['TotalTimeInSeconds'];
$updateGrade->userid = $USER->id;

$resret = $DB->get_record("precheck_data", array("itemid"=>$cm->instance, "userid"=>$USER->id));

// precheck_grade_item_update($game, $updateGrade);
// precheck_save_data($json_string, $cm->instance);






$context = get_context_instance(CONTEXT_COURSE, $game->course);



add_to_log($course->id, "precheck", "view", "view.php?id=$cm->id", "$game->id");

/// Print the page header
$strexagamess = get_string("modulenameplural", "precheck");
$strexagames  = get_string("modulename", "precheck");

$navlinks = array();
$navlinks[] = array('name' => $strexagamess, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($game->name), 'link' => '', 'type' => 'activityinstance');


$PAGE->set_url($_SERVER['REQUEST_URI']);
$PAGE->requires->js('/mod/precheck/js/swfobject.js', true);

$stringman = get_string_manager();
$strings = $stringman->load_component_strings('mod_precheck', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'mod_precheck');

echo $OUTPUT->header();

//$context = get_context_instance(CONTEXT_COURSE, $game->course);
// $context = context_module::instance($cm->id);

// precheck_print_tabs($game, 'show');

/// Print the main part of the page

//var_dump($result);
$html = '<ul id="resulttable">';
$results = json_decode($resret->data, true);

    foreach($results as $key => $value){
        if(is_array($value)){
            $html = recList($value, $html, $key);
        } else {
            $html .= '<li>'.$key.': '. $value .'</li>';
        }
    }
   $html .= '</ul>';
   
   echo $html;
   
   function recList($results, $html, $prevkey){
       $html .= '<li><span class="caret">'.$prevkey.'</span>';
       $html .= '<ul class="inner">';
       foreach($results as $key => $value){
           if(is_array($value) && !empty($value)){
               $html = recList($value, $html, $key);
           } else {
               $html .= '<li>'.$key.': '. $value .'</li>';
           }
           
       }
       
       $html .= '</ul>';
       $html .= '</li>';
       return $html;
   }
   ?>
   <script>
   var toggler = document.getElementsByClassName("caret");
   var i;
   
   for (i = 0; i < toggler.length; i++) {
       toggler[i].addEventListener("click", function() {
           this.parentElement.querySelector(".inner").classList.toggle("active");
           this.classList.toggle("caret-down");
       });
   }
   </script>
<?php

/// Finish the page
echo $OUTPUT->footer();
