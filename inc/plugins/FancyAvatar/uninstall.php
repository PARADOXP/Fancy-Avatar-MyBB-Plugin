<?php
/**
 * Fancy Avatar - Uninstall Routines
 * 
 * Contains all cleanup routines for plugin uninstallation.
 * 
 * @package FancyAvatar
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

if (!defined('PLUGINLIBRARY')) {
    define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');
}

/**
 * Main uninstall routine
 */
function fancyavatar_uninstall_run()
{
    global $db, $PL;
    
    $PL or require_once PLUGINLIBRARY;
    
    // Delete settings group
    $PL->settings_delete('fancyavatar');
    
    // Delete templates
    $PL->templates_delete('fancyavatar');
    
    // Delete template group
    $db->delete_query('templategroups', "prefix = 'fancyavatar'");
    
    // Delete stylesheet
    $PL->stylesheet_delete('fancyavatar');
    
    // Remove any template edits made by the plugin
    fancyavatar_remove_template_edits();
    
    // Rebuild settings to sync
    if (function_exists('rebuild_settings') === false) {
        require_once MYBB_ROOT . 'inc/functions.php';
    }
    rebuild_settings();
    
    // Clear template cache
    $db->delete_query('datacache', "title = 'templates'");
}

/**
 * Remove template edits made during activation
 */
function fancyavatar_remove_template_edits()
{
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    // Reverse the {$fancyavatar_option} addition to manageuser template
    find_replace_templatesets(
        'member_profile_modoptions_manageuser',
        '#\{\$fancyavatar_option\}#',
        ''
    );
}
