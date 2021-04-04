<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang,
    [
        'ACP_ADVANCEDGUESTPOSTING_TITLE' => 'Advanced Guest Posting',
        'ACP_ADVANCEDGUESTPOSTING' => 'Settings',

        'ACP_ADVANCEDGUESTPOSTING_REG_POSTS' => 'Post count for registration reminders',
        'ACP_ADVANCEDGUESTPOSTING_REG_POSTS_EXPLAIN' => 'Number of posts after which to remind guest user to register. 0 disables the registration reminder.',
        'ACP_ADVANCEDGUESTPOSTING_TOS_LINK' => 'Terms of service URL',
        'ACP_ADVANCEDGUESTPOSTING_TOS_LINK_EXPLAIN' => 'Specify a full URL to a terms of service page to use in the terms of service acceptance checkbox text. Leave blank to use the default phpBB terms of service page.',
        'ACP_ADVANCEDGUESTPOSTING_STORE_GUEST_INFO' => 'Enable guest info storage',
        'ACP_ADVANCEDGUESTPOSTING_STORE_GUEST_INFO_EXPLAIN' => 'Enables storage of guest info associated to a post in a table.',

        'ACP_ADVANCEDGUESTPOSTING_SETTINGS_SAVED' => 'Advanced Guest Posting Settings saved.',
        'LOG_ACP_ADVANCEDGUESTPOSTING_SETTINGS' => '<strong>Advanced Guest Posting settings updated</strong>',
    ]
);
