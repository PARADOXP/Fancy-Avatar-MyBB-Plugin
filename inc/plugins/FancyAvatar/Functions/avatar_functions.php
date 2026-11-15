<?php
/**
 * Fancy Avatar - Core Functions
 *
 * Contains URL generation and avatar helper functions for all providers.
 *
 * @package FancyAvatar
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

/**
 * Generate an avatar URL using the configured provider
 *
 * @param string $username The username to seed the avatar
 * @param string $email Optional email for seed fallback
 * @param int $uid Optional user ID for additional seed variation
 * @return string The avatar URL
 */
function fancyavatar_generate_url($username, $email = '', $uid = 0)
{
    global $mybb;

    $provider = $mybb->settings['fancyavatar_provider'];

    switch ($provider) {
        case 'uiavatars':
            return fancyavatar_generate_uiavatars_url($username, $email, $uid);
        case 'avataaars':
            return fancyavatar_generate_avataaars_url($username, $email, $uid);
        case 'dicebear':
        default:
            return fancyavatar_generate_dicebear_url($username, $email, $uid);
    }
}

/**
 * Generate a DiceBear avatar URL
 *
 * @param string $username The username to seed the avatar
 * @param string $email Optional email for seed fallback
 * @param int $uid Optional user ID
 * @return string The DiceBear avatar URL
 */
function fancyavatar_generate_dicebear_url($username, $email = '', $uid = 0)
{
    global $mybb;

    $style = $mybb->settings['fancyavatar_style'];
    $size = intval($mybb->settings['fancyavatar_size']);
    $seed_method = $mybb->settings['fancyavatar_seed'];
    $shape = $mybb->settings['fancyavatar_shape'];
    $bgcolor = $mybb->settings['fancyavatar_bgcolor'];
    $mouth = $mybb->settings['fancyavatar_mouth'];
    $expression = $mybb->settings['fancyavatar_expression'];

    $seed = fancyavatar_get_seed($username, $email, $uid, $seed_method);

    $url = 'https://api.dicebear.com/7.x/' . urlencode($style) . '/svg';
    $url .= '?seed=' . urlencode($seed);

    if ($size > 0) {
        $url .= '&size=' . $size;
    }

    if (!empty($shape) && $shape !== 'default') {
        $valid_shapes = array('adventurer', 'adventurer-neutral', 'big-ears', 'big-ears-neutral',
                               'big-smile', 'fun-emoji', 'lorelei', 'lorelei-neutral',
                               'micah', 'micah-neutral', 'personas', 'shapes');
        if (in_array($style, $valid_shapes)) {
            $url .= '&shape=' . urlencode($shape);
        }
    }

    if (!empty($bgcolor) && $bgcolor !== 'transparent') {
        $url .= '&backgroundColor=' . urlencode(ltrim($bgcolor, '#'));
    }

    if (!empty($mouth) && $mouth !== 'default') {
        $valid_mouth_styles = array('avataaars');
        $valid_mouth_values = array('smile', 'sad', 'serious', 'tongue');
        if (in_array($style, $valid_mouth_styles) && in_array($mouth, $valid_mouth_values)) {
            $url .= '&mouth=' . urlencode($mouth);
        }
    }

    if (!empty($expression) && $expression !== 'default') {
        $valid_expression_styles = array('adventurer', 'adventurer-neutral', 'big-ears', 'big-ears-neutral',
                                          'big-smile', 'fun-emoji', 'lorelei', 'lorelei-neutral',
                                          'micah', 'micah-neutral', 'personas');
        $valid_expression_values = array('happy', 'sad', 'angry', 'surprised', 'shocked', 'blink', 'wink', 'laughing');
        if (in_array($style, $valid_expression_styles) && in_array($expression, $valid_expression_values)) {
            $url .= '&expression=' . urlencode($expression);
        }
    }

    return $url;
}

/**
 * Generate a UI Avatars URL
 *
 * @param string $username The username
 * @param string $email Optional email
 * @param int $uid Optional user ID
 * @return string The UI Avatars URL
 */
