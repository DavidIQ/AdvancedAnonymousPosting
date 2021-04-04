<?php
/**
 *
 * Advanced Guest Posting. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, David Colón, https://www.davidiq.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace davidiq\advancedguestposting\event;

/**
 * @ignore
 */

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
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

    /** @var driver_interface Database object */
    protected $db;

    /** @var string guest_info table name */
    protected $guest_info_table;

    public static function getSubscribedEvents()
    {
        return [
            'core.posting_modify_template_vars' => 'load_form',
            'core.posting_modify_submission_errors' => 'verify_tos_acceptance',
            'core.posting_modify_submit_post_after' => 'save_guest_info',
            'core.posting_modify_post_data' => 'registration_nag',
            'core.modify_posting_parameters' => 'registration_nag_confirm',
            'core.ucp_register_data_before' => 'add_registration_data'
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
     * @param driver_interface $db Database object
     * @param string $root_path phpBB root path
     * @param string $php_ext phpEx
     * @param string $guest_info_table guest_info table name
     */
    public function __construct(request $request, language $language, template $template, config $config, user $user, driver_interface $db, string $root_path, string $php_ext, string $guest_info_table)
    {
        $this->request = $request;
        $this->language = $language;
        $this->template = $template;
        $this->config = $config;
        $this->user = $user;
        $this->db = $db;
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
        $this->guest_info_table = $guest_info_table;
    }

    /**
     * Loads form data
     *
     * @param \phpbb\event\data $event Event object
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

        $email_var = $this->config['allow_sfs'] ? 'email' : 'session_email';

        $this->template->assign_vars([
            'SESSION_EMAIL' => $this->request->variable($email_var, $this->user->data['session_email']),
            'ADVANCEDGUESTPOSTING_TOS_ACCEPT' => sprintf($this->language->lang('ADVANCEDGUESTPOSTING_TOS'), $tos_link),
        ]);
    }

    /**
     * Ensures TOS have been accepted
     *
     * @param \phpbb\event\data $event Event object
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
     * @param \phpbb\event\data $event Event object
     */
    public function save_guest_info($event)
    {
        if ($this->user->data['is_registered'])
        {
            return;
        }

        $email_var = $this->config['allow_sfs'] ? 'email' : 'session_email';

        $session_data = [
            'session_username' => $this->request->variable('username', ''),
            'session_email' => $this->request->variable($email_var, ''),
            'session_posts' => (int)$this->user->data['session_posts'] + 1
        ];

        $this->user->update_session($session_data);

        if ($this->config['davidiq_advancedguestposting_store_guest_info'] && !empty($session_data['session_email']))
        {
            $post_id = (int)$event['data']['post_id'];
            $result = $this->db->sql_query_limit("SELECT COUNT(1) AS cnt
                FROM {$this->guest_info_table}
                WHERE post_id = " . $post_id, 1);
            $count = $this->db->sql_fetchfield('cnt');
            $this->db->sql_freeresult($result);
            if (!$count)
            {
                $this->db->sql_query("INSERT INTO {$this->guest_info_table} " . $this->db->sql_build_array('INSERT', [
                        'post_id' => $post_id,
                        'guest_email' => $session_data['session_email'],
                    ]));
            } else
            {
                $this->db->sql_query("UPDATE {$this->guest_info_table}
                        SET guest_email = '" . $this->db->sql_escape($session_data['session_email']) . "'
                        WHERE post_id = $post_id");
            }
        }
    }

    /**
     * Determines if registration nag screen should be displayed
     *
     * @param \phpbb\event\data $event Event object
     */
    public function registration_nag($event)
    {
        $davidiq_advancedguestposting_reg_posts = (int)$this->config['davidiq_advancedguestposting_reg_posts'];
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
     * @param \phpbb\event\data $event Event object
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
        } else
        {
            redirect("{$this->root_path}{$advancedguestposting_confirm_page}");
        }
    }

    /**
     * Adds session data to registration form
     *
     * @param \phpbb\event\data $event Event object
     */
    public function add_registration_data($event)
    {
        $data = $event['data'];

        $username = $data['username'];
        $email = $data['email'];

        $data['username'] = !isset($username) ? $this->user->data['session_username'] : $username;
        $data['email'] = !isset($email) ? $this->user->data['session_email'] : $email;

        $event['data'] = $data;
    }
}
