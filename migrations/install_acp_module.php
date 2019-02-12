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

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['davidiq_advancedguestposting_goodbye']);
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('davidiq_advancedguestposting_goodbye', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_advancedguestposting_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_advancedguestposting_TITLE',
				array(
					'module_basename'	=> '\davidiq\advancedguestposting\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