function fancyavatar_generate_uiavatars_url($username, $email = '', $uid = 0)
{
    global $mybb;

    $seed_method = $mybb->settings['fancyavatar_seed'];
    $size = intval($mybb->settings['fancyavatar_size']);
    $bgcolor = $mybb->settings['fancyavatar_bgcolor'];
    $uiavatars_color = $mybb->settings['fancyavatar_uiavatars_color'];
    $uiavatars_bold = $mybb->settings['fancyavatar_uiavatars_bold'];
    $uiavatars_length = intval($mybb->settings['fancyavatar_uiavatars_length']);

    $seed = fancyavatar_get_seed($username, $email, $uid, $seed_method);

    $url = 'https://ui-avatars.com/api/?';
    $url .= 'name=' . urlencode($seed);

    if ($size > 0) {
        $url .= '&size=' . $size;
    }

    if (!empty($bgcolor) && $bgcolor !== 'transparent') {
        $url .= '&background=' . urlencode(ltrim($bgcolor, '#'));
    } else {
        $url .= '&background=random';
    }

    if (!empty($uiavatars_color) && $uiavatars_color !== 'default') {
        $url .= '&color=' . urlencode(ltrim($uiavatars_color, '#'));
    }

    if ($uiavatars_bold == 'yes') {
        $url .= '&bold=true';
    }

    if ($uiavatars_length > 0 && $uiavatars_length != 2) {
        $url .= '&length=' . $uiavatars_length;
    }

    $url .= '&rounded=true&format=svg';

    return $url;
}

/**
 * Generate an Avataaars URL
 * Uses deterministic hash-based parameter selection from the seed
 *
 * @param string $username The username
 * @param string $email Optional email
 * @param int $uid Optional user ID
 * @return string The Avataaars URL
 */
function fancyavatar_generate_avataaars_url($username, $email = '', $uid = 0)
{
    global $mybb;

    $seed_method = $mybb->settings['fancyavatar_seed'];
    $avataaars_style = $mybb->settings['fancyavatar_avataaars_style'];

    $seed = fancyavatar_get_seed($username, $email, $uid, $seed_method);

    $hash = fancyavatar_hash_seed($seed);

    $top_types = array(
        'NoHair', 'Eyepatch', 'Hat', 'Hijab', 'Turban', 'Turban2', 'WinterHat1',
        'WinterHat2', 'WinterHat3', 'WinterHat4', 'LongHairBigHair', 'LongHairBob',
        'LongHairBun', 'LongHairCurly', 'LongHairCurvy', 'LongHairDreads',
        'LongHairFrida', 'LongHairFro', 'LongHairFroBand', 'LongHairMiaWallace',
        'LongHairNotTooLong', 'LongHairShaggyMiley', 'LongHairStraight',
        'LongHairStraight2', 'LongHairStraightStrand', 'ShortHairDreads01',
        'ShortHairFrizzle', 'ShortHairShaggyMullet', 'ShortHairShortCurly',
        'ShortHairShortFlat', 'ShortHairShortWaved', 'ShortHairTheCaesar',
        'ShortHairTheCaesarSidePart',
    );

    $hair_colors = array(
        'Auburn', 'Black', 'Blonde', 'BlondeGolden', 'Brown', 'BrownDark',
        'PastelPink', 'Platinum', 'Red', 'SilverGray',
    );

    $accessories_types = array(
        'Blank', 'Kurt', 'Prescription01', 'Prescription02', 'Round',
        'Sunglasses', 'Wayfarers',
    );

    $clothe_types = array(
        'BlazerShirt', 'BlazerSweater', 'CollarSweater', 'GraphicShirt',
        'Hoodie', 'Overall', 'ShirtCrewNeck', 'ShirtScoopNeck', 'ShirtVNeck',
    );

    $clothe_colors = array(
        'Black', 'Blue01', 'Blue02', 'Blue03', 'Gray01', 'Gray02', 'Heather',
        'PastelBlue', 'PastelGreen', 'PastelOrange', 'PastelRed', 'PastelYellow',
        'Pink', 'Red', 'White',
    );

    $graphic_types = array(
        'Bat', 'Cumbia', 'Deer', 'Diamond', 'Hola', 'Pizza', 'Resist',
        'Selena', 'Bear', 'SkullOutline', 'Skull',
    );

    $eye_types = array(
        'Close', 'Cry', 'Default', 'Dizzy', 'EyeRoll', 'Happy', 'Hearts',
        'Side', 'Squint', 'Surprised', 'Wink', 'WinkWacky',
    );

    $eyebrow_types = array(
        'Angry', 'AngryNatural', 'Default', 'DefaultNatural', 'FlatNatural',
        'RaisedExcited', 'RaisedExcitedNatural', 'SadConcerned',
        'SadConcernedNatural', 'UnibrowNatural', 'UpDown', 'UpDownNatural',
    );

    $mouth_types = array(
        'Concerned', 'Default', 'Disbelief', 'Eating', 'Grimace', 'Sad',
        'ScreamOpen', 'Serious', 'Smile', 'Tongue', 'Twinkle', 'Vomit',
    );

    $skin_colors = array(
        'Tanned', 'Yellow', 'Pale', 'Light', 'Brown', 'DarkBrown', 'Black',
    );

    $top_type = $top_types[$hash % count($top_types)];
    $hair_color = $hair_colors[($hash >> 4) % count($hair_colors)];
    $accessories = $accessories_types[($hash >> 8) % count($accessories_types)];
    $clothe_type = $clothe_types[($hash >> 12) % count($clothe_types)];
    $clothe_color = $clothe_colors[($hash >> 16) % count($clothe_colors)];
    $graphic_type = $graphic_types[($hash >> 20) % count($graphic_types)];
    $eye_type = $eye_types[($hash >> 24) % count($eye_types)];
    $eyebrow_type = $eyebrow_types[($hash >> 28) % count($eyebrow_types)];
    $mouth_type = $mouth_types[($hash >> 1) % count($mouth_types)];
    $skin_color = $skin_colors[($hash >> 5) % count($skin_colors)];

    $avatar_style = ($avataaars_style == 'transparent') ? 'Transparent' : 'Circle';

    $url = 'https://avataaars.io/?';
    $url .= 'avatarStyle=' . urlencode($avatar_style);
    $url .= '&topType=' . urlencode($top_type);
    $url .= '&hairColor=' . urlencode($hair_color);
    $url .= '&accessoriesType=' . urlencode($accessories);
    $url .= '&clotheType=' . urlencode($clothe_type);
    $url .= '&clotheColor=' . urlencode($clothe_color);
    $url .= '&graphicType=' . urlencode($graphic_type);
    $url .= '&eyeType=' . urlencode($eye_type);
    $url .= '&eyebrowType=' . urlencode($eyebrow_type);
    $url .= '&mouthType=' . urlencode($mouth_type);
    $url .= '&skinColor=' . urlencode($skin_color);

    return $url;
}

