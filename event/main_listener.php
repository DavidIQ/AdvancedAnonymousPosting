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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Advanced Guest Posting Event listener.
 */
class main_listener implements EventSubscriberInterface
{

	/** @var language Language object */
	protected $language;

	/** @var string phpEx */
	protected $php_ext;

	/** @var request Request object */
	protected $request;

	/** @var template Template object */
	protected $template;

	/** @var config Config object */
	protected $config;

	/** @var string Forum root path */
	protected $root_path;

	/** @var user User object */
	protected $user;

	public static function getSubscribedEvents()
	{
		return [
			'core.posting_modify_template_vars'     => 'load_form',
         'core.posting_modify_submission_errors' => 'verify_tos_acceptance',
         'core.posting_modify_submit_post_after' => 'save_guest_info',
         'core.posting_modify_post_data'         => 'registration_nag',
         'core.modify_posting_parameters'        => 'registration_nag_confirm',
         'core.ucp_register_data_before'         => 'add_registration_data'
		];
	}

   /**
    * Listener constructor
    *
    * @param request $request Request object
    * @param language $language Language object
    * @param template $template Template object
    * @param config $config Config object
    * @param user $user User object
    * @param string $root_path
    * @param string $php_ext phpEx
    */
	public function __construct(request $request, language $language, template $template, config $config, user $user, $root_path, $php_ext)
	{
	   $this->request = $request;
		$this->language = $language;
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Loads form data
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_form($event)
	{
      if ($this->user->data['is_registered'])
      {
         return;
      }

      $this->language->add_lang('common', 'davidiq/advancedguestposting');
      $tos_link = $this->config['davidiq_advancedguestposting_tos_link'];
      $tos_link = empty($tos_link) ? append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=terms') : $tos_link;

      $page_data = $event['page_data'];
      $page_data['USERNAME'] = $this->request->variable('username', $this->user->data['session_username']);
      $event['page_data'] = $page_data;

      $this->template->assign_vars([
         //'S_ERROR'		=> $s_errors,
         //'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
         'SESSION_EMAIL'                   => $this->request->variable('session_email', $this->user->data['session_email']),
         'ADVANCEDGUESTPOSTING_TOS_ACCEPT' => sprintf($this->language->lang('ADVANCEDGUESTPOSTING_TOS'), $tos_link),
      ]);
	}

   /**
	 * Ensures TOS have been accepted
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function verify_tos_acceptance($event)
   {
      if ($this->user->data['is_registered'])
      {
         return;
      }

	   if (!$this->request->variable('advancedguestposting_tos_accept', false))
	   {
	      $error = $event['error'];
	      $this->language->add_lang('common', 'davidiq/advancedguestposting');
	      $error[] = $this->language->lang('ADVANCEDGUESTPOSTING_TOS_ACCEPT_ERROR');
	      $event['error'] = $error;
      }
   }

   /**
	 * Saves guest info to sessions table
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
   public function save_guest_info($event)
   {
      if ($this->user->data['is_registered'])
      {
         return;
      }

      $session_data = [
         'session_username' => $this->request->variable('username', ''),
         'session_email'    => $this->request->variable('session_email', ''),
         'session_posts'    => (int) $this->user->data['session_posts'] + 1
      ];

      $this->user->update_session($session_data);
   }

   /**
	 * Determines if registration nag screen should be displayed
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
   public function registration_nag($event)
   {
      $davidiq_advancedguestposting_reg_posts = (int) $this->config['davidiq_advancedguestposting_reg_posts'];
      if ($this->user->data['is_registered'] || !$davidiq_advancedguestposting_reg_posts
         || (int)$this->user->data['session_posts'] < $davidiq_advancedguestposting_reg_posts)
      {
         return;
      }

      $advancedguestposting_confirm_page = $this->request->variable('advancedguestposting_confirm_page', '');
      if (empty($advancedguestposting_confirm_page))
      {
         $this->language->add_lang('common', 'davidiq/advancedguestposting');

         confirm_box(
            false,
            $this->language->lang('ADVANCEDGUESTPOSTING_REGISTER_NAG'),
            build_hidden_fields(['advancedguestposting_confirm_page' => $this->user->page['page']]));
      }
   }

   /**
	 * Determines if registration nag screen was displayed
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
   public function registration_nag_confirm($event)
   {
      $advancedguestposting_confirm_page = $this->request->variable('advancedguestposting_confirm_page', '');
      if ($this->user->data['is_registered'] || empty($advancedguestposting_confirm_page))
      {
         return;
      }

      $this->user->update_session(['session_posts' => 0]);

      if (confirm_box(true))
      {
         redirect(append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=register'));
      }
      else
      {
         redirect("{$this->root_path}{$advancedguestposting_confirm_page}");
      }
   }

   /**
	 * Adds session data to registration form
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
   public function add_registration_data($event)
   {
      $data = $event['data'];

      $username = $data['username'];
      $email = $data['email'];

      $data['username'] = empty($username) ? $this->user->data['session_username'] : $username;
      $data['email'] = empty($email) ? $this->user->data['session_email'] : $email;

      $event['data'] = $data;
   }
}
