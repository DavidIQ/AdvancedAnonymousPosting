<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace davidiq\advancedguestposting\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advanced Guest Posting Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_template_vars'	=> 'display_forums_modify_template_vars',
		);
	}

	/**
	 * A sample PHP event
	 * Modifies the names of the forums on index
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function display_forums_modify_template_vars($event)
	{
		$forum_row = $event['forum_row'];
		$forum_row['FORUM_NAME'] .= ' :: ' . $this->language->lang('advancedguestposting_EVENT') . ' :: ';
		$event['forum_row'] = $forum_row;
	}
}