/**
 * Get a deterministic seed value based on configured method
 *
 * @param string $username The username
 * @param string $email Optional email
 * @param int $uid Optional user ID
 * @param string $seed_method The seed method (username, email, uid, random)
 * @return string The seed value
 */
function fancyavatar_get_seed($username, $email = '', $uid = 0, $seed_method = 'username')
{
    switch ($seed_method) {
        case 'email':
            return !empty($email) ? $email : $username;
        case 'uid':
            return $uid ? (string)$uid : $username;
        case 'random':
            return md5(uniqid($username . $uid, true));
        case 'username':
        default:
            return $username;
    }
}

/**
 * Generate a deterministic numeric hash from a string seed
 *
 * @param string $seed The seed string
 * @return int A positive integer hash
 */
function fancyavatar_hash_seed($seed)
{
    $hash = 5381;
    $len = strlen($seed);
    for ($i = 0; $i < $len; $i++) {
        $hash = (($hash << 5) + $hash) + ord($seed[$i]);
        $hash &= 0x7FFFFFFF;
    }
    return $hash;
}

/**
 * Check if a user has the default/empty avatar
 *
 * @param int $uid The user ID
 * @return bool True if user has default avatar
 */
function fancyavatar_has_default_avatar($uid)
{
    global $db, $mybb;

    $uid = intval($uid);
    if ($uid <= 0) {
        return true;
    }

    $query = $db->simple_select('users', 'avatar, avatartype', 'uid = ' . $uid);
    $user = $db->fetch_array($query);

    if (!$user) {
        return true;
    }

    if (empty($user['avatar'])) {
        return true;
    }

    if ($user['avatar'] == $mybb->settings['useravatar']) {
        return true;
    }

    return false;
}

/**
 * Check if a user has a Fancy Avatar (any provider)
 *
 * @param int $uid The user ID
 * @return bool True if user has a Fancy Avatar
 */
function fancyavatar_is_fancy_avatar($uid)
{
    global $db;

    $uid = intval($uid);
    if ($uid <= 0) {
        return false;
    }

    $query = $db->simple_select('users', 'avatar, avatartype', 'uid = ' . $uid);
    $user = $db->fetch_array($query);

    if (!$user) {
        return false;
    }

    if ($user['avatartype'] == 'remote' && !empty($user['avatar'])) {
        return (
            strpos($user['avatar'], 'dicebear.com') !== false ||
            strpos($user['avatar'], 'ui-avatars.com') !== false ||
            strpos($user['avatar'], 'avataaars.io') !== false
        );
    }

    return false;
}

