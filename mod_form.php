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

require_once('moodleform_mod.php');

class mod_exagames_mod_form extends moodleform_mod
{

	protected $replaceQuizId = 0;

    function definition()
    {

        global $COURSE, $CFG, $DB, $OUTPUT, $PAGE, $USER;
        $mform =& $this->_form;
//-------------------------------------------------------------------------------
        $stringman = get_string_manager();
        $strings = $stringman->load_component_strings('mod_exagames', $CFG->lang);

        $PAGE->requires->strings_for_js(array_keys($strings), 'mod_exagames');


        /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('exagamesname', 'exagames'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements(null);
        /// Adding the optional "intro" and "introformat" pair of fields
        /*
        $mform->addElement('htmleditor', 'intro', get_string('exagamesintro', 'exagames'));
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'introformat', get_string('format'));
        */

	    $allowedQuestionTypes = ['multichoice', 'truefalse'];

        // Quiz Dropdown
        $quizzes = array();
        $questionBankNames = array();
        $qtest = array();
        //$exagame = $DB->get_record('exagames', ['id'=>$PAGE->cm->instance]);
        //$exagame->quizid = optional_param('quizid', $exagame->quizid, PARAM_TEXT);
        if ($recs = $DB->get_records_sql("
                SELECT ca.* 
                FROM {$CFG->prefix}question_bank_entries as en 
                    INNER JOIN {$CFG->prefix}question_categories as ca on en.questioncategoryid = ca.id 
                GROUP BY en.questioncategoryid
                ORDER BY ca.name")) {

            foreach ($recs as $key => $rec) {
				/*$qTestRec = $DB->get_records_sql("
                    SELECT qu.* FROM {$CFG->prefix}question_bank_entries as ba
                        INNER JOIN {$CFG->prefix}question_versions as qv on ba.id = qv.questionbankentryid
                        INNER JOIN {$CFG->prefix}question as qu on qu.id = qv.questionid 
                    WHERE ba.questioncategoryid = ? AND qu.qtype IN ('".implode('\', \'', $allowedQuestionTypes)."')",
                    [intval($rec->id)]);*/
                $qTestRec = $DB->get_records_sql("
			        SELECT qu.* 
			                FROM {$CFG->prefix}question_bank_entries as en
			                    INNER JOIN {$CFG->prefix}question_versions as qv on en.id = qv.questionbankentryid
			                    INNER JOIN {$CFG->prefix}question as qu on qv.questionid = qu.id 
			                WHERE en.questioncategoryid = ?
			                  	AND qu.qtype IN ('".implode('\', \'', $allowedQuestionTypes)."')
			                    AND qv.id = (
			                        SELECT MAX(qv_inner.id)
			                            FROM {$CFG->prefix}question_versions AS qv_inner
			                            WHERE qv_inner.questionbankentryid = qv.questionbankentryid
			                                AND qv_inner.status = 'ready'
			                    );
    			", [intval($rec->id)]);

	            // ignore empty quizzes (with no any suitable type questions)
	            if (!$qTestRec || !count($qTestRec)) {
					continue;
	            }
                $qtest[$key] = $qTestRec;
                $quizzes[$rec->id] = $rec->name;
                $qDetails = new stdClass();
                $qNameArr = array();
                foreach ($qtest[$key] as $q) {
                    $curDetails = $DB->get_record(
                            'exagames_question',
                            array('id' => $q->id),
                            'difficulty, display_order, content_url',
                            IGNORE_MISSING);
                    if ($curDetails) {
                        $qDetails->difficulty = $curDetails->difficulty;
                        $qDetails->display_order = $curDetails->display_order;
                        $qDetails->content_url = $curDetails->content_url;
                        $qDetails->name = $q->name;
                        $qDetails->id = $q->id;
                    } else {
                        $qDetails = new stdClass();
                        $qDetails->difficulty = "";
                        $qDetails->display_order = "";
                        $qDetails->content_url = "";
                        $qDetails->name = $q->name;
                        $qDetails->id = $q->id;
                    }
                    $qNameArr[] = clone $qDetails;
                }
                $qObject = new stdClass();
                $qObject->questionDetails = $qNameArr;
                $qObject->quizName = $rec->name;

                $questionBankNames[$rec->id] = $qObject;
            }

        }

        /*$questions = array();
        $quizzes_questionNames = array();
        $firstQuizId = null;
        $urlQuizId = optional_param('quizId', 0, PARAM_INT); // Course Module ID, or
        if ($urlQuizId != null) $firstQuizId = $urlQuizId;
        if ($recs = $DB->get_records_select('quiz', "course='$COURSE->id'", null, 'name', 'id,name')) {
            foreach ($recs as $rec) {
                if ($firstQuizId == null) {
                    $firstQuizId = $rec->id;
                }
                $questions[$rec->id] = $rec->name;
                $quizObj->preload_questions();
                $quizObj->load_questions();
                $qDetails = new stdClass();
                $qNameArr = array();
                foreach ($quizObj->get_questions() as $q) {
                    $curDetails = $DB->get_record('exagames_question', array('id' => $q->id), $fields = 'difficulty, display_order, content_url', $strictness = IGNORE_MISSING);
                    if($curDetails) {
                        $qDetails->difficulty = $curDetails->difficulty;
                        $qDetails->display_order = $curDetails->display_order;
                        $qDetails->content_url = $curDetails->content_url;
                        $qDetails->name = $q->name;
                        $qDetails->id = $q->id;
                    } else {
                        $qDetails = new stdClass();
                        $qDetails->difficulty = "";
                        $qDetails->display_order = "";
                        $qDetails->content_url = "";
                        $qDetails->name = $q->name;
                        $qDetails->id = $q->id;
                    }
                    $qNameArr[] = clone $qDetails;
                }
                $qObject = new stdClass();
                $qObject->questionDetails = $qNameArr;
                //	$temp = $DB->get_record('exagames_question', array ('id'=>), $fields='*', $strictness=IGNORE_MISSING);

                $qObject->quizName = $rec->name;

                $quizzes_questionNames[$rec->id] = $qObject;
            }
        }*/

        /*if (!$questions) {
            // dirty as moodle: link to add quiz if no quiz was found in this course!
            $return  = optional_param('return', 0, PARAM_BOOL);
            $type    = optional_param('type', '', PARAM_ALPHANUM);
            $section = required_param('section', PARAM_INT);

            $a = new StdClass;
            $a->linkTag = '<a href="'.$CFG->wwwroot.'/course/modedit.php?add=quiz&type='.$type.'&course='.$COURSE->id.'&section='.$section.'&return='.$return.'">';
            $redirect = $CFG->wwwroot.'/course/modedit.php?add=quiz&type='.$type.'&course='.$COURSE->id.'&section='.$section.'&return='.$return;
            exagames_print_error('noquizzesincourse', 'exagames', $redirect, $a);
        }*/

        // selected quiz
//        $selectedQuiz = $this->optional_param('quizid', 0, PARAM_INT);
        $selectedQuiz = optional_param('quizid', 0, PARAM_INT);
        if (!$selectedQuiz) {
            $selectedQuiz = isset($this->get_current()->quizid) ? $this->get_current()->quizid : 0;
        }
		if (!$selectedQuiz) {
            $selectedQuiz = array_key_first($quizzes);
        }
        $this->replaceQuizId = $selectedQuiz;

        $mform->addElement('select', 'quizid', get_string('modulename', 'quiz'), $quizzes, ['onChange' => 'handleQuizSelectParam();']);
        $mform->addHelpButton('quizid', 'quizid', 'exagames');
        $mform->addRule('quizid', null, 'required', null, 'client');

        $games = array(
            'braingame' => get_string('game_braingame', 'exagames'),
            'tiles' => get_string('game_tiles', 'exagames')
        );

        //element type, key, language, options

        $mform->addElement('select', 'gametype', get_string('gametype', 'exagames'), $games);
        $mform->addHelpButton('gametype', 'gametype', 'exagames');

        //$quizLen = count($questionBankNames[$exagame->quizid]->questionDetails);
//        foreach ($questionBankNames as $quizKey => $questions) {

		// use only selected quiz.
	    if ($selectedQuiz && isset($questionBankNames[$selectedQuiz])) {
            $quizKey = $selectedQuiz;
            $questions = $questionBankNames[$selectedQuiz];
            foreach ($questions->questionDetails as $questKey => $qDetails) {
                $content_url = $qDetails->content_url;
                $display_order = $qDetails->display_order;
                $difficulty = $qDetails->difficulty;
                $question_id = $qDetails->id;

                $tilesEditor = [];

                $urlParams = '?';
                $urlParams .= isset($content_url) && $content_url != null ? "content_url=$content_url&" : "";
                $urlParams .= isset($display_order) && $display_order != null ? "display_order=$display_order&" : "";
                $urlParams .= isset($difficulty) && $difficulty != null ? "difficulty=$difficulty&" : "";
                $urlParams .= isset($question_id) && $question_id != null ? "question_id=$question_id&" : "";
                $urlParams .= isset($responses) && $responses != null ? "$responses" : "";

                // save the language strings as well, since M.util does not work
                $urlParams .= "no_config_safed_text=" . get_string('tiles_noConfig', 'exagames') . "&";
                $urlParams .= "config_safed_text=" . get_string('tiles_saveText', 'exagames') . "&";
                $urlParams .= "edit_config_safed_text=" . get_string('tiles_editConfig', 'exagames') . "&";
                $urlParams .= "no_square_selected_text=" . get_string('tiles_noSquareSelected', 'exagames') . "&";
                $urlParams .= "no_difficulty_selected_text=" . get_string('tiles_noDifficultySelected', 'exagames') . "&";
                $urlParams .= "configuration_saved=" . get_string('configurationSaved', 'exagames') . "&";
                $urlParams .= "no_configuration_saved=" . get_string('noConfigurationSaved', 'exagames') . "&";
                $urlParams .= "randomize_button=" . get_string('randomizeButton', 'exagames') . "&";
                $urlParams .= "simulate_button=" . get_string('simulateButton', 'exagames') . "&";
                $urlParams .= "reset_button=" . get_string('resetButton', 'exagames') . "&";
                $urlParams .= "save_button=" . get_string('saveButton', 'exagames') . "&";
                $urlParams .= "diff_easy=" . get_string('easyDifficulty', 'exagames') . "&";
                $urlParams .= "diff_int=" . get_string('interDifficulty', 'exagames') . "&";
                $urlParams .= "diff_hard=" . get_string('hardDifficulty', 'exagames') . "&";

                $tilesEditor[] = $mform->createElement("html", '
											<div id="tileEditor-' . $quizKey . '-quest-' . $questKey . '" style="width: 940px; height:600px">
												<iframe class="editor_frames" id="editorframe-' . $quizKey . '-quest-' . $questKey . '" frameborder="0" style="height:600px;width:100%" src="' . $CFG->wwwroot . '/mod/exagames/html5/form_editor/tiles.html' . $urlParams . '"></iframe></br>
											</div>');
                $tilesEditor[] = $mform->createElement('filemanager', 'attachments', get_string('attachment', 'exagames'), null,
                    array('subdirs' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
                        'accepted_types' => array('.jpg', '.png', '.gif'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL));

                $mform->addGroup($tilesEditor, 'Editor', get_string('question', 'exagames') . '"' . $qDetails->name . '"');

            }
        }


        $url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        //	$_SERVER['REQUEST_SCHEME'] . '//' . $_SERVER['SERVER_NAME']


        ?>
        <!--<script src="/mod/exagames/html5/js/jquery.min.js"></script>-->
        <script src="https://code.jquery.com/jquery-3.6.0.js"
                integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
        <script>
            let quizzes = <?php echo json_encode($questionBankNames); ?>;
            let url_string = window.location.href;
            let url = new URL(url_string);
            var cur_quizid = url.searchParams.get("quizid");
            var loc = <?php echo json_encode($url); ?>;

            $(document).ready(function () {
                handleGameTypeParam();
                handleQuizSelectParam(false);

                $('#id_quizid').prop('disabled', false);
                $('#id_gametype').prop('disabled', false);
                
                $('#id_gametype').on('change', function () {
                    handleGameTypeParam();
                });

                $('#id_quizid').on('change', function () {
                    handleQuizSelectParam(true);
                });

                $('.mainContainer').on('load', function() {
                    alert("test");
                    alert("<?php echo get_string('tiles_difficultyLabel_hard', 'exagames'); ?>");
                    $("iframe").contents().find('#difficultyLabel').html("<?php echo get_string('tiles_difficultyLabel', 'exagames'); ?>");
                    $("iframe").contents().find('#saveButton').html("<?php echo get_string('tiles_saveButton', 'exagames'); ?>");
                    $("iframe").contents().find('#randomizeButton').html("<?php echo get_string('tiles_randomizeButton', 'exagames'); ?>");
                    $("iframe").contents().find('#simulateButton').html("<?php echo get_string('tiles_simulateButton', 'exagames'); ?>");
                    $("iframe").contents().find('#saveText').html("<?php echo get_string('tiles_saveText', 'exagames'); ?>");
                    $("iframe").contents().find('#resetButton').html("<?php echo get_string('tiles_resetButton', 'exagames'); ?>");
                    $("iframe").contents().find("#difficultyForm input[value='easy']")
                        .next()
                        .html("<?php echo get_string('tiles_difficultyLabel_easy', 'exagames'); ?>");
                    $("iframe").contents().find("#difficultyForm input[value='intermediate']")
                        .next()
                        .html("<?php echo get_string('tiles_difficultyLabel_medium', 'exagames'); ?>");
                    $("iframe").contents().find("#difficultyForm input[value='hard']")
                        .next()
                        .html("<?php echo get_string('tiles_difficultyLabel_hard', 'exagames'); ?>");
                    $("iframe").contents().find('#difficultyLabel').html("<?php echo get_string('tiles_difficultyLabel', 'exagames'); ?>");
                });
            });

            function handleGameTypeParam() {
                switch($('#id_gametype').val()) {
                    case 'tiles':
                        $('.initial-hide').show();
                        handleQuizSelectParam(false);
                        break;
                    case 'braingame':
                        $("div[id*=tileEditor]").parent().parent().parent().parent().css('display', 'none');
                        break;
                }
            }

            function handleQuizSelectParam(pageReload = false) {
                var selectedQuizId = $('#id_quizid').val();
                // 1. hide tile editor
                $("div[id*=tileEditor]").parent().parent().parent().parent().css('display', 'none');
                // 2. reload the form
	            if (pageReload) {
                    $('#id_quizid').closest('form').css('opacity', '0.25');
                    var url = new URL(window.location);
                    url.searchParams.set('quizid', selectedQuizId);
                    window.location.href = url.toString();
                } else {
                    // 3. useless after page reloading - show tile editors if it is 'tiles' game type
                    if ($('#id_gametype').val() == 'tiles') {
                        $("div[id*=tileEditor-" + selectedQuizId + "]").parent().parent().parent().parent().css('display', 'block');
                        $("div[id*=tileEditor-" + selectedQuizId + "]").css('display', 'block');

                        $(".filemanager").show();
                        $(".form-filetypes-descriptions").show();

                        /*                    setTimeout(function() {
                                                $('#id_quizid').trigger('change');
                                            }, 500);*/
                    }
                }
            }

            function getLoadSwitchRef() {
                return $('#id_quizid');
            }

            function a(frameId) {
                return $('#' + frameId).parent().parent().find(".fm-content-wrapper > .fp-content");
            }

            function getFMImage(frameId) {
                return new Promise((resolve, reject) => {
                    let fileElement = $('#' + frameId).parent().parent().find('.fp-file .fp-thumbnail > img');
                    if (fileElement.length) {
                        let imagePath = $(fileElement).attr('src');
                        resolve(imagePath);
                    } else {
                        let elapsedTime = 0;
                        let intervalId;

                        intervalId = setInterval(function() {
                            fileElement = $('#' + frameId).closest('.felement').find('.filemanager img.realpreview').first();

                            if (fileElement.length > 0) {
                                clearInterval(intervalId);
                                let imagePath = $(fileElement).attr('src');
                                resolve(imagePath);
                            }

                            elapsedTime += 50;

                            if (elapsedTime >= 1000) {
                                clearInterval(intervalId);
                                resolve(''); // return an empty string if not found
                            }
                        }, 50); // check every 50 ms
                    }
                });
            }

            function getFMImageOld(frameId) {
                let fileElement = $('#' + frameId).parent().parent().find('.fp-file .fp-thumbnail > img');
                if (fileElement.length) {
                    let imagePath = $($(fileElement)[0]).attr('src');
                    return imagePath;
                } else {
                    // check element no more than 1 second
                    var elapsedTime = 0;
                    var intervalId;
                    var imagePath = '';
                    intervalId = setInterval(function() {
                        fileElement = $('#' + frameId).closest('.felement').find('.filemanager img.realpreview').first();

                        if (fileElement.length > 0) {
                            // exists!
                            imagePath = $(fileElement).attr('src');
                            if (imagePath) {
                                imagePath = trimURLParamsFromMedia(imagePath);
                            }
                            return imagePath;
                        }
                        elapsedTime += 50;

                        if (elapsedTime >= 1000) { // 1 second - limit
                            clearInterval(intervalId);
                            return '';
                        }
                    }, 50); // every 50 ms
                }

            }

            function trimURLParamsFromMedia(str) {
                if (str.includes(".png")) {
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
                return window.location.href;
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
                var urlparts = url.split('?');
                if (urlparts.length >= 2) {

                    var prefix = encodeURIComponent(parameter) + '=';
                    var pars = urlparts[1].split(/[&;]/g);

                    //reverse iteration as may be destructive
                    for (var i = pars.length; i-- > 0;) {
                        //idiom for string.startsWith
                        if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                            pars.splice(i, 1);
                        }
                    }

                    url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
                    return url;
                } else {
                    return url;
                }
            }

        </script>
        <?php

        if ((optional_param('func', '', PARAM_TEXT) == 'configure_question') && ($questionId = optional_param('questionid', '', PARAM_INT)) && ($content_url = optional_param('content_url', '', PARAM_TEXT))) {
            // $content_url contains an url into DRAFT file, so we need to save it directly:
            if ($content_url && strpos($content_url, '/user/draft/') !== false ) { // only for DRAFT urls
                $parsed_url = parse_url($content_url);
                $path = $parsed_url['path'];
                $dynamic_part = substr($path, strpos($path, "exagames/lib/file_load.php/") + strlen("exagames/lib/file_load.php/"));
                $parts = explode('/', $dynamic_part);
                $contextid = $parts[0];
                $context = context::instance_by_id($contextid);
                $component = $parts[1];
                $filearea = $parts[2];
                $draftid = $parts[3];
                $filename = urldecode($parts[4]);
                $fullpath = "/$context->id/$component/$filearea/$draftid/$filename";
                $filehash = sha1($fullpath);

                $fs = get_file_storage();
                $draftFile = $fs->get_file_by_hash($filehash);
                if ($draftFile) { // only if all ok with draft file
                    $draftContent = $draftFile->get_content();
                    // save to real fixed filearea
                    $newComponent = 'mod_exagames';
                    $newFilearea = 'question_content';
                    $file_record = array(
                        'contextid' => $contextid,
                        'component' => $newComponent,
                        'filearea' => $newFilearea,
                        'itemid' => $questionId,
                        'filepath' => '/',
                        'filename' => $filename,
                        'timecreated' => time(),
                        'timemodified' => time(),
                    );

                    // Remove files: we need only single file for every question
                    $fs->delete_area_files($contextid, $newComponent, $newFilearea, $questionId);
                    // And new file create
                    $new_file = $fs->create_file_from_string($file_record, $draftContent);

                    $content_partUrl = implode('/', [$contextid, $newComponent, $newFilearea, $questionId, $filename]);
                    $mainLength = strpos($content_url, "exagames/lib/file_load.php/") + strlen("exagames/lib/file_load.php/");
                    $mainUrl = substr($content_url, 0, $mainLength);
                    $mainUrl .= $content_partUrl;

                    $content_url = $mainUrl;
                }
            }

            $questionConfig = new stdClass();
            $questionConfig->id = $questionId;
            $questionConfig->content_url = $content_url;
            $questionConfig->tile_size = optional_param('tile_size', '', PARAM_TEXT);
            $questionConfig->difficulty = optional_param('difficulty', '', PARAM_TEXT);
            $questionConfig->display_order = optional_param('display_order', '', PARAM_TEXT);
            if (!$DB->record_exists('exagames_question', array('id' => $questionId))) {
                $DB->execute("
                    INSERT INTO {$CFG->prefix}exagames_question (id, tile_size, content_url, difficulty, display_order) 
                    VALUES ({$questionConfig->id}, '', '', '', '')
                ");
            }

            $DB->update_record('exagames_question', $questionConfig);

            echo "ok=1";
            exit;
        }

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

	    // To show TOP results
        $mform->addElement('header', 'showtopresultshdr',
            get_string('showtopresults.settingsHeader', 'exagames'));
        $mform->addHelpButton('showtopresultshdr', 'showtopresults.settingsHeader', 'exagames');

        // Get all cohorts
        require_once ($CFG->dirroot.'/cohort/lib.php');
        $cohorts = cohort_get_all_cohorts();
        $cohortstoselect = [];
        if (@$cohorts['cohorts']) {
			foreach ($cohorts['cohorts'] as $cohort) {
				if ($cohort->visible) {
                    $cohortstoselect[$cohort->id] = $cohort->name;
				}
            }
		}
        // Get all groups (for the course)
        $groups = groups_get_all_groups($COURSE->id);
        $groupstoselect = [];
        if (@$groups) {
			foreach ($groups as $group) {
                $groupstoselect[$group->id] = $group->name;
            }
		}

		// type selector
        $topresults = array(
            'none' => get_string('showtopresults.item.none', 'exagames'),
            'all' => get_string('showtopresults.item.all', 'exagames'),
            'ownCohorts' => get_string('showtopresults.item.byOwnCohorts', 'exagames'),
            'selectedCohort' => get_string('showtopresults.item.bySelectedCohort', 'exagames'),
            'ownGroups' => get_string('showtopresults.item.byOwnGroups', 'exagames'),
            'selectedGroup' => get_string('showtopresults.item.bySelectedGroup', 'exagames'),
        );
        $mform->addElement('select', 'showtopresults', get_string('showtopresults.select', 'exagames'), $topresults);
        $mform->addHelpButton('showtopresults', 'showtopresults.select', 'exagames');

	    // Cohort selector
        if (@$cohorts['cohorts']) {
            $mform->addElement('select', 'showtopresultscohort', get_string('showtopresults.selectCohort', 'exagames'), $cohortstoselect);
            // Hide 'showtopresultscohort' unless 'showtopresults' has the value 'selectedGroup'
            $mform->hideIf('showtopresultscohort', 'showtopresults', 'neq', 'selectedCohort');
        } else {
            $mform->addElement('static', 'noanycohortmessage', '', html_writer::tag('span', get_string('showtopresults.noanycohort', 'exagames'), ['class' => 'text-danger']));
            // Hide 'noanycohortmessage' unless 'showtopresults' has the value 'selectedGroup'
            $mform->hideIf('noanycohortmessage', 'showtopresults', 'neq', 'selectedCohort');
        }

		// Group selector
        if (@$groups) {
            $mform->addElement('select', 'showtopresultsgroup', get_string('showtopresults.selectGroup', 'exagames'), $groupstoselect);
            // Hide 'showtopresultsgroup' unless 'showtopresults' has the value 'selectedGroup'
            $mform->hideIf('showtopresultsgroup', 'showtopresults', 'neq', 'selectedGroup');
        } else {
            $mform->addElement('static', 'noanygroupmessage', '', html_writer::tag('span', get_string('showtopresults.noanygroup', 'exagames'), ['class' => 'text-danger']));
            // Hide 'noanygroupmessage' unless 'showtopresults' has the value 'selectedGroup'
            $mform->hideIf('noanygroupmessage', 'showtopresults', 'neq', 'selectedGroup');
        }
		// 'required' rule - does not work well still
//        $mform->addRule('showtopresultscohort', get_string('required'), 'required', null, 'client');

		// Secure name toggler
        $mform->addElement('advcheckbox', 'securenames', get_string('showtopresults.hideUserName', 'exagames'));
        $mform->addHelpButton('securenames', 'showtopresults.hideUserName', 'exagames');
		$mform->setDefault('securenames', 0);
        $mform->hideIf('securenames', 'showtopresults', 'eq', 'none');

	    // Hide teachers toggler
        $mform->addElement('advcheckbox', 'hideteachers', get_string('showtopresults.hideTeachers', 'exagames'));
        $mform->addHelpButton('hideteachers', 'showtopresults.hideTeachers', 'exagames');
        $mform->setDefault('hideteachers', 0);
        $mform->hideIf('hideteachers', 'showtopresults', 'eq', 'none');

		//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();

		//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }

    public function get_data()
    {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            // Turn off completion settings if the checkboxes aren't ticked.
            $autocompletion = !empty($data->{'completion'.$suffix}) && $data->{'completion'.$suffix} == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->{'completionminscoreenabled'.$suffix}) || !$autocompletion) {
                $data->{'completionminscore'.$suffix} = 0;
            }
        }
        return $data;
    }

    public function definition_after_data() {
        $mform =& $this->_form;

        // Force the quiz id by needed value
        if ($this->replaceQuizId) {
            $mform->setDefault('quizid', $this->replaceQuizId);
        }

        parent::definition_after_data();

    }

    public function data_preprocessing(&$toform) {

		parent::data_preprocessing($toform);

        $suffix = $this->get_suffix();
        $completionminscoreel = 'completionminscore' . $suffix;
        if (empty($toform[$completionminscoreel])) {
            $toform[$completionminscoreel] = 0;
        } else {
            $completionminscoreenabledel = 'completionminscoreenabled' . $suffix;
            $toform[$completionminscoreenabledel] = $toform[$completionminscoreel] > 0;
        }

    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked.
            $suffix = $this->get_suffix();
            $completion = $data->{'completion' . $suffix};
            $autocompletion = !empty($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->{'completionminscoreenabled' . $suffix}) || !$autocompletion) {
                $data->{'completionminscore' . $suffix} = 0;
            }
        }
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        $suffix = $this->get_suffix();
        $completionminscoregroupel = 'completionminscore' . $suffix;
        if (!empty($data[$completionminscoregroupel])) {
            if ($data[$completionminscoregroupel] >= 0) {
                if ($data[$completionminscoregroupel] > 100) {
                    $completionminscoregroupel = 'completionminscoregroup' . $suffix;
                    $errors[$completionminscoregroupel] = 'The score cannot be greater than 100';
                }
            } else {
                $completionminscoregroupel = 'completionminscoregroup' . $suffix;
                $errors[$completionminscoregroupel] = 'The score can not be less than 0';
            }
        }
		// 'required' for selected cohort:
//        if ($data['showtopresults'] === 'selectedGroup' && empty($data['showtopresultscohort'])) {
//            $errors['showtopresultscohort'] = get_string('required');
//        }

		return $errors;
    }

    /**
     * Display module-specific activity completion rules.
     * Part of the API defined by moodleform_mod
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = $this->get_suffix();

        $group = [];
        $completionminscoreenabledel = 'completionminscoreenabled' . $suffix;
        $group[] = $mform->createElement(
            'checkbox',
            $completionminscoreenabledel,
            '',
            get_string('completionminscore', 'exagames')
        );
        $completionminscoreel = 'completionminscore' . $suffix;
        $group[] = $mform->createElement('text', $completionminscoreel, '', ['size' => 5]);
        $mform->setType($completionminscoreel, PARAM_FLOAT);
        $completionminscoregroupel = 'completionminscoregroup' . $suffix;
        $mform->addGroup($group, $completionminscoregroupel, '', ' ', false);
        $mform->hideIf($completionminscoreel, $completionminscoreenabledel, 'notchecked');

        return [$completionminscoregroupel];
    }

    /**
     * IS NOT USED -  moved into higher level in 'add_completion_rules'
     * Add completion grading elements to the form and return the list of element ids.
     *
     * @return array Array of string IDs of added items, empty array if none
     * @deprecated
     */
//    public function add_completiongrade_rules(): array {
//		return [];
        /*$mform = $this->_form;
        $suffix = $this->get_suffix();

        $completionpassgradeel = 'completionpassgrade' . $suffix;
        $completionexagamegradeel = 'completionexagamegrade' . $suffix;
        $group = [];
        $group[] = $mform->createElement('html', '<span style="margin: 0px 5px;">'.get_string('completion_mingrade', 'exagames').'</span>');
        $group[] = $mform->createElement('text', $completionexagamegradeel, '', ['size' => 7]);
        $group[] = $mform->createElement('html', '<span style="margin: 0px 5px;">%</span>');
        $mform->setType($completionexagamegradeel, PARAM_FLOAT);
        $completionexagamegradegroupel = 'completionexagamegradegroup' . $suffix;
        $mform->addGroup($group, $completionexagamegradegroupel, '', ' ', false);
        $mform->hideIf($completionexagamegradegroupel, $completionpassgradeel, 'notchecked');
        $mform->hideIf($completionexagamegradegroupel, $completionpassgradeel, 'notchecked');
        return [$completionexagamegradegroupel];*/

        /*$mform->createElement('text', $completionexagamegradeel, '', ['size' => 7]);
        $mform->addElement(
            'text',
            $completionexagamegradeel,
            '',
            ['size' => '7']
        );
        $mform->hideIf($completionexagamegradeel, $completionpassgradeel, 'notchecked');
        return [$completionexagamegradeel];*/
//    }

    /**
     * Called during validation. Indicates whether a module-specific completion rule is selected.
     *
     * @param array $data Input data (not yet validated)
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        return  !empty($data['completionminscoreenabled' . $suffix]);
    }


}
