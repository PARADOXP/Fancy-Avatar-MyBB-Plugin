<?php
/**
 * Fancy Avatar Plugin for MyBB
 * 
 * Automatically assigns unique avatars to users who don't have a custom avatar set.
 * Supports DiceBear (24+ styles), UI Avatars (initials), and Avataaars (characters).
 * 
 * @package FancyAvatar
 * @version 1.1.0
 * @author PARAD0X
 * @license MIT
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

if (!defined('PLUGINLIBRARY')) {
    define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');
}

// Require helper functions
require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/avatar_functions.php';
require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/hook_functions.php';

/**
 * Plugin information
 */
function fancyavatar_info()
{
    return array(
        'name'          => 'Fancy Avatar',
        'description'   => 'Automatically assigns unique avatars to users without custom avatars. Supports DiceBear (24+ styles), UI Avatars (initials-based), and Avataaars (customizable characters).',
        'website'       => 'https://mybb.epizy.com',
        'author'        => 'PARAD0X',
        'authorsite'    => 'https://community.mybb.com/user-134311.html',
        'version'       => '1.1.0',
        'compatibility' => '18*',
        'codename'      => 'fancyavatar',
        'guid'          => '',
    );
}

/**
 * Check if plugin is installed
 */
function fancyavatar_is_installed()
{
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name = 'fancyavatar'");
    return $db->fetch_field($query, 'gid') ? true : false;
}

/**
 * Install the plugin
 */
function fancyavatar_install()
{
    global $PL, $db;

    $PL or require_once PLUGINLIBRARY;

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/install.php';
    fancyavatar_install_run();
}

/**
 * Uninstall the plugin
 */
function fancyavatar_uninstall()
{
    global $PL, $db;

    $PL or require_once PLUGINLIBRARY;

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/uninstall.php';
    fancyavatar_uninstall_run();
}

/**
 * Activate the plugin
 */
function fancyavatar_activate()
{
    global $PL, $db, $mybb;

    $PL or require_once PLUGINLIBRARY;

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/install.php';
    fancyavatar_activate_run();
}

/**
 * Deactivate the plugin
 */
function fancyavatar_deactivate()
{
    global $PL, $db;

    $PL or require_once PLUGINLIBRARY;

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/install.php';
    fancyavatar_deactivate_run();
}

// Hook registration
if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_settings_manage', 'fancyavatar_admin_lang');
    $plugins->add_hook('admin_config_settings_change', 'fancyavatar_admin_lang');
    $plugins->add_hook('admin_config_settings_start', 'fancyavatar_admin_lang');
    $plugins->add_hook('admin_user_menu', 'fancyavatar_admin_menu');
    $plugins->add_hook('admin_user_action_handler', 'fancyavatar_admin_action_handler');
    $plugins->add_hook('admin_load', 'fancyavatar_admin_load');

    // User insert hooks (also needed in ACP for admin-created users)
    $plugins->add_hook('datahandler_user_insert', 'fancyavatar_new_user_insert', 5);
    $plugins->add_hook('datahandler_user_insert_end', 'fancyavatar_new_user_insert_end');
} else {
    // Global hooks
    $plugins->add_hook('global_start', 'fancyavatar_global_start');

    // Auto-refresh avatars when settings change
    $plugins->add_hook('pre_output_page', 'fancyavatar_pre_output_page');

    // New user registration hooks
    $plugins->add_hook('datahandler_user_insert', 'fancyavatar_new_user_insert', 5);
    $plugins->add_hook('datahandler_user_insert_end', 'fancyavatar_new_user_insert_end');

    // Avatar deletion hooks
    $plugins->add_hook('usercp_do_avatar_end', 'fancyavatar_avatar_deleted');

    // Profile display hooks - set variable BEFORE manageuser template is evaluated
    $plugins->add_hook('member_profile_start', 'fancyavatar_profile_mod_option');

    // UserCP profile hooks
    $plugins->add_hook('usercp_profile_end', 'fancyavatar_usercp_profile');
    $plugins->add_hook('usercp_avatar_end', 'fancyavatar_usercp_avatar');

    // Postbit hooks
    $plugins->add_hook('postbit', 'fancyavatar_postbit');
    $plugins->add_hook('postbit_author_user', 'fancyavatar_postbit_author_user');

    // Login hooks
    $plugins->add_hook('member_login', 'fancyavatar_member_login');

    // Misc handler for batch operations
    $plugins->add_hook('misc_start', 'fancyavatar_misc_handler');
}

/**
 * Load language file on relevant front-end pages
 */
function fancyavatar_global_start()
{
    global $lang, $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $lang->load('fancyavatar');
}

/**
 * Admin settings page language loader
 */
function fancyavatar_admin_lang()
{
    global $lang;
    $lang->load('fancyavatar');
}

/**
 * Hook: admin_user_menu
 * Add Fancy Avatar to the User module sidebar
 */
function fancyavatar_admin_menu(&$sub_menu)
{
    $sub_menu['90'] = array(
        "id" => "fancyavatar",
        "title" => "Fancy Avatar",
        "link" => "index.php?module=user-fancyavatar"
    );

    return $sub_menu;
}

/**
 * Hook: admin_user_action_handler
 * Register the Fancy Avatar action
 */
function fancyavatar_admin_action_handler(&$actions)
{
    $actions['fancyavatar'] = array(
        'active' => 'fancyavatar',
        'file' => 'fancyavatar.php'
    );

    return $actions;
}

/**
 * Hook: admin_load
 * Intercept admin page load and route to plugin's admin page
 */
function fancyavatar_admin_load()
{
    global $mybb, $page, $lang, $db;

    if($page->active_action == "fancyavatar")
    {
        $lang->load('fancyavatar');

        $admin_file = MYBB_ROOT . 'inc/plugins/FancyAvatar/Admin/fancyavatar.php';

        if(file_exists($admin_file))
        {
            require $admin_file;
            exit;
        }
    }
}

/**
 * Hook: pre_output_page
 * Detect settings changes by comparing a hash and auto-refresh all DiceBear avatars
 */
function fancyavatar_pre_output_page(&$page)
{
    global $mybb, $db;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/avatar_functions.php';

    $current_hash = fancyavatar_get_settings_hash_value();

    $query = $db->simple_select('settings', 'value', "name = 'fancyavatar_settings_hash'");
    $stored_hash = $db->fetch_field($query, 'value');

    if ($stored_hash !== $current_hash) {
        fancyavatar_refresh_all();
    }
}

// No local settings hash function - uses fancyavatar_get_settings_hash_value() from avatar_functions.php