/**
 * Assign a Fancy Avatar to a user
 *
 * @param int $uid The user ID
 * @param string $username The username
 * @param string $email Optional email
 * @return bool True on success
 */
function fancyavatar_assign_avatar($uid, $username, $email = '')
{
    global $db, $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return false;
    }

    $uid = intval($uid);
    if ($uid <= 0) {
        return false;
    }

    $avatar_url = fancyavatar_generate_url($username, $email, $uid);
    $size = intval($mybb->settings['fancyavatar_size']);

    $update_data = array(
        'avatar'           => $db->escape_string($avatar_url),
        'avatartype'       => 'remote',
        'avatardimensions' => $size . '|' . $size,
    );

    $db->update_query('users', $update_data, 'uid = ' . $uid);

    return true;
}

/**
 * Get a user's avatar URL (Fancy Avatar if no custom avatar)
 *
 * @param int $uid The user ID
 * @param bool $force Force Fancy Avatar even if user has custom
 * @return string The avatar URL
 */
function fancyavatar_get_avatar_url($uid, $force = false)
{
    global $db, $mybb;

    $uid = intval($uid);
    if ($uid <= 0) {
        return fancyavatar_generate_url('default');
    }

    $query = $db->simple_select('users', 'avatar, avatartype, username, email', 'uid = ' . $uid);
    $user = $db->fetch_array($query);

    if (!$user) {
        return fancyavatar_generate_url('default');
    }

    if ($force || fancyavatar_has_default_avatar($uid)) {
        return fancyavatar_generate_url($user['username'], $user['email'], $uid);
    }

    return $user['avatar'];
}

/**
 * Format avatar HTML for display
 *
 * @param int $uid The user ID
 * @param int $size Optional size override
 * @param string $class Optional CSS class
 * @param bool $force Force Fancy Avatar
 * @return string HTML img tag
 */
function fancyavatar_get_avatar_html($uid, $size = 0, $class = '', $force = false)
{
    global $mybb;

    if ($size <= 0) {
        $size = intval($mybb->settings['fancyavatar_size']);
    }

    $avatar_url = fancyavatar_get_avatar_url($uid, $force);
    $class = !empty($class) ? ' ' . $class : '';
    $radius = intval($mybb->settings['fancyavatar_radius']);

    $style = '';
    if ($radius >= 0 && $radius <= 50) {
        $style = ' style="border-radius: ' . $radius . '%;"';
    }

    return '<img src="' . htmlspecialchars($avatar_url) . '" alt="Avatar" class="fancyavatar' . $class . '" width="' . $size . '" height="' . $size . '"' . $style . ' />';
}

/**
 * Get the SQL WHERE condition matching all Fancy Avatar provider domains
 *
 * @return string SQL condition
 */
function fancyavatar_provider_where()
{
    return "avatar LIKE '%dicebear.com%' OR avatar LIKE '%ui-avatars.com%' OR avatar LIKE '%avataaars.io%'";
}

/**
 * Check if a URL belongs to any supported provider
 *
 * @param string $url The avatar URL
 * @return bool True if it's a Fancy Avatar URL
 */
function fancyavatar_is_provider_url($url)
{
    return (
        strpos($url, 'dicebear.com') !== false ||
        strpos($url, 'ui-avatars.com') !== false ||
        strpos($url, 'avataaars.io') !== false
    );
}

/**
 * Refresh all existing Fancy Avatars to reflect new settings
 *
 * @return int Number of users updated
 */
