<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace davidiq\advancedguestposting\migrations;

class install_guest_info_table extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'guest_info');
    }

    public static function depends_on()
    {
        return array('\phpbb\db\migration\data\v320\v320');
    }

    public function update_schema()
    {
        return [
            'add_tables' 		=> [
                $this->table_prefix . 'guest_info'	=> [
                    'COLUMNS'		=> [
                        'post_id'					=> ['UINT', 0],
                        'guest_email'				=> ['VCHAR:100', ''],
                    ],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables'		=> [
                $this->table_prefix . 'guest_info',
            ],
        ];
    }
}
