<?php
if(!defined("IN_MYBB")) die("Direct initialization not allowed.");

$l['fancyavatar'] = 'Fancy Avatar';
$l['fancyavatar_desc'] = 'Automatically assigns unique avatars to users without custom avatars. Supports DiceBear, UI Avatars, and Avataaars providers.';

// Settings
$l['setting_group_fancyavatar'] = 'Fancy Avatar Settings';
$l['setting_group_fancyavatar_desc'] = 'Configure avatar provider and settings for your forum. Automatically assigns unique avatars to users without custom avatars.';

$l['setting_fancyavatar_enabled'] = 'Enable Fancy Avatar';
$l['setting_fancyavatar_enabled_desc'] = 'Enable or disable the Fancy Avatar plugin. When enabled, users without custom avatars will automatically receive avatars.';

$l['setting_fancyavatar_provider'] = 'Avatar Provider';
$l['setting_fancyavatar_provider_desc'] = 'Select which avatar service to use. DiceBear offers 24+ artistic styles. UI Avatars generates clean initial-based avatars. Avataaars creates customizable cartoon characters.';

$l['setting_fancyavatar_size'] = 'Avatar Size (pixels)';
$l['setting_fancyavatar_size_desc'] = 'The width and height of the generated avatar in pixels. Recommended: 64, 128, 256.';

$l['setting_fancyavatar_seed'] = 'Avatar Seed Method';
$l['setting_fancyavatar_seed_desc'] = 'How avatar uniqueness is determined. Username gives each user a unique look; Email provides consistency across services.';

$l['setting_fancyavatar_radius'] = 'Border Radius (%)';
$l['setting_fancyavatar_radius_desc'] = 'Roundedness of avatar corners. 0 = square corners, 50 = fully round (circle).';

$l['setting_fancyavatar_exclude_groups'] = 'Exclude User Groups';
$l['setting_fancyavatar_exclude_groups_desc'] = 'Comma-separated list of usergroup IDs to exclude from automatic avatar assignment.';

$l['setting_fancyavatar_refresh_on_view'] = 'Refresh Avatar on Profile View';
$l['setting_fancyavatar_refresh_on_view_desc'] = 'When enabled, avatar URLs are refreshed to reflect current settings when a profile is viewed.';

$l['setting_fancyavatar_style'] = 'DiceBear Style';
$l['setting_fancyavatar_style_desc'] = 'Visual style for DiceBear avatars. Only applies when provider is set to DiceBear.';

$l['setting_fancyavatar_bgcolor'] = 'DiceBear Background Color';
$l['setting_fancyavatar_bgcolor_desc'] = 'Background color for DiceBear avatars. Only applies to DiceBear provider.';

$l['setting_fancyavatar_shape'] = 'DiceBear Avatar Shape';
$l['setting_fancyavatar_shape_desc'] = 'Shape for supported DiceBear styles. Default uses the style\'s native shape.';

$l['setting_fancyavatar_mouth'] = 'DiceBear Mouth Style';
$l['setting_fancyavatar_mouth_desc'] = 'Mouth expression for DiceBear avataaars style. Default uses random selection.';

$l['setting_fancyavatar_expression'] = 'DiceBear Expression Style';
$l['setting_fancyavatar_expression_desc'] = 'Facial expression for supported DiceBear styles. Default uses random selection.';

$l['setting_fancyavatar_uiavatars_color'] = 'UI Avatars Font Color';
$l['setting_fancyavatar_uiavatars_color_desc'] = 'Font color for UI Avatars initials. Only applies when provider is set to UI Avatars.';

$l['setting_fancyavatar_uiavatars_bold'] = 'UI Avatars Bold Text';
$l['setting_fancyavatar_uiavatars_bold_desc'] = 'Display initials in bold font for UI Avatars. Only applies when provider is set to UI Avatars.';

$l['setting_fancyavatar_uiavatars_length'] = 'UI Avatars Initials Length';
$l['setting_fancyavatar_uiavatars_length_desc'] = 'Number of characters to use from the username for initials. Only applies when provider is set to UI Avatars.';

$l['setting_fancyavatar_avataaars_style'] = 'Avataaars Avatar Style';
$l['setting_fancyavatar_avataaars_style_desc'] = 'Choose between Circle (with background) or Transparent (no background). Only applies when provider is set to Avataaars.';

// UserCP
$l['fancyavatar_usercp_info'] = 'Your profile has a Fancy Avatar. You can change it in the Avatar section.';
$l['fancyavatar_ucp_title'] = 'Fancy Avatar';
$l['fancyavatar_ucp_desc'] = 'Click the avatar or the shuffle button to get a new random avatar. When you find one you like, click "Use This Avatar".';
$l['fancyavatar_ucp_button'] = 'Use This Avatar';
$l['fancyavatar_ucp_shuffle_button'] = 'Shuffle';
$l['fancyavatar_ucp_shuffle_title'] = 'Click to get a new avatar';
$l['fancyavatar_ucp_shuffle_hint'] = 'Click to shuffle';
$l['fancyavatar_ucp_confirm'] = 'Are you sure you want to use this avatar?';

// Errors
$l['fancyavatar_error_invalid_request'] = 'Invalid request. Please try again.';
$l['fancyavatar_error_no_permission'] = 'You do not have permission to perform this action.';
$l['fancyavatar_error_plugin_disabled'] = 'Fancy Avatar plugin is currently disabled.';

// Success messages
$l['fancyavatar_success_avatar_assigned'] = 'A new avatar has been assigned to your profile.';
$l['fancyavatar_success_batch_updated'] = 'Successfully updated %d users with Fancy Avatars.';

// Moderator options
$l['fancyavatar_change_avatar'] = 'Change to Fancy Avatar';

// Admin
$l['fancyavatar_admin_batch_update'] = 'Batch Update Avatars';
$l['fancyavatar_admin_batch_update_desc'] = 'Assign Fancy Avatars to all users with default avatars.';
$l['fancyavatar_admin_batch_confirm'] = 'This will assign Fancy Avatars to all users who currently have the default avatar. Are you sure you want to continue?';
$l['fancyavatar_admin_batch_complete'] = 'Batch update complete. %d users updated.';