function fancyavatar_refresh_all()
{
    global $db, $mybb;

    $size = intval($mybb->settings['fancyavatar_size']);
    $updated = 0;

    $query = $db->query("
        SELECT uid, username, email
        FROM " . TABLE_PREFIX . "users
        WHERE " . fancyavatar_provider_where() . "
        ORDER BY uid ASC
    ");

    while ($user = $db->fetch_array($query)) {
        $avatar_url = fancyavatar_generate_url($user['username'], $user['email'], $user['uid']);

        $db->update_query('users', array(
            'avatar'           => $db->escape_string($avatar_url),
            'avatartype'       => 'remote',
            'avatardimensions' => $size . '|' . $size,
        ), 'uid = ' . intval($user['uid']));

        $updated++;
    }

    if ($updated > 0) {
        fancyavatar_update_settings_hash();
    }

    return $updated;
}

/**
 * Update the stored settings hash to match current settings
 */
function fancyavatar_update_settings_hash()
{
    global $mybb, $db;

    $hash = fancyavatar_get_settings_hash_value();

    $db->update_query('settings', array(
        'value' => $db->escape_string($hash),
    ), "name = 'fancyavatar_settings_hash'");
}

/**
 * Generate current settings hash value
 *
 * @return string MD5 hash
 */
function fancyavatar_get_settings_hash_value()
{
    global $mybb;

    return md5(serialize(array(
        $mybb->settings['fancyavatar_provider'],
        $mybb->settings['fancyavatar_style'],
        $mybb->settings['fancyavatar_size'],
        $mybb->settings['fancyavatar_seed'],
        $mybb->settings['fancyavatar_bgcolor'],
        $mybb->settings['fancyavatar_shape'],
        $mybb->settings['fancyavatar_mouth'],
        $mybb->settings['fancyavatar_expression'],
        $mybb->settings['fancyavatar_radius'],
        $mybb->settings['fancyavatar_uiavatars_color'],
        $mybb->settings['fancyavatar_uiavatars_bold'],
        $mybb->settings['fancyavatar_uiavatars_length'],
        $mybb->settings['fancyavatar_avataaars_style'],
    )));
}

/**
 * Batch update all users with default avatars
 *
 * @return int Number of users updated
 */
function fancyavatar_batch_update()
{
    global $db, $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return 0;
    }

    $default_avatar = $mybb->settings['useravatar'];
    $size = intval($mybb->settings['fancyavatar_size']);
    $updated = 0;

    $exclude_groups = $mybb->settings['fancyavatar_exclude_groups'];
    $where = "avatar = '' OR avatar IS NULL";

    if (!empty($default_avatar)) {
        $where .= " OR avatar = '" . $db->escape_string($default_avatar) . "'";
    }

    if (!empty($exclude_groups)) {
        $where .= " AND usergroup NOT IN (" . $db->escape_string($exclude_groups) . ")";
    }

    $query = $db->query("
        SELECT uid, username, email
        FROM " . TABLE_PREFIX . "users
        WHERE {$where}
        ORDER BY uid ASC
    ");

    while ($user = $db->fetch_array($query)) {
        $avatar_url = fancyavatar_generate_url($user['username'], $user['email'], $user['uid']);

        $db->update_query('users', array(
            'avatar'           => $db->escape_string($avatar_url),
            'avatartype'       => 'remote',
            'avatardimensions' => $size . '|' . $size,
        ), 'uid = ' . intval($user['uid']));

        $updated++;
    }

    return $updated;
}

/**
 * Force Fancy Avatar on ALL users (overwrites custom avatars)
 *
 * @return int Number of users updated
 */
function fancyavatar_force_update()
{
    global $db, $mybb;

    if (!$mybb->settings['fancyavatar_enabled']) {
        return 0;
    }

    $size = intval($mybb->settings['fancyavatar_size']);
    $updated = 0;

    $query = $db->query("
        SELECT uid, username, email
        FROM " . TABLE_PREFIX . "users
        ORDER BY uid ASC
    ");

    while ($user = $db->fetch_array($query)) {
        $avatar_url = fancyavatar_generate_url($user['username'], $user['email'], $user['uid']);

        $db->update_query('users', array(
            'avatar'           => $db->escape_string($avatar_url),
            'avatartype'       => 'remote',
            'avatardimensions' => $size . '|' . $size,
        ), 'uid = ' . intval($user['uid']));

        $updated++;
    }

    return $updated;
}

/**
 * Get available DiceBear styles
 *
 * @return array
 */
function fancyavatar_get_dicebear_styles()
{
    return array(
        'adventurer'        => 'Adventurer',
        'adventurer-neutral'=> 'Adventurer Neutral',
        'avataaars'         => 'Avataaars',
        'avataaars-neutral' => 'Avataaars Neutral',
        'big-ears'          => 'Big Ears',
        'big-ears-neutral'  => 'Big Ears Neutral',
        'big-smile'         => 'Big Smile',
        'bottts'            => 'Bottts',
        'bottts-neutral'    => 'Bottts Neutral',
        'fun-emoji'         => 'Fun Emoji',
        'icons'             => 'Icons',
        'identicon'         => 'Identicon',
        'initials'          => 'Initials',
        'lorelei'           => 'Lorelei',
        'lorelei-neutral'   => 'Lorelei Neutral',
        'micah'             => 'Micah',
        'micah-neutral'     => 'Micah Neutral',
        'miniavs'           => 'Miniavs',
        'personas'          => 'Personas',
        'pixel-art'         => 'Pixel Art',
        'pixel-art-neutral' => 'Pixel Art Neutral',
        'rings'             => 'Rings',
        'shapes'            => 'Shapes',
        'thumbs'            => 'Thumbs',
    );
}
