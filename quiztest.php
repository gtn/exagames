<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$id = required_param('cmid', PARAM_INT); // Course module id

if (!$cm = get_coursemodule_from_id('quiz', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error("coursemisconf");
}
if (!$quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

if (!$quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$quizobj = quiz::create($quiz->id, $USER->id);

require_login($quizobj->get_courseid(), false, $quizobj->get_cm());

quiz_delete_previews($quiz, $USER->id);

$quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
$quba->set_preferred_behaviour($quiz->preferredbehaviour);




// Look for an existing attempt.
$attempts = quiz_get_user_attempts($quiz->id, $USER->id, 'all');
$lastattempt = end($attempts);

// If an in-progress attempt exists, check password then redirect to it.
if ($lastattempt && !$lastattempt->timefinish) {
    $accessmanager->do_password_check($quizobj->is_preview_user());
    redirect($quizobj->attempt_url($lastattempt->id, $page));
}

// Get number for the next or unfinished attempt
if ($lastattempt && !$lastattempt->preview && !$quizobj->is_preview_user()) {
    $attemptnumber = $lastattempt->attempt + 1;
} else {
    $lastattempt = false;
    $attemptnumber = 1;
}

$attempt = quiz_create_attempt($quiz, $attemptnumber, $lastattempt, time(), false);

// Save the attempt in the database.
$transaction = $DB->start_delegated_transaction();
question_engine::save_questions_usage_by_activity($quba);
$attempt->uniqueid = $quba->get_id();
$attempt->id = $DB->insert_record('quiz_attempts', $attempt);

// Update the quiz attempt record.
$attempt->finish_attempt($timenow);

echo 'ok';
exit;

$attempt->uniqueid = $quba->get_id();
$attempt->id = $DB->insert_record('quiz_attempts', $attempt);
