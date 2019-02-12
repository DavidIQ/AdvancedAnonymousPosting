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

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_advancedguestposting');
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'davidiq_advancedguestposting_table'	=> array(
					'COLUMNS'		=> array(
						'advancedguestposting_id'			=> array('UINT', null, 'auto_increment'),
						'advancedguestposting_name'			=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'	=> 'advancedguestposting_id',
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_advancedguestposting'				=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_advancedguestposting',
				),
			),
			'drop_tables'		=> array(
				$this->table_prefix . 'davidiq_advancedguestposting_table',
			),
		);
	}
}
