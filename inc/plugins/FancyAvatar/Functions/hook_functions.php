<?php
/**
 * Fancy Avatar - Hook Functions
 *
 * Contains all hook handler functions for the plugin.
 *
 * @package FancyAvatar
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

/**
 * Hook: datahandler_user_insert
 * Assign avatar to new users during registration
 *
 * @param UserDataHandler $userhandler The user data handler object
 */
function fancyavatar_new_user_insert(&$userhandler)
{
    global $mybb, $db;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $user_data = $userhandler->data;

    if (!empty($user_data['avatar'])) {
        return;
    }

    $exclude_groups = $mybb->settings['fancyavatar_exclude_groups'];
    $usergroup = intval($user_data['usergroup']);

    if (!empty($exclude_groups)) {
        $exclude_array = array_map('intval', explode(',', $exclude_groups));
        if (in_array($usergroup, $exclude_array)) {
            return;
        }
    }

    $username = $user_data['username'];
    $email = isset($user_data['email']) ? $user_data['email'] : '';
    $uid = isset($userhandler->inserts['uid']) ? $userhandler->inserts['uid'] : 0;

    $avatar_url = fancyavatar_generate_url($username, $email, $uid);
    $size = intval($mybb->settings['fancyavatar_size']);

    $userhandler->user_insert_data['avatar'] = $avatar_url;
    $userhandler->user_insert_data['avatartype'] = 'remote';
    $userhandler->user_insert_data['avatardimensions'] = $size . '|' . $size;
}

/**
 * Hook: datahandler_user_insert_end
 * Fallback for assigning avatar after user is inserted
 *
 * @param UserDataHandler $userhandler The user data handler object
 */
function fancyavatar_new_user_insert_end(&$userhandler)
{
    global $mybb, $db;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $uid = $userhandler->uid;

    if (!$uid) {
        return;
    }

    $query = $db->simple_select('users', 'avatar, avatartype', 'uid = ' . intval($uid));
    $user = $db->fetch_array($query);

    if (!$user) {
        return;
    }

    if (empty($user['avatar']) || $user['avatar'] == $mybb->settings['useravatar']) {
        $username = $userhandler->data['username'];
        $email = isset($userhandler->data['email']) ? $userhandler->data['email'] : '';

        fancyavatar_assign_avatar($uid, $username, $email);
    }
}

/**
 * Hook: usercp_do_avatar_end
 * Reassign avatar when user deletes their avatar
 *
 * @return void
 */
function fancyavatar_avatar_deleted()
{
    global $mybb, $db;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    if ($mybb->input['action'] != 'do_deleteavatar') {
        return;
    }

    $uid = $mybb->user['uid'];

    $exclude_groups = $mybb->settings['fancyavatar_exclude_groups'];
    $usergroup = intval($mybb->user['usergroup']);

    if (!empty($exclude_groups)) {
        $exclude_array = array_map('intval', explode(',', $exclude_groups));
        if (in_array($usergroup, $exclude_array)) {
            return;
        }
    }

    $username = $mybb->user['username'];
    $email = $mybb->user['email'];

    fancyavatar_assign_avatar($uid, $username, $email);
}

/**
 * Hook: member_profile_start
 * Set mod option variable BEFORE manageuser template is evaluated
 *
 * @return void
 */
function fancyavatar_profile_mod_option()
{
    global $mybb, $db, $memprofile, $templates, $lang, $uid, $fancyavatar_option;

    $fancyavatar_option = '';

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    if ($mybb->usergroup['caneditprofiles'] == 1 && modcp_can_manage_user($memprofile['uid'])) {
        $fancyavatar_option = eval($templates->render('fancyavatar_mod_options_change_avatar'));
    }
}

/**
 * Hook: usercp_avatar_end
 * Display Fancy Avatar option in UserCP avatar page
 *
 * @return void
 */
function fancyavatar_usercp_avatar()
{
    global $mybb, $lang, $templates, $fancyavatar_ucp_option;

    $fancyavatar_ucp_option = '';

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $lang->load('fancyavatar');

    require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/avatar_functions.php';

    $fancyavatar_provider = $mybb->settings['fancyavatar_provider'];
    $fancyavatar_preview = fancyavatar_generate_url($mybb->user['username'], $mybb->user['email'], $mybb->user['uid']);
    $fancyavatar_size = intval($mybb->settings['fancyavatar_size']);
    $fancyavatar_radius = intval($mybb->settings['fancyavatar_radius']);
    $fancyavatar_misc_url = $mybb->settings['bburl'] . '/misc.php';

    $fancyavatar_style = $mybb->settings['fancyavatar_style'];
    $fancyavatar_bgcolor = $mybb->settings['fancyavatar_bgcolor'];
    $fancyavatar_uiavatars_color = $mybb->settings['fancyavatar_uiavatars_color'];
    $fancyavatar_uiavatars_bold = $mybb->settings['fancyavatar_uiavatars_bold'];
    $fancyavatar_uiavatars_length = intval($mybb->settings['fancyavatar_uiavatars_length']);
    $fancyavatar_avataaars_style = $mybb->settings['fancyavatar_avataaars_style'];

    $fancyavatar_ucp_option = eval($templates->render('fancyavatar_usercp_avatar_option'));
}

