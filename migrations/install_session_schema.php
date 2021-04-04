<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace davidiq\advancedguestposting\migrations;

class install_session_schema extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'sessions', 'session_email');
    }

    public static function depends_on()
    {
        return array('\phpbb\db\migration\data\v320\v320');
    }

    public function update_schema()
    {
        return [
            'add_columns' => [
                $this->table_prefix . 'sessions' => [
                    'session_username' => ['VCHAR:255', ''],
                    'session_email' => ['VCHAR:100', ''],
                    'session_posts' => ['INT:11', 0]
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_columns' => [
                $this->table_prefix . 'sessions' => [
                    'session_username',
                    'session_email',
                    'session_posts'
                ],
            ],
        ];
    }
}
