<?php
/**
 * Fancy Avatar - Install Routines
 *
 * Contains installation, activation, deactivation, and uninstall routines.
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
 * Main installation routine
 */
function fancyavatar_install_run()
{
    global $db, $PL;

    $PL or require_once PLUGINLIBRARY;
}

/**
 * Activation routine
 */
function fancyavatar_activate_run()
{
    global $db, $PL, $mybb;

    $PL or require_once PLUGINLIBRARY;

    fancyavatar_activate_settings();
    fancyavatar_activate_templates();
    fancyavatar_activate_stylesheet();

    $db->query("ALTER TABLE " . TABLE_PREFIX . "users MODIFY avatar VARCHAR(500) NOT NULL DEFAULT ''");

    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
    find_replace_templatesets(
        'member_profile_modoptions_manageuser',
        '#\{\$purgespammer\}#',
        '{$purgespammer}{$fancyavatar_option}'
    );
    find_replace_templatesets(
        'usercp_avatar',
        '#\{\$avatar_remote\}#',
        '{$avatar_remote}{$fancyavatar_ucp_option}'
    );

    if (function_exists('rebuild_settings') === false) {
        require_once MYBB_ROOT . 'inc/functions.php';
    }
    rebuild_settings();

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/avatar_functions.php';
    $hash = fancyavatar_get_settings_hash_value();

    $existing = $db->simple_select('settings', 'sid', "name = 'fancyavatar_settings_hash'");
    if (!$db->fetch_field($existing, 'sid')) {
        $db->insert_query('settings', array(
            'name'        => 'fancyavatar_settings_hash',
            'title'       => '',
            'description' => '',
            'optionscode' => '',
            'value'       => $db->escape_string($hash),
            'gid'         => 0,
            'disporder'   => 99,
        ));
    } else {
        $db->update_query('settings', array(
            'value' => $db->escape_string($hash),
        ), "name = 'fancyavatar_settings_hash'");
    }

    $db->delete_query('datacache', "title = 'templates'");
}

/**
 * Deactivation routine
 */
function fancyavatar_deactivate_run()
{
    global $db, $PL;

    $PL or require_once PLUGINLIBRARY;

    $PL->templates_delete('fancyavatar');
    $PL->stylesheet_delete('fancyavatar');
    $db->delete_query('templategroups', "prefix = 'fancyavatar'");

    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
    find_replace_templatesets(
        'member_profile_modoptions_manageuser',
        '#\{\$fancyavatar_option\}#',
        ''
    );
    find_replace_templatesets(
        'usercp_avatar',
        '#\{\$fancyavatar_ucp_option\}#',
        ''
    );

    $db->delete_query('datacache', "title = 'templates'");
}

/**
 * Register plugin settings from settings.json via PluginLibrary
 */
function fancyavatar_activate_settings()
{
    global $PL;

    $PL or require_once PLUGINLIBRARY;

    $settings_file = MYBB_ROOT . 'inc/plugins/FancyAvatar/settings.json';
    if (!file_exists($settings_file)) {
        return;
    }

    $json = @file_get_contents($settings_file);
    $data = @json_decode($json, true);
    if (!is_array($data) || !isset($data['group']) || !isset($data['settings'])) {
        return;
    }

    $group = $data['group'];
    $name = !empty($group['name']) ? $group['name'] : 'fancyavatar';
    $title = !empty($group['title']) ? $group['title'] : 'Fancy Avatar Settings';
    $description = !empty($group['description']) ? $group['description'] : '';

    $PL->settings($name, $title, $description, $data['settings']);
}

/**
 * Register plugin templates
 */
function fancyavatar_activate_templates()
{
    global $PL;

    $PL or require_once PLUGINLIBRARY;

    $templates_dir = MYBB_ROOT . 'inc/plugins/FancyAvatar/Templates/';
    $template_files = glob($templates_dir . '*.html');

    $templates = array();
    foreach ($template_files as $file) {
        $filename = basename($file, '.html');
        $content = @file_get_contents($file);
        if ($content !== false) {
            $key = str_replace('fancyavatar_', '', $filename);
            $templates[$key] = $content;
        }
    }

    if (!empty($templates)) {
        $PL->templates('fancyavatar', 'Fancy Avatar', $templates);
    }
}

/**
 * Register plugin stylesheet from Assets/fancyavatar.css
 */
function fancyavatar_activate_stylesheet()
{
    global $PL;

    $PL or require_once PLUGINLIBRARY;

    $css_path = MYBB_ROOT . 'inc/plugins/FancyAvatar/Assets/fancyavatar.css';
    if (file_exists($css_path)) {
        $css = @file_get_contents($css_path);
        if ($css !== false) {
            $PL->stylesheet('fancyavatar', $css);
        }
    }
}