/**
 * Hook: usercp_profile_end
 * Display avatar info in UserCP
 *
 * @return void
 */
function fancyavatar_usercp_profile()
{
    global $mybb, $db, $lang, $templates;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $lang->load('fancyavatar');

    if (fancyavatar_is_fancy_avatar($mybb->user['uid'])) {
        $avatar_url = $mybb->user['avatar'];
        $avatar_size = intval($mybb->settings['fancyavatar_size']);

        $fancyavatar_info = eval($templates->render('fancyavatar_usercp_info'));
    }
}

/**
 * Hook: postbit
 * Add avatar fallback in postbit
 *
 * @param array &$post The post data array
 * @return void
 */
function fancyavatar_postbit(&$post)
{
    global $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    if (empty($post['avatar']) || $post['avatar'] == $mybb->settings['useravatar']) {
        $post['avatar'] = fancyavatar_generate_url(
            $post['username'],
            '',
            $post['uid']
        );
    }
}

/**
 * Hook: postbit_author_user
 * Add avatar fallback in postbit author info
 *
 * @param array &$post The post data array
 * @return void
 */
function fancyavatar_postbit_author_user(&$post)
{
    global $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    if (empty($post['avatar']) || $post['avatar'] == $mybb->settings['useravatar']) {
        $post['avatar'] = fancyavatar_generate_url(
            $post['username'],
            '',
            $post['uid']
        );
    }
}

/**
 * Hook: member_login
 * Refresh avatar on login if needed
 *
 * @return void
 */
function fancyavatar_member_login()
{
    global $mybb, $db;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $uid = isset($mybb->user['uid']) ? $mybb->user['uid'] : 0;

    if (!$uid) {
        return;
    }

    if (fancyavatar_has_default_avatar($uid)) {
        $username = isset($mybb->user['username']) ? $mybb->user['username'] : '';
        $email = isset($mybb->user['email']) ? $mybb->user['email'] : '';

        fancyavatar_assign_avatar($uid, $username, $email);
    }
}

/**
 * Hook: misc_start
 * Handle batch update, change avatar, and set avatar actions
 *
 * @return void
 */
function fancyavatar_misc_handler()
{
    global $mybb, $db, $lang, $page;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return;
    }

    $action = $mybb->get_input('action');

    if ($action == 'fancyavatar_batch') {
        if (!$mybb->user['superadmin']) {
            error_no_permission();
        }

        $lang->load('fancyavatar');

        if ($mybb->request_method != 'post') {
            error($lang->fancyavatar_error_invalid_request);
        }

        verify_post_check($mybb->get_input('my_post_key'));

        $updated = fancyavatar_batch_update();

        $success_msg = sprintf($lang->fancyavatar_success_batch_updated, $updated);
        redirect('index.php?module=config-plugins', $success_msg);
    }

    if ($action == 'fancyavatar_change') {
        $lang->load('fancyavatar');

        verify_post_check($mybb->get_input('my_post_key'));

        if ($mybb->usergroup['caneditprofiles'] != 1) {
            error_no_permission();
        }

        $uid = intval($mybb->input['uid']);
        if ($uid <= 0) {
            error($lang->fancyavatar_error_invalid_request);
        }

        $query = $db->simple_select('users', 'uid, username, email', 'uid = ' . $uid);
        $target_user = $db->fetch_array($query);

        if (!$target_user) {
            error($lang->fancyavatar_error_invalid_request);
        }

        require_once MYBB_ROOT . 'inc/functions_modcp.php';
        if (!modcp_can_manage_user($uid)) {
            error_no_permission();
        }

        fancyavatar_assign_avatar($uid, $target_user['username'], $target_user['email']);

        $success_msg = $lang->fancyavatar_success_avatar_assigned;
        redirect('member.php?action=profile&uid=' . $uid, $success_msg);
    }

    if ($action == 'fancyavatar_set') {
        $lang->load('fancyavatar');

        if ($mybb->request_method != 'post') {
            error($lang->fancyavatar_error_invalid_request);
        }

        verify_post_check($mybb->get_input('my_post_key'));

        $avatar_url = $mybb->get_input('avatar_url');

        if (empty($avatar_url) || !fancyavatar_is_provider_url($avatar_url)) {
            error($lang->fancyavatar_error_invalid_request);
        }

        $size = intval($mybb->settings['fancyavatar_size']);

        $db->update_query('users', array(
            'avatar'           => $db->escape_string($avatar_url),
            'avatartype'       => 'remote',
            'avatardimensions' => $size . '|' . $size,
        ), 'uid = ' . intval($mybb->user['uid']));

        $success_msg = $lang->fancyavatar_success_avatar_assigned;
        redirect('usercp.php?action=avatar', $success_msg);
    }
}
