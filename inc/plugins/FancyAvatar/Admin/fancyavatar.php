<?php
/**
 * Fancy Avatar - Admin Module
 *
 * Allows admins to batch assign Fancy Avatars to users.
 *
 * @package FancyAvatar
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

require_once MYBB_ROOT . 'inc/plugins/FancyAvatar/Functions/avatar_functions.php';

$lang->load('fancyavatar');

if($mybb->input['action'] == 'run' || $mybb->input['action'] == 'run_force')
{
    $is_force = ($mybb->input['action'] == 'run_force');
    $page->add_breadcrumb_item($is_force ? $lang->fancyavatar_force_button : $lang->fancyavatar_batch_update);

    if($mybb->request_method == "post")
    {
        verify_post_check($mybb->get_input('my_post_key'));

        if($is_force)
        {
            $updated = fancyavatar_force_update();
            fancyavatar_update_settings_hash();
            log_admin_action("fancyavatar", "force_update", $updated);
            flash_message(sprintf($lang->fancyavatar_force_complete, $updated), 'success');
        }
        else
        {
            $updated = fancyavatar_batch_update();
            fancyavatar_update_settings_hash();
            log_admin_action("fancyavatar", "batch_update", $updated);
            flash_message(sprintf($lang->fancyavatar_batch_complete, $updated), 'success');
        }

        admin_redirect("index.php?module=user-fancyavatar");
    }
}

$page->add_breadcrumb_item($lang->fancyavatar_admin_title, "index.php?module=user-fancyavatar");

$sub_tabs['fancyavatar_manage'] = array(
    'title' => $lang->fancyavatar_admin_title,
    'link' => "index.php?module=user-fancyavatar",
    'description' => $lang->fancyavatar_manage_desc
);

$page->output_header($lang->fancyavatar_admin_title);
$page->output_nav_tabs($sub_tabs, 'fancyavatar_manage');

$default_avatar = $mybb->settings['useravatar'];
$where = "avatar = '' OR avatar IS NULL";
if(!empty($default_avatar))
{
    $where .= " OR avatar = '" . $db->escape_string($default_avatar) . "'";
}

$query = $db->query("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "users WHERE {$where}");
$total_default = $db->fetch_field($query, 'total');

$query = $db->query("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "users");
$total_users = $db->fetch_field($query, 'total');

$query = $db->query("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "users WHERE " . fancyavatar_provider_where());
$total_fancy = $db->fetch_field($query, 'total');

$table = new Table;
$table->construct_header($lang->fancyavatar_stat_label, array("width" => "50%"));
$table->construct_header($lang->fancyavatar_stat_value, array("width" => "50%"));

$table->construct_cell("<strong>" . $lang->fancyavatar_total_users . "</strong>");
$table->construct_cell($total_users);
$table->construct_row();

$table->construct_cell("<strong>" . $lang->fancyavatar_default_avatars . "</strong>");
$table->construct_cell($total_default);
$table->construct_row();

$table->construct_cell("<strong>" . $lang->fancyavatar_fancy_avatars . "</strong>");
$table->construct_cell($total_fancy);
$table->construct_row();

$table->output($lang->fancyavatar_statistics);

echo "<br />";

$form = new Form("index.php?module=user-fancyavatar&amp;action=run", "post");
echo "<div class=\"fancyavatar_batch_box\">";
if($total_default > 0)
{
    echo "<p>" . sprintf($lang->fancyavatar_batch_desc, $total_default) . "</p>";
}
else
{
    echo "<p>" . $lang->fancyavatar_batch_no_users . "</p>";
}
echo "<p>" . $lang->fancyavatar_batch_confirm . "</p>";
$buttons[] = $form->generate_submit_button($lang->fancyavatar_batch_button);
$form->output_submit_wrapper($buttons);
$form->end();
echo "</div>";

echo "<br />";

$form2 = new Form("index.php?module=user-fancyavatar&amp;action=run_force", "post");
echo "<div class=\"fancyavatar_batch_box\">";
echo "<p>" . sprintf($lang->fancyavatar_force_desc, $total_users) . "</p>";
echo "<p>" . $lang->fancyavatar_force_confirm . "</p>";
$buttons2[] = $form2->generate_submit_button($lang->fancyavatar_force_button);
$form2->output_submit_wrapper($buttons2);
$form2->end();
echo "</div>";

$page->output_footer();
exit;
