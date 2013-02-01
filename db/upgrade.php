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

    global $CFG, $THEME, $db;

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

    return $result;
}

?>
