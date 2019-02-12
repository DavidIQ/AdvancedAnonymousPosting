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
use phpbb\language\language;
use phpbb\request\request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advanced Guest Posting Event listener.
 */
class main_listener implements EventSubscriberInterface
{

	/**
	 * @var language Language object
	 */
	protected $language;

	/**
	 * @var request Request object
	 */
	protected $request;

	public static function getSubscribedEvents()
	{
		return [
			'core.page_header'					=> 'load_lang',
		];
	}

   /**
	 * Listener constructor
	 *
	 * @param request  $request  Request object
	 * @param language $language Language object
	 */
	public function __construct(request $request, language $language)
	{
		$this->language = $language;
	}

	/**
	 * Loads language
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_lang($event)
	{
		$this->language->add_lang('common', 'davidiq/advancedguestposting');
	}
}
