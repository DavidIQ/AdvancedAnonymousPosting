<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace davidiq\advancedguestposting\acp;

/**
 * Advanced Guest Posting ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\davidiq\advancedguestposting\acp\main_module',
			'title'		=> 'ACP_advancedguestposting_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_advancedguestposting',
					'auth'	=> 'ext_davidiq/advancedguestposting && acl_a_board',
					'cat'	=> array('ACP_advancedguestposting_TITLE')
				),
			),
		);
	}
}
