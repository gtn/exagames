<?php  //$Id: upgrade.php,v 1.2 2007/08/08 22:36:54 stronk7 Exp $

// This file keeps track of upgrades to
// the newmodule module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_exagames_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $result = true;

    if ($result && $oldversion < 2009042100) {
  		// changes to exabisgames table
		$table = new XMLDBTable('exabisgames');
		$field = new XMLDBField('swf');
        $field->setAttributes(XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL);
        $result = $result && add_field($table, $field);      
	} elseif ($result && $oldversion < 2010052102) {
  		// changes to exabisgames table
		$table = new XMLDBTable('exabisgames');
		$field = new XMLDBField('swf');
		// rename needs field
        $field->setAttributes(XMLDB_TYPE_CHAR, '12', null, XMLDB_NOTNULL);
        $result = $result && rename_field($table, $field, 'gametype');      
	}

    if ($oldversion < 2024102900) {
        // Update records from 'exagames_question' which are related into draft files
        // Will be created real files and re-related to the new ones
        // If draft file is not existing already - nothing to do
        $questionconfigs = $DB->get_records_sql("
				SELECT q.* 
				FROM {exagames_question} q
				WHERE content_url LIKE '%/user/draft/%'
				");
        $fs = get_file_storage();
        foreach ($questionconfigs as $confObj) {
            $orignialUrl = $confObj->content_url;
            $questionId = $confObj->id;
            $parsed_url = parse_url($orignialUrl);
            $path = $parsed_url['path'];
            $dynamic_part = substr($path, strpos($path, "exagames/lib/file_load.php/") + strlen("exagames/lib/file_load.php/"));
            $parts = explode('/', $dynamic_part);
            $contextid = $parts[0];
            $component = $parts[1];
            $filearea = $parts[2];
            $draftid = $parts[3];
            $filename = urldecode($parts[4]);
            $fullpath = "/$contextid/$component/$filearea/$draftid/$filename";
            $filehash = sha1($fullpath);

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
                // Remove files: we need only single file for every question (must not be yet, but let leave this code)
                $fs->delete_area_files($contextid, $newComponent, $newFilearea, $questionId);
                // And new file create
                $new_file = $fs->create_file_from_string($file_record, $draftContent);
                // get new url
                $content_partUrl = implode('/', [$contextid, $newComponent, $newFilearea, $questionId, $filename]);
                $mainLength = strpos($orignialUrl, "exagames/lib/file_load.php/") + strlen("exagames/lib/file_load.php/");
                $mainUrl = substr($orignialUrl, 0, $mainLength);
                $mainUrl .= $content_partUrl;
                $confObj->content_url = $mainUrl;
                // save to DB
                $questionconfigs = $DB->update_record('exagames_question', $confObj);
            }
        }
        upgrade_mod_savepoint(true, 2024102900, 'exagames');

    }

    return $result;
}

?>
