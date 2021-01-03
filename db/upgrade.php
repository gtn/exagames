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

function xmldb_webgl_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $result = true;
    
    $dbman = $DB->get_manager();

    /*
    if ($oldversion < 2020123100) {
        $table = new xmldb_table('precheck');
        $dbman->drop_table($table);
        $table = new xmldb_table('precheck_data');
        $dbman->drop_table($table);
        
        // Define table webgl to be created.
        $table = new xmldb_table('webgl');
        
        // Adding fields to table webgl.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('intro', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_field('gametype', XMLDB_TYPE_CHAR, '12', null, null, null);
        
        // Adding keys to table webgl.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Conditionally launch create table for webgl.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Define table webgl to be created.
        $table = new xmldb_table('webgl_data');
        
        // Adding fields to table webgl.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
        
        // Adding keys to table webgl.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Conditionally launch create table for webgl.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        upgrade_mod_savepoint(true, 2020123100, 'webgl');
        
    }
    
    */
    return $result;
}

?>
