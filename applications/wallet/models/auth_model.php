<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {

    // The following method prevents an error occurring when $this->data is modified.
    // Error Message: 'Indirect modification of overloaded property Demo_cart_admin_model::$data has no effect'.
    public function &__get($key) {
        $CI =& get_instance();
        return $CI->$key;
    }

    public function __construct()
    {
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('cookie');
        $this->load->config('auth', TRUE);
        $this->lang->load('auth');

        ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

        // Sessions and cookies
        $this->auth->session_name = $this->config->item('sessions','auth');
        $this->auth->cookie_name = $this->config->item('cookies','auth');

        // Get the current auth session, else get the default values
        if ($this->session->userdata($this->auth->session_name['name']) !== FALSE)
        {
            $this->auth->session_data = $this->session->userdata($this->auth->session_name['name']);
        }
        else
        {
            $this->auth->session_data = $this->set_auth_defaults();
        }

        // Database tables and settings
        $this->auth->database_config = $database_config = $this->config->item('database','auth');

        // Prefix each table column with the name of the parent table.
        foreach($database_config as $table_key => $table_data)
        {
            if (! empty($table_data['table']) && ! empty($table_data['columns']))
            {
                foreach($table_data['columns'] as $column_reference => $column_name)
                {
                    $database_config[$table_key]['columns'][$column_reference] = $table_data['table'].'.'.$column_name;
                }

                if (! empty($table_data['custom_columns']))
                {
                    $database_config[$table_key]['custom_columns'] = array();

                    foreach($table_data['custom_columns'] as $column_reference => $column_name)
                    {
                        $database_config[$table_key]['custom_columns'][$column_name] = $table_data['table'].'.'.$column_name;
                    }
                }
            }
            // Prefix the primary key, foreign key and custom columns of any custom tables.
            else if ($table_key == 'custom')
            {
                foreach($table_data as $custom_table_key => $table_data)
                {
                    if (! empty($table_data['table']) && ! empty($table_data['primary_key']))
                    {
                        $database_config['custom'][$custom_table_key]['primary_key'] = $table_data['table'].'.'.$table_data['primary_key'];
                    }
                    if (! empty($table_data['table']) && ! empty($table_data['foreign_key']))
                    {
                        $database_config['custom'][$custom_table_key]['foreign_key'] = $table_data['table'].'.'.$table_data['foreign_key'];
                    }
                    if (! empty($table_data['table']) && ! empty($table_data['custom_columns']))
                    {
                        foreach($table_data['custom_columns'] as $column_reference => $column_name)
                        {
                            $database_config['custom'][$custom_table_key]['custom_columns'][$column_reference] =  $table_data['table'].'.'.$column_name;
                        }
                    }
                }
            }
        }

        // User session table
        $this->auth->tbl_user_session = $database_config['user_sess']['table'];
        $this->auth->tbl_join_user_session = $database_config['user_sess']['join'];
        $this->auth->tbl_col_user_session = $database_config['user_sess']['columns'];

        // User group table
        $this->auth->tbl_user_group = $database_config['user_group']['table'];
        $this->auth->tbl_join_user_group = $database_config['user_group']['join'];
        $this->auth->tbl_col_user_group = $database_config['user_group']['columns'];

        // User privilege tables
        $this->auth->tbl_user_privilege = $database_config['user_privileges']['table'];
        $this->auth->tbl_col_user_privilege = $database_config['user_privileges']['columns'];
        $this->auth->tbl_user_privilege_users = $database_config['user_privilege_users']['table'];
        $this->auth->tbl_col_user_privilege_users = $database_config['user_privilege_users']['columns'];

        // User group privilege tables
        $this->auth->tbl_user_privilege_groups = $database_config['user_privilege_groups']['table'];
        $this->auth->tbl_col_user_privilege_groups = $database_config['user_privilege_groups']['columns'];

        // User main account table
        $this->auth->tbl_user_account = $database_config['user_acc']['table'];
        $this->auth->tbl_join_user_account = $database_config['user_acc']['join'];
        $this->auth->tbl_col_user_account = array_merge($database_config['user_acc']['columns'], $database_config['user_acc']['custom_columns']);

        // User custom data table(s)
        $this->auth->tbl_custom_data = (! empty($database_config['custom'])) ? $database_config['custom'] : array();

        // Primary user identity column
        $this->auth->primary_identity_col = $database_config['user_acc']['table'].'.'.$database_config['settings']['primary_identity_col'];

        // Database settings
        $this->auth->db_settings = $database_config['settings'];

        // Security settings
        $this->auth->auth_security = $this->config->item('security','auth');

        // General settings
        $this->auth->auth_settings = $this->config->item('settings','auth');

        // Email settings
        $this->auth->email_settings = $this->config->item('email','auth');

        // Set auth SQL clauses
        $this->auth->select = $this->auth->join = $this->auth->order_by = $this->auth->group_by = $this->auth->limit = array();
        $this->auth->where = $this->auth->or_where = $this->auth->where_in = array();
        $this->auth->or_where_in = $this->auth->where_not_in = $this->auth->or_where_not_in = array();
        $this->auth->like = $this->auth->or_like = $this->auth->not_like = $this->auth->or_not_like = array();

        // Status and error messages.
        $this->auth->message_settings = $this->config->item('messages', 'auth');
        $this->auth->status_messages = array('public' => array(), 'admin' => array());
        $this->auth->error_messages = array('public' => array(), 'admin' => array());

        // Global template data.
        $this->auth->template_data = array();
    }

    public function set_auth_defaults() {
        foreach($this->auth->session_name as $session_name => $session_alias)
        {
            if (!in_array($session_name,array('name','math_captcha')))
            {
                $this->auth->session_data[$session_alias] = FALSE;
            }
        }

        $this->session->set_userdata(array($this->auth->session_name['name'] => $this->auth->session_data));
    }

    /**
     * Used in conjunction with $config['validate_login_onload'] set via the config file.
     * Validates a browser login session token with a stored database login token.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function validate_database_login_session() {
        $user_id = $this->auth->session_data[$this->auth->session_name['user_id']];
        $session_token = $this->auth->session_data[$this->auth->session_name['login_session_token']];

        $sql_where = array(
            $this->auth->tbl_col_user_account['id'] => $user_id,
            $this->auth->tbl_col_user_account['suspend'] => 0,
            $this->auth->tbl_col_user_session['token'] => $session_token
        );

        // If a session expire time is defined, check its valid.
        if ($this->auth->auth_security['login_session_expire'] > 0)
        {
            $sql_where[$this->auth->tbl_col_user_session['date'].' > '] = $this->database_date_time(-$this->auth->auth_security['login_session_expire']);
        }

        $query = $this->db->from($this->auth->tbl_user_account)
            ->join($this->auth->tbl_user_session, $this->auth->tbl_join_user_account.' = '.$this->auth->tbl_join_user_session)
            ->where($sql_where)
            ->get();

        ###+++++++++++++++++++++++++++++++++###

        // User login credentials are valid, continue as normal.
        if ($query->num_rows() == 1)
        {
            // Get database session token and hash it to try and match hashed cookie token if required for the 'logout_user_onclose' or 'login_via_password_token' features.
            $session_token = $query->row()->{$this->auth->database_config['user_sess']['columns']['token']};
            $hash_session_token = $this->hash_cookie_token($session_token);

            // Validate if user has closed their browser since login (Defined by config file).
            if ($this->auth->auth_security['logout_user_onclose'])
            {
                if (get_cookie($this->auth->cookie_name['login_session_token']) != $hash_session_token)
                {
                    $this->set_error_message('login_session_expired', 'config');
                    $this->logout(FALSE);
                    return FALSE;
                }
            }
            // Check whether to unset the users 'Logged in via password' status if they closed their browser since login (Defined by config file).
            else if ($this->auth->auth_security['unset_password_status_onclose'])
            {
                if (get_cookie($this->auth->cookie_name['login_via_password_token']) != $hash_session_token)
                {
                    $this->delete_logged_in_via_password_session();
                    return FALSE;
                }
            }

            // Extend users login time if defined by config file.
            if ($this->auth->auth_security['extend_login_session'])
            {
                // Set extension time.
                $sql_update[$this->auth->tbl_col_user_session['date']] = $this->database_date_time();

                $sql_where = array(
                    $this->auth->tbl_col_user_session['user_id'] => $user_id,
                    $this->auth->tbl_col_user_session['token'] => $session_token
                );

                $this->db->update($this->auth->tbl_user_session, $sql_update, $sql_where);
            }

            // If loading the 'complete' library, it extends the 'lite' library with additional functions,
            // however, this would also runs the __construct twice, causing the user to wastefully be verified twice.
            // To counter this, the 'auth_verified' var is set to indicate the user has already been verified for this page load.
            return $this->auth_verified = TRUE;
        }
        // The users login session token has either expired, is invalid (Not found in database), or their account has been deactivated since login.
        // Attempt to log the user in via any defined 'Remember Me' cookies.
        // If the "Remember me' cookies are valid, the user will have 'logged_in' credentials, but will have no 'logged_in_via_password' credentials.
        // If the user cannot be logged in via a 'Remember me' cookie, the user will be stripped of any login session credentials.
        // Note: If the user is also logged in on another computer using the same identity, those sessions are not deleted as they will be authenticated when they next login.
        else
        {
            $this->delete_logged_in_via_password_session();
            return FALSE;
        }
    }

    /**
     * Note: $all_sessions variable allows you to define whether to delete all database session or just the current session.
     * When set to FALSE, this can be used to logout a user off of one computer (Internet Cafe) but not another (Home).
     *
     * @return bool
     * @author Rob Hussey
     */
    public function logout($all_sessions = TRUE) {
        $user_id = $this->auth->session_data[$this->auth->session_name['user_id']];

        // Delete database login sessions and 'Remember me' cookies.
        $this->delete_database_login_session($user_id, $all_sessions);

        // Delete session login data.
        $this->auth->session_data = $this->set_auth_defaults();
        $this->session->unset_userdata($this->auth->session_name['name']);

        // Run database maintenance function to clean up any expired login sessions.
        $this->delete_expired_remember_users();

        return TRUE;
    }

    /**
     * Cleanup function to delete all expired 'Remember me' sessions from database.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function delete_expired_remember_users($expire_time = FALSE) {
        if (!$expire_time)
        {
            $expire_time = $this->auth->auth_security['user_cookie_expire'];
        }

        // Create expire date.
        $expire_date = $this->database_date_time(-$expire_time);

        $this->db->delete($this->auth->tbl_user_session, array($this->auth->tbl_col_user_session['date'].' < ' => $expire_date));

        return $this->db->affected_rows() > 0;
    }

    /**
     * Format the current or a submitted date and time (in seconds).
     * Additional time can be added / subtracted.
     *
     * @return void
     * @author Rob Hussey
     */
    public function database_date_time($apply_time = 0, $time = FALSE, $force_unix = FALSE) {
        // Get timestamp of either submitted time or current time.
        if ($time) {
            $time = (is_numeric($time) && strlen($time) == 10) ? $time : strtotime($time);
        }
        else {
            $time = time();
        }

        // Add or subtract any submitted apply time.
        $time += $apply_time;

        // If database time is set as UNIX via config file, or if a unix time has been requested.
        if ((is_numeric($this->auth->db_settings['date_time']) && strlen($this->auth->db_settings['date_time']) == 10) || $force_unix) {
            return $time;
        }
        else if (is_string($this->auth->db_settings['date_time']) && strtotime($this->auth->db_settings['date_time'])) // MySQL datetime.
        {
            return date('Y-m-d H:i:s', $time);
        }
        else // Return time set via config file.
        {
            return $this->auth->db_settings['date_time'];
        }
    }

    /**
     * Create a hash of users database session token, users browser details and a static salt.
     * This can help invalidate hijacked cookies used from a different browser.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function hash_cookie_token($data) {
        if (empty($data))
        {
            return FALSE;
        }

        $browser = $this->auth->auth_security['static_salt'].$this->input->server('HTTP_USER_AGENT');

        return sha1($data.$browser);
    }

    /**
     * Attempt to log the user in via any defined 'Remember me' cookies.
     * If the user cannot be logged in via a 'Remember me' cookie, then remove any login credentials assigned to the users session.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function delete_logged_in_via_password_session() {
        if (! $this->login_remembered_user())
        {
            $this->set_error_message('login_session_expired', 'config');
            $this->session->set_userdata(array($this->auth->session_name['name'] => $this->set_auth_defaults()));
        }

        return TRUE;
    }

    /**
     * @return bool
     * @author Rob Hussey
     * @author Ben Edmunds
     */
    public function login_remembered_user() {
        if (!get_cookie($this->auth->cookie_name['user_id']) || !get_cookie($this->auth->cookie_name['remember_series']) ||
            !get_cookie($this->auth->cookie_name['remember_token']))
        {
            return FALSE;
        }

        $user_id = get_cookie($this->auth->cookie_name['user_id']);
        $remember_series = get_cookie($this->auth->cookie_name['remember_series']);
        $remember_token = get_cookie($this->auth->cookie_name['remember_token']);

        $sql_select = array(
            $this->auth->primary_identity_col,
            $this->auth->tbl_col_user_account['id'],
            $this->auth->tbl_col_user_account['group_id'],
            $this->auth->tbl_col_user_account['activation_token'],
            $this->auth->tbl_col_user_account['last_login_date']
        );

        // Database session tokens are hashed with user-agent to 'help' invalidate hijacked cookies used from different browser.
        $sql_where = array(
            $this->auth->tbl_col_user_account['id'] => $user_id,
            $this->auth->tbl_col_user_account['suspend'] => 0,
            $this->auth->tbl_col_user_session['series'] => $this->hash_cookie_token($remember_series),
            $this->auth->tbl_col_user_session['token'] => $this->hash_cookie_token($remember_token),
            $this->auth->tbl_col_user_session['date'].' > ' => $this->database_date_time(-$this->auth->auth_security['user_cookie_expire'])
        );

        $query = $this->db->select($sql_select)
            ->from($this->auth->tbl_user_account)
            ->join($this->auth->tbl_user_session, $this->auth->tbl_join_user_account.' = '.$this->auth->tbl_join_user_session)
            ->where($sql_where)
            ->get();

        ###+++++++++++++++++++++++++++++++++###

        // If user exists.
        if ($query->num_rows() == 1)
        {
            $user = $query->row();

            // If an activation time limit is defined by config file and account hasn't been activated by email.
            if ($this->auth->auth_settings['account_activation_time_limit'] > 0 &&
                !empty($user->{$this->auth->database_config['user_acc']['columns']['activation_token']}))
            {
                if (!$this->validate_activation_time_limit($user->{$this->auth->database_config['user_acc']['columns']['last_login_date']}))
                {
                    $this->set_error_message('account_requires_activation', 'config');
                    return FALSE;
                }
            }

            // Set user login sessions.
            if ($this->set_login_sessions($user))
            {
                // Extend 'Remember me' if defined by config file.
                if ($this->auth->auth_security['extend_cookies_on_login'])
                {
                    $this->remember_user($user->{$this->auth->database_config['user_acc']['columns']['id']});
                }
                return TRUE;
            }
        }

        // 'Remember me' has been unsuccessful, for security, remove any existing cookies and database sessions.
        $this->delete_database_login_session($user_id);

        return FALSE;
    }

    /**
     * Validate whether a non-activatated account is within the activation time limit set via config.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function validate_activation_time_limit($last_login) {
        if (empty($last_login))
        {
            return FALSE;
        }

        // Set account activation expiry time.
        $expire_time = (60 * $this->auth->auth_settings['account_activation_time_limit']); // 60 Secs * expire minutes.

        // Convert if using MySQL time.
        if (strtotime($last_login))
        {
            $last_login = strtotime($last_login);
        }

        // Account activation time has expired, user must now activate account via email.
        if (($last_login + $expire_time) < time())
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Set all login session and database data.
     *
     * @return bool
     * @author Rob Hussey / Filou Tschiemer
     */
    private function set_login_sessions($user, $logged_in_via_password = TRUE) {
        if (!$user) {
            return FALSE;
        }

        $user_id = $user->{$this->auth->database_config['user_acc']['columns']['id']};

        // Regenerate CI session_id on successful login.
        $this->regenerate_ci_session_id();

        // Update users last login date.
        $this->update_last_login($user_id);

        // Set database and login session token if defined by config file.
        if ($this->auth->auth_security['validate_login_onload'] && ! $this->insert_database_login_session($user_id))
        {
            return FALSE;
        }

        // Set user id and identifier data to session.
        $this->auth->session_data[$this->auth->session_name['user_id']] = $user_id;
        $this->auth->session_data[$this->auth->session_name['user_identifier']] = $user->{$this->auth->db_settings['primary_identity_col']};

        // Get group data.
        $sql_where[$this->auth->tbl_col_user_group['id']] = $user->{$this->auth->database_config['user_acc']['columns']['group_id']};

        $group = $this->get_groups(FALSE, $sql_where)->row();

        // Set admin status to session.
        $this->auth->session_data[$this->auth->session_name['is_admin']] = ($group->{$this->auth->database_config['user_group']['columns']['admin']} == 1);

        $this->auth->session_data[$this->auth->session_name['group']] =
            array($group->{$this->auth->database_config['user_group']['columns']['id']} => $group->{$this->auth->database_config['user_group']['columns']['name']});

        ###+++++++++++++++++++++++++++++++++###

        $privilege_sources = $this->auth->auth_settings['privilege_sources'];
        $privileges = array();

        // If 'user' privileges have been defined within the config 'privilege_sources'.
        if (in_array('user', $privilege_sources))
        {
            // Get user privileges.
            $sql_select = array(
                $this->auth->tbl_col_user_privilege['id'],
                $this->auth->tbl_col_user_privilege['name']
            );

            $sql_where = array($this->auth->tbl_col_user_privilege_users['user_id'] => $user_id);

            $query = $this->get_user_privileges($sql_select, $sql_where);

            // Create an array of user privileges.
            if ($query->num_rows() > 0)
            {
                foreach($query->result_array() as $data)
                {
                    $privileges[$data[$this->auth->database_config['user_privileges']['columns']['id']]] = $data[$this->auth->database_config['user_privileges']['columns']['name']];
                }
            }
        }

        // If 'group' privileges have been defined within the config 'privilege_sources'.
        if (in_array('group', $privilege_sources))
        {
            // Get group privileges.
            $sql_select = array(
                $this->auth->tbl_col_user_privilege['id'],
                $this->auth->tbl_col_user_privilege['name']
            );

            $sql_where = array($this->auth->tbl_col_user_privilege_groups['group_id'] => $user->{$this->auth->database_config['user_acc']['columns']['group_id']});

            $query = $this->get_user_group_privileges($sql_select, $sql_where);

            // Extend array of user privileges by group privileges.
            if ($query->num_rows() > 0)
            {
                foreach($query->result_array() as $data)
                {
                    $privileges[$data[$this->auth->database_config['user_privileges']['columns']['id']]] = $data[$this->auth->database_config['user_privileges']['columns']['name']];
                }
            }
        }

        // Set user privileges to session.
        $this->auth->session_data[$this->auth->session_name['privileges']] = $privileges;

        ###+++++++++++++++++++++++++++++++++###

        $this->session->set_userdata(array($this->auth->session_name['name'] => $this->auth->session_data));

        return TRUE;
    }

    /**
     * Returns a list of all privileges matching the $sql_where condition.
     *
     * @return void
     * @author Rob Hussey
     */
    public function get_privileges($sql_select, $sql_where) {
        // Set any custom defined SQL statements.
        $this->set_custom_sql_to_db($sql_select, $sql_where);

        return $this->db->get($this->auth->tbl_user_privilege);
    }

    /**
     * Returns a list of user privileges matching the $sql_where condition.
     *
     * @return void
     * @author Rob Hussey
     */
    public function get_user_privileges($sql_select, $sql_where) {
        // Set any custom defined SQL statements.
        $this->set_custom_sql_to_db($sql_select, $sql_where);

        return $this->db->from($this->auth->tbl_user_privilege)
            ->join($this->auth->tbl_user_privilege_users, $this->auth->tbl_col_user_privilege['id'].' = '.$this->auth->tbl_col_user_privilege_users['privilege_id'])
            ->get();
    }

    /**
     * Returns a list of user group privileges matching the $sql_where condition.
     *
     * @return void
     * @author Rob Hussey / Filou Tschiemer
     */
    public function get_user_group_privileges($sql_select, $sql_where) {
        // Set any custom defined SQL statements.
        $this->set_custom_sql_to_db($sql_select, $sql_where);

        return $this->db->from($this->auth->tbl_user_privilege)
            ->join($this->auth->tbl_user_privilege_groups, $this->auth->tbl_col_user_privilege['id'].' = '.$this->auth->tbl_col_user_privilege_groups['privilege_id'])
            ->get();
    }

    /**
     * Used in conjunction with $config['validate_login_onload'] set via the config file.
     * The function inserts a login session token into the database and browser session.
     * These two tokens are then compared on every page load to ensure the users session is still valid.
     *
     * This method offers more control over login security as you can logout users immediately (By removing their database session or
     * suspending / deactivating them), rather than having to wait for the users CodeIgniter session to expire.
     * However, it requires more database calls per page load.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function insert_database_login_session($user_id) {
        if (!is_numeric($user_id))
        {
            return FALSE;
        }

        // Generate session token.
        $session_token = sha1($this->generate_token(20));

        $sql_insert = array(
            $this->auth->tbl_col_user_session['user_id'] => $user_id,
            $this->auth->tbl_col_user_session['token'] => $session_token,
            $this->auth->tbl_col_user_session['date'] => $this->database_date_time()
        );

        $this->db->insert($this->auth->tbl_user_session, $sql_insert);

        if ($this->db->affected_rows() > 0)
        {
            // Create session.
            $this->auth->session_data[$this->auth->session_name['login_session_token']] = $session_token;
            $this->session->set_userdata(array($this->auth->session_name['name'] => $this->auth->session_data));

            // Hash database session token as it will be visible via cookie.
            $hash_session_token = $this->hash_cookie_token($session_token);

            // Create cookies to detect if user closes their browser (Defined by config file).
            if ($this->auth->auth_security['logout_user_onclose'])
            {
                set_cookie(array(
                    'name' => $this->auth->cookie_name['login_session_token'],
                    'value' => $hash_session_token,
                    'expire' => 0 // Set to 0 so it expires on browser close.
                ));
            }
            // Create a cookie to detect when a user has closed their browser since logging in via password (Defined by config file).
            // If the cookie is not set/valid, a users 'logged in via password' status will be unset.
            else if ($this->auth->auth_security['unset_password_status_onclose'])
            {
                set_cookie(array(
                    'name' => $this->auth->cookie_name['login_via_password_token'],
                    'value' => $hash_session_token,
                    'expire' => 0 // Set to 0 so it expires on browser close.
                ));
            }

            return TRUE;
        }
        return FALSE;
    }

    /**
     * Creates a set of 'Remember me' cookies and inserts a database row containing the cookie session data.
     *
     * @return bool
     * @author Rob Hussey
     * @author Ben Edmunds
     */
    private function remember_user($user_id) {
        if (!is_numeric($user_id)) {
            return FALSE;
        }

        // Use existing 'Remember me' series token if it exists.
        if (get_cookie($this->auth->cookie_name['remember_series'])) {
            $remember_series = get_cookie($this->auth->cookie_name['remember_series']);
        }
        else {
            $remember_series = $this->generate_token(40);
        }

        // Set new 'Remember me' unique token.
        $remember_token = $this->generate_token(40);

        // Hash the database session tokens with user-agent to help invalidate hijacked cookies used from different browser.
        $sql_insert = array(
            $this->auth->tbl_col_user_session['user_id'] => $user_id,
            $this->auth->tbl_col_user_session['series'] => $this->hash_cookie_token($remember_series),
            $this->auth->tbl_col_user_session['token'] => $this->hash_cookie_token($remember_token),
            $this->auth->tbl_col_user_session['date'] => $this->database_date_time()
        );

        $this->db->insert($this->auth->tbl_user_session, $sql_insert);

        ###+++++++++++++++++++++++++++++++++###

        // Cleanup and remove the now used 'Remember me' database session if they exist.
        if (get_cookie($this->auth->cookie_name['remember_series']) && get_cookie($this->auth->cookie_name['remember_token'])) {
            $sql_where = array(
                $this->auth->tbl_col_user_session['user_id'] => $user_id,
                $this->auth->tbl_col_user_session['series'] =>
                    $this->hash_cookie_token(get_cookie($this->auth->cookie_name['remember_series'])),
                $this->auth->tbl_col_user_session['token'] =>
                    $this->hash_cookie_token(get_cookie($this->auth->cookie_name['remember_token']))
            );

            $this->db->delete($this->auth->tbl_user_session, $sql_where);
        }

        ###+++++++++++++++++++++++++++++++++###

        // Set new 'Remember me' cookies.
        if ($this->db->affected_rows() > 0) {
            set_cookie(array(
                'name' => $this->auth->cookie_name['user_id'],
                'value' => $user_id,
                'expire' => $this->auth->auth_security['user_cookie_expire'],
            ));

            set_cookie(array(
                'name' => $this->auth->cookie_name['remember_series'],
                'value' => $remember_series,
                'expire' => $this->auth->auth_security['user_cookie_expire'],
            ));

            set_cookie(array(
                'name' => $this->auth->cookie_name['remember_token'],
                'value' => $remember_token,
                'expire' => $this->auth->auth_security['user_cookie_expire'],
            ));

            return TRUE;
        }

        // 'Remember me' has been unsuccessful, for security, remove any existing cookies and database sessions.
        $this->delete_database_login_session($user_id);

        return FALSE;
    }

    /**
     * Delete database login sessions and delete 'Remember me' cookies.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function delete_database_login_session($user_id, $all_sessions = TRUE) {
        if (!is_numeric($user_id))
        {
            return FALSE;
        }

        // Get 'Remember me' cookie values before they are deleted.
        $remember_token = get_cookie($this->auth->cookie_name['remember_token']);
        $remember_series = get_cookie($this->auth->cookie_name['remember_series']);

        // Delete 'Remember me' cookies if they exist.
        $this->delete_remember_me_cookies();

        ###+++++++++++++++++++++++++++++++++###

        // Check whether to delete all sessions for user on all browers they may be logged in on, or just this session.
        if (!$all_sessions && isset($remember_token))
        {
            // If deleting a login session not associated to a 'Remember me' cookie.
            if (!isset($remember_series))
            {
                $remember_series = '';
            }

            $sql_where = '('.
                $this->auth->tbl_col_user_session['user_id'].' = '.$this->db->escape($user_id).' AND '.
                $this->auth->tbl_col_user_session['series'].' = '.$this->db->escape($this->hash_cookie_token($remember_series)).' AND '.
                $this->auth->tbl_col_user_session['token'].' = '.$this->db->escape($this->hash_cookie_token($remember_token)).
                ')';
            $this->db->where($sql_where, NULL, FALSE);

            // Delete the login session token if it is set.
            if ($session_token = $this->auth->session_data[$this->auth->session_name['login_session_token']])
            {
                $sql_where = '('.
                    $this->auth->tbl_col_user_session['user_id'].' = '.$this->db->escape($user_id).' AND '.
                    $this->auth->tbl_col_user_session['token'].' = '.$this->db->escape($session_token).
                    ')';
                $this->db->or_where($sql_where, NULL, FALSE);
            }
        }
        else
        {
            $this->db->where($this->auth->tbl_col_user_session['user_id'], $user_id);
        }

        // Delete database session records.
        $this->db->delete($this->auth->tbl_user_session);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete any defined 'Remember me' cookies.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function delete_remember_me_cookies()
    {
        if (get_cookie($this->auth->cookie_name['user_id']))
        {
            delete_cookie($this->auth->cookie_name['user_id']);
        }
        if ($remember_series = get_cookie($this->auth->cookie_name['remember_series']))
        {
            delete_cookie($this->auth->cookie_name['remember_series']);
        }
        if ($remember_token = get_cookie($this->auth->cookie_name['remember_token']))
        {
            delete_cookie($this->auth->cookie_name['remember_token']);
        }

        return TRUE;
    }

    /**
     * Updates the main user account table with the last time a user logged in and their IP address.
     * The data type of the date can be formatted via the config file.
     *
     * @return bool
     * @author Rob Hussey
     * @author Ben Edmunds
     */
    public function update_last_login($user_id) {
        // Update user IP address and last login date.
        $login_data = array(
            $this->auth->tbl_col_user_account['ip_address'] => $this->input->ip_address(),
            $this->auth->tbl_col_user_account['last_login_date'] => $this->database_date_time()
        );

        $this->db->update($this->auth->tbl_user_account, $login_data, array($this->auth->tbl_col_user_account['id'] => $user_id));

        return $this->db->affected_rows() == 1;
    }

    /**
     * Returns a list of user groups matching the $sql_where condition.
     *
     * @return object
     * @author Rob Hussey
     */
    public function get_groups($sql_select = FALSE, $sql_where = FALSE) {
        // Set any custom defined SQL statements.
        $this->set_custom_sql_to_db($sql_select, $sql_where);

        return $this->db->get($this->auth->tbl_user_group);
    }

    /**
     * Used internally by auth to call any custom user defined SQL Active Record functions at the correct point during a function.
     *
     * @return null
     * @author Rob Hussey
     */
    public function set_custom_sql_to_db($sql_select = FALSE, $sql_where = FALSE)
    {
        // Set directly submitted SELECT and WHERE clauses.
        if (!empty($sql_select)) {
            $this->db->select($sql_select);
        }

        if (!empty($sql_where)) {
            $this->db->where($sql_where);
        }

        ### ++++++++++++++++++++ ###

        // Set SQL clauses defined via auth SQL Active Record functions.

        // Set array of all SQL clause types.
        $clause_types = array(
            'select', 'where', 'or_where', 'where_in', 'or_where_in', 'where_not_in', 'or_where_not_in',
            'like', 'or_like', 'not_like', 'or_not_like', 'join', 'order_by', 'group_by', 'limit'
        );

        // Loop through clause types.
        foreach($clause_types as $sql_clause) {
            // If a clause is set.
            if (! empty($this->auth->$sql_clause))
            {
                // Loop through the clause array setting values using active record.
                foreach($this->auth->$sql_clause as $value)
                {
                    // Key, value and parameter method.
                    if (is_array($value) && key($value) === 'key_value_param_method')
                    {
                        $data = current($value);
                        $this->db->$sql_clause($data['key'], $data['value'], $data['param']);
                    }
                    // Key and value method.
                    else if (is_array($value) && key($value) === 'key_value_method')
                    {
                        $data = current($value);
                        $this->db->$sql_clause($data['key'], $data['value']);
                    }
                    // String or Associative array method.
                    else
                    {
                        $this->db->$sql_clause($value);
                    }
                }
            }
        }
    }

    /**
     * Validate the submitted login details and attempt to log the user into their account.
     */
    function login() {
        $this->load->library('form_validation');

        // Set validation rules.
        $this->form_validation->set_rules('login_identity', 'Identity (Email / Login)', 'required');
        $this->form_validation->set_rules('login_password', 'Password', 'required');

        // If failed login attempts from users IP exceeds limit defined by config file, validate captcha.
        if ($this->ez_auth->ip_login_attempts_exceeded())
        {
            /**
             * reCAPTCHA
             * http://www.google.com/recaptcha
             * To activate reCAPTCHA, ensure the 'recaptcha_response_field' validation below is uncommented and then comment out the 'login_captcha' validation further below.
             *
             * The custom validation rule 'validate_recaptcha' can be found in '../libaries/MY_Form_validation.php'.
             * The form field name used by 'reCAPTCHA' is 'recaptcha_response_field', this field name IS NOT editable.
             *
             * Note: To use this example, you will also need to enable the recaptcha examples in 'controllers/auth.php', and 'views/demo/login_view.php'.
             */
            $this->form_validation->set_rules('recaptcha_response_field', 'Captcha Answer', 'required|validate_recaptcha');

            /**
             * flexi auths math CAPTCHA
             * Math CAPTCHA is a basic CAPTCHA style feature that asks users a basic maths based question to validate they are indeed not a bot.
             * To activate Math CAPTCHA, ensure the 'login_captcha' validation below is uncommented and then comment out the 'recaptcha_response_field' validation above.
             *
             * The field value submitted as the answer to the math captcha must be submitted to the 'validate_math_captcha' validation function.
             * The custom validation rule 'validate_math_captcha' can be found in '../libaries/MY_Form_validation.php'.
             *
             * Note: To use this example, you will also need to enable the math_captcha examples in 'controllers/auth.php', and 'views/demo/login_view.php'.
             */
            # $this->form_validation->set_rules('login_captcha', 'Captcha Answer', 'required|validate_math_captcha['.$this->input->post('login_captcha').']');
        }

        // Run the validation.
        if ($this->form_validation->run())
        {
            // Check if user wants the 'Remember me' feature enabled.
            $remember_user = ($this->input->post('remember_me') == 1);
            $remember_user = 1;

            // Verify login data.
            $this->ez_auth->login($this->input->post('login_identity'), $this->input->post('login_password'), $remember_user);

            // Save any public status or error messages (Whilst suppressing any admin messages) to CI's flash session data.
            $this->session->set_flashdata('message', $this->ez_auth->get_messages());

            // Reload page, if login was successful, sessions will have been created that will then further redirect verified users.
            redirect();
        }
        else
        {
            // Set validation errors.
            $this->data['message'] = validation_errors('<p class="error_msg">', '</p>');

            return FALSE;
        }
    }

    /**
     * login
     * Verifies a users identity and password, if valid, they are logged in.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function perform_login($identity = FALSE, $password = FALSE, $remember_user = FALSE)
    {
        if (empty($identity) || empty($password) || (!$identity = $this->get_primary_identity($identity)))
        {
            return FALSE;
        }

        // Check if login attempts are being counted.
        if ($this->auth->auth_security['login_attempt_limit'] > 0)
        {
            // Check user has not exceeded login attempts.
            if ($this->login_attempts_exceeded($identity))
            {
                $this->set_error_message('login_attempts_exceeded', 'config');
                return FALSE;
            }
        }

        $sql_select = array(
            $this->auth->primary_identity_col,
            $this->auth->tbl_col_user_account['id'],
            $this->auth->tbl_col_user_account['password'],
            $this->auth->tbl_col_user_account['group_id'],
            $this->auth->tbl_col_user_account['activation_token'],
            $this->auth->tbl_col_user_account['active'],
            $this->auth->tbl_col_user_account['suspend'],
            $this->auth->tbl_col_user_account['last_login_date'],
            $this->auth->tbl_col_user_account['failed_logins']
        );

        $sql_where = array($this->auth->primary_identity_col => $identity);

        // Set any custom defined SQL statements.
        $this->auth_model->set_custom_sql_to_db();

        $query = $this->db->select($sql_select)
            ->where($sql_where)
            ->get($this->auth->tbl_user_account);

        ###+++++++++++++++++++++++++++++++++###

        // User exists, now validate credentials.
        if ($query->num_rows() == 1)
        {
            $user = $query->row();

            // If an activation time limit is defined by config file and account hasn't been activated by email.
            if ($this->auth->auth_settings['account_activation_time_limit'] > 0 &&
                !empty($user->{$this->auth->database_config['user_acc']['columns']['activation_token']}))
            {
                if (!$this->validate_activation_time_limit($user->{$this->auth->database_config['user_acc']['columns']['last_login_date']}))
                {
                    $this->set_error_message('account_requires_activation', 'config');
                    return FALSE;
                }
            }

            // Check if account has been suspended.
            if ($user->{$this->auth->database_config['user_acc']['columns']['suspend']} == 1)
            {
                $this->set_error_message('account_suspended', 'config');
                return FALSE;
            }

            // Verify submitted password matches database.
            if ($this->verify_password($identity, $password))
            {
                // Reset failed login attempts.
                if ($user->{$this->auth->database_config['user_acc']['columns']['failed_logins']} > 0) {
                    $this->reset_login_attempts($identity);
                }

                // Set user login sessions.
                if ($this->set_login_sessions($user, TRUE)) {
                    // Set 'Remember me' cookie and database record if checked by user.
                    if ($remember_user) {
                        $this->remember_user($user->{$this->auth->database_config['user_acc']['columns']['id']});
                    }
                    // Else, ensure any existing 'Remember me' cookies are deleted.
                    // This can occur if the user logs in via password, whilst already logged in via a "Remember me" cookie.
                    else {
                        $this->delete_remember_me_cookies();
                    }
                    return TRUE;
                }
            }
            // Password does not match, log the failed login attempt if defined via the config file.
            else if ($this->auth->auth_security['login_attempt_limit'] > 0)
            {
                $attempts = $user->{$this->auth->database_config['user_acc']['columns']['failed_logins']};

                // Increment failed login attempts.
                $this->increment_login_attempts($identity, $attempts);
            }
        }

        return FALSE;
    }

    /**
     * This function is called when a user successfully logs in, it's used to remove any previously logged failed login attempts.
     * This prevents a user accumulating a login time ban for every failed attempt they make.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function reset_login_attempts($identity) {
        if (empty($identity)) {
            return FALSE;
        }

        $login_data = array(
            $this->auth->tbl_col_user_account['failed_login_ip'] => '',
            $this->auth->tbl_col_user_account['failed_logins'] => 0,
            $this->auth->tbl_col_user_account['failed_login_ban_date'] => 0
        );

        $this->db->update($this->auth->tbl_user_account, $login_data, array($this->auth->primary_identity_col => $identity));

        return $this->db->affected_rows() == 1;
    }

    /**
     * This function is called to log details of when a user has failed a login attempt.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function increment_login_attempts($identity, $attempts) {
        if (empty($identity) || !is_numeric($attempts)) {
            return FALSE;
        }

        $attempts++;
        $time_ban = 0;

        // Length of time ban in seconds.
        if ($attempts >= $this->auth->auth_security['login_attempt_limit']) {
            // Set time ban message.
            $this->set_error_message('login_attempts_exceeded', 'config');

            $time_ban = $this->auth->auth_security['login_attempt_time_ban'];

            // If failed attempts continue for more than 3 times the limit, increase the time ban by a factor of 2.
            if ($attempts >= ($this->auth->auth_security['login_attempt_limit'] * 3)) {
                $time_ban = ($time_ban * 2);
            }

            // Set time ban as a date.
            $time_ban = $this->database_date_time($time_ban);
        }

        // Record users ip address to compare future login attempts.
        $login_data = array(
            $this->auth->tbl_col_user_account['failed_login_ip'] => $this->input->ip_address(),
            $this->auth->tbl_col_user_account['failed_logins'] => $attempts,
            $this->auth->tbl_col_user_account['failed_login_ban_date'] => $time_ban
        );

        $this->db->update($this->auth->tbl_user_account, $login_data, array($this->auth->primary_identity_col => $identity));

        return $this->db->affected_rows() == 1;
    }

    /**
     * Looks-up database identity columns and return users primary identifier.
     *
     * @return string
     * @author Rob Hussey
     */
    public function get_primary_identity($identity) {
        if (empty($identity) || !is_string($identity))
        {
            return FALSE;
        }

        $identity_cols = $this->auth->db_settings['identity_cols'];

        // Loop through database identity columns.
        for ($i = 0; count($identity_cols) > $i; $i++) {
            $this->db->or_where($identity_cols[$i], $identity);
        }

        $query = $this->db->select($this->auth->primary_identity_col)
            ->get($this->auth->tbl_user_account);

        // Return users primary identity.
        if ($query->num_rows() == 1) {
            return $query->row()->{$this->auth->db_settings['primary_identity_col']};
        }
        return FALSE;
    }

    private function login_attempts_exceeded($identity) {
        if (empty($identity)) {
            return TRUE;
        }

        $sql_select = array(
            $this->auth->tbl_col_user_account['failed_logins'],
            $this->auth->tbl_col_user_account['failed_login_ban_date']
        );

        $query = $this->db->select($sql_select)
            ->where($this->auth->primary_identity_col, $identity)
            ->get($this->auth->tbl_user_account);

        if ($query->num_rows() == 1) {
            $user = $query->row();

            $attempts = $user->{$this->auth->database_config['user_acc']['columns']['failed_logins']};
            $failed_login_date = $user->{$this->auth->database_config['user_acc']['columns']['failed_login_ban_date']};

            // Check if login attempts are acceptable.
            if ($attempts < $this->auth->auth_security['login_attempt_limit']) {
                return FALSE;
            }
            // Login attempts exceed limit, now check if user has waited beyond time ban limit to attempt another login.
            else if ($this->database_date_time($this->auth->auth_security['login_attempt_time_ban'], $failed_login_date, TRUE)
                < $this->database_date_time(FALSE, FALSE, TRUE))
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Generates the html for Google reCAPTCHA.
     * Note: If the reCAPTCHA is located on an SSL secured page (https), set $ssl = TRUE.
     *
     * @return string
     * @author Rob Hussey
     */
    public function recaptcha($ssl = FALSE) {
        $this->load->helper('recaptcha');

        // Get config settings.
        $captcha_theme = $this->auth->auth_security['recaptcha_theme'];
        $captcha_lang = $this->auth->auth_security['recaptcha_language'];

        // Set defaults.
        $theme = "theme:'clean',";
        $language = "lang:'en'";

        // Set reCAPTCHA theme.
        if (!empty($captcha_theme))
        {
            if ($captcha_theme == 'custom')
            {
                $theme = "theme:'custom', custom_theme_widget:'recaptcha_widget',";
            }
            else
            {
                $theme = "theme:'".$captcha_theme."',";
            }
        }

        // Set reCAPTCHA language.
        if (!empty($captcha_lang))
        {
            $language = "lang:'".$captcha_lang."'";
        }

        $theme_html = "<script>var RecaptchaOptions = { $theme $language };</script>\n";
        $captcha_html = recaptcha_get_html($this->auth->auth_security['recaptcha_public_key'], NULL, $ssl);

        return $theme_html.$captcha_html;
    }

    /**
     * Validates if a Google reCAPTCHA answer submitted via http POST data is correct.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function validate_recaptcha() {
        $this->load->helper('recaptcha');

        $response = recaptcha_check_answer(
            $this->auth->auth_security['recaptcha_private_key'],
            $this->input->ip_address(),
            $this->input->post('recaptcha_challenge_field'),
            $this->input->post('recaptcha_response_field')
        );

        return $response->is_valid;
    }

    /**
     * Validates whether the number of failed login attempts from a unique IP address has exceeded a defined limit.
     * The function can be used in conjunction with showing a Captcha for users repeatedly failing login attempts.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function ip_login_attempts_exceeded()
    {
        // Compare users IP address against any failed login IP addresses.
        $sql_where = array(
            $this->auth->tbl_col_user_account['failed_login_ip'] => $this->input->ip_address(),
            $this->auth->tbl_col_user_account['failed_logins'].' >= ' => $this->auth->auth_security['login_attempt_limit']
        );

        $query = $this->db->where($sql_where)
            ->get($this->auth->tbl_user_account);

        return $query->num_rows() > 0;
    }


    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // TOKEN GENERATION
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Generates a random unhashed password / token / salt.
     * Includes a safe guard to ensure vowels are removed to avoid offensive words when used for password generation.
     * Additionally, 0, 1 removed to avoid confusion with o, i, l.
     *
     * @return string
     */
    public function generate_token($length = 8) {
        $characters = '23456789BbCcDdFfGgHhJjKkMmNnPpQqRrSsTtVvWwXxYyZz';
        $count = mb_strlen($characters);

        for ($i = 0, $token = ''; $i < $length; $i++)
        {
            $index = rand(0, $count - 1);
            $token .= mb_substr($characters, $index, 1);
        }
        return $token;
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Generates a new hashed password / token.
     *
     * @return string
     * @author Rob Hussey
     */
    public function generate_hash_token($token, $database_salt = FALSE, $is_password = FALSE) {
        if (empty($token))
        {
            return FALSE;
        }

        // Get static salt if set via config file.
        $static_salt = $this->auth->auth_security['static_salt'];

        if ($is_password)
        {
            require_once(APPPATH.'libraries/phpass/PasswordHash.php');
            $phpass = new PasswordHash(8, FALSE);

            return $phpass->HashPassword($database_salt . $token . $static_salt);
        }
        else
        {
            return sha1($database_salt . $token . $static_salt);
        }
    }

    /**
     * Regenerate CodeIgniters session id like native PHP session_regenerate_id(TRUE), used whenever a users permissions change.
     *
     * @return bool
     * @author Rob Hussey
     */
    private function regenerate_ci_session_id() {
        // This is targeting a native CodeIgniter cookie, not an auth cookie.
        $ci_session = array(
            'name'   => $this->config->item('sess_cookie_name'),
            'value'  => '',
            'expire' => ''
        );
        set_cookie($ci_session);
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // Account Registration
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * register_account
     * Create a new user account.
     * Then if defined via the '$instant_activate' var, automatically log the user into their account.
     */
    function register_account()
    {
        $this->load->library('form_validation');

        // Set validation rules.
        // The custom rules 'identity_available' and 'validate_password' can be found in '../libaries/MY_Form_validation.php'.
        $validation_rules = array(
            array('field' => 'register_email_address', 'label' => 'Email Address', 'rules' => 'required|valid_email|identity_available'),
        );

        $this->form_validation->set_rules($validation_rules);

        // Run the validation.
        if ($this->form_validation->run())
        {
            // Get user login details from input.
            $email = $this->input->post('register_email_address');
            $password = substr(str_shuffle(MD5(microtime())), 0, 10);

            // Set whether to instantly activate account.
            // This var will be used twice, once for registration, then to check if to log the user in after registration.
            $instant_activate = FALSE;

            // The last 2 variables on the register function are optional, these variables allow you to:
            // #1. Specify the group ID for the user to be added to (i.e. 'Moderator' / 'Public'), the default is set via the config file.
            // #2. Set whether to automatically activate the account upon registration, default is FALSE.
            // Note: An account activation email will be automatically sent if auto activate is FALSE, or if an activation time limit is set by the config file.
            $response = $this->ez_auth->insert_user($email, $password, 1, $instant_activate);

            if ($response)
            {
                // This is an example 'Welcome' email that could be sent to a new user upon registration.
                // Bear in mind, if registration has been set to require the user activates their account, they will already be receiving an activation email.
                // Therefore sending an additional email welcoming the user may be deemed unnecessary.
                $email_data = array('identity' => $email, 'password' => $password);
                $this->ez_auth->send_email($email, 'Welcome', 'registration_welcome.tpl.php', $email_data);
                // Note: The 'registration_welcome.tpl.php' template file is located in the '../views/includes/email/' directory defined by the config file.

                ###+++++++++++++++++###

                // Save any public status or error messages (Whilst suppressing any admin messages) to CI's flash session data.
                // This is an example of how to log the user into their account immeadiately after registering.
                // This example would only be used if users do not have to authenticate their account via email upon registration.
                if ($this->ez_auth->login($email, $password))
                {
                    // Redirect user to mai page.
                    redirect();
                }

                // Redirect user to login page
                redirect();
            }
        }

        // Set validation errors.
        $this->data['message'] = validation_errors('<p class="error_msg">', '</p>');
        echo "Form validation failed<br />";

        return FALSE;
    }

    public function send_email($email_to = NULL, $email_title = NULL, $data = NULL, $template = NULL) {
        if (empty($email_to) || empty($data) || empty($template)) {
            return FALSE;
        }

        // Merge any additional template data that has been set via the template_data() function, (Must be called prior to parent function).
        if (!empty($this->auth->template_data['template_data'])) {
            $data = array_merge($data, (array)$this->auth->template_data['template_data']);
        }

        // Overwrite default template file to send email via template_data() function (Must be called prior to parent function).
        if (!empty($this->auth->template_data['template'])) {
            $template = $this->auth->template_data['template'];
        }

        $message = $this->load->view($template, $data, TRUE);

        $this->load->library('email');
        $this->email->clear();
        $this->email->initialize(array('mailtype' => $this->auth->email_settings['email_type']));
        $this->email->set_newline("\r\n");
        $this->email->from($this->auth->email_settings['reply_email'], $this->auth->email_settings['site_title']);
        $this->email->to($email_to);
        $this->email->subject($this->auth->email_settings['site_title'] ." ". $email_title);
        $this->email->message($message);

        return $this->email->send();
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // USER MANAGEMENT / CRUD METHODS
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * insert_user
     * Inserts user account and profile data, returning the users new id.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function insert_user($email, $password, $group_id = FALSE) {
        // Check that an email address and password have been set.
        // If a username is defined as an identity column, ensure it is also set.
        if (empty($email) || empty($password)) {
            $this->set_error_message('account_creation_insufficient_data', 'config');
            return FALSE;
        }

        // Check email is unique.
        if (!$this->identity_available($email)) {
            $this->set_error_message('account_creation_duplicate_email', 'config');
            return FALSE;
        }

        ###+++++++++++++++++++++++++++++++++###

        if (!is_numeric($group_id)) {
            $group_id = $this->auth->auth_settings['default_group_id'];
        }

        $ip_address = $this->input->ip_address();

        $store_database_salt = $this->auth->auth_security['store_database_salt'];
        $database_salt = $store_database_salt ? $this->generate_token($this->auth->auth_security['database_salt_length']) : FALSE;

        $hash_password = $this->generate_hash_token($password, $database_salt, TRUE);
        $activation_token = sha1($this->generate_token(20));
        $suspend_account = ($this->auth->auth_settings['suspend_new_accounts']) ? 1 : 0;

        ###+++++++++++++++++++++++++++++++++###

        // Start SQL transaction.
        $this->db->trans_start();

        // Main user account table.
        $sql_insert = array(
            $this->auth->tbl_col_user_account['group_id'] => $group_id,
            $this->auth->tbl_col_user_account['email'] => $email,
            $this->auth->tbl_col_user_account['password'] => $hash_password,
            $this->auth->tbl_col_user_account['ip_address'] => $ip_address,
            $this->auth->tbl_col_user_account['last_login_date'] => $this->database_date_time(),
            $this->auth->tbl_col_user_account['date_added'] => $this->database_date_time(),
            $this->auth->tbl_col_user_account['activation_token'] => $activation_token,
            $this->auth->tbl_col_user_account['active'] => 0,
            $this->auth->tbl_col_user_account['suspend'] => $suspend_account
        );

        if ($store_database_salt)
        {
            $sql_insert[$this->auth->tbl_col_user_account['salt']] = $database_salt;
        }

        // Create main user account.
        $this->db->insert($this->auth->tbl_user_account, $sql_insert);

        $user_id = $this->db->insert_id();

        // Complete SQL transaction.
        $this->db->trans_complete();

        return is_numeric($user_id) ? $user_id : FALSE;
    }

    /**
     * Activates a users account allowing them to login to their account.
     * If $verify_token = TRUE, a valid $activation_token must also be submitted.
     *
     * @return void
     * @author Rob Hussey
     * @author Mathew Davies
     */
    public function activate_user($user_id, $activation_token = FALSE, $verify_token = TRUE, $clear_token = TRUE) {
        if ($activation_token) {
            // Confirm activation token is 40 characters long (length of sha1).
            if ($verify_token && strlen($activation_token) != 40) {
                return FALSE;
            }
            // Verify that $activation_token matches database record.
            else if ($verify_token && strlen($activation_token) == 40) {
                $sql_where = array(
                    $this->auth->tbl_col_user_account['id'] => $user_id,
                    $this->auth->tbl_col_user_account['activation_token'] => $activation_token
                );

                $query = $this->db->where($sql_where)
                    ->get($this->auth->tbl_user_account);

                if ($query->num_rows() !== 1)
                {
                    return FALSE;
                }
            }
        }

        if ($clear_token) {
            $sql_update[$this->auth->tbl_col_user_account['activation_token']] = '';
        }
        $sql_update[$this->auth->tbl_col_user_account['active']] = 1;

        $this->db->update($this->auth->tbl_user_account, $sql_update, array($this->auth->tbl_col_user_account['id'] => $user_id));

        return $this->db->affected_rows() == 1;
    }

    /**
     * Resends a new account activation token to a users email address.
     */
    function resend_activation_token() {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('activation_token_identity', 'Identity (Email / Login)', 'required');

        // Run the validation.
        if ($this->form_validation->run())
        {
            // Verify identity and resend activation token.
            $response = $this->ez_auth->resend_activation_token($this->input->post('activation_token_identity'));

            // Save any public status or error messages (Whilst suppressing any admin messages) to CI's flash session data.
            $this->session->set_flashdata('message', $this->ez_auth->get_messages());

            // Redirect user.
            ($response) ? redirect('auth') : redirect('auth/resend_activation_token');
        }
        else
        {
            // Set validation errors.
            $this->data['message'] = validation_errors('<p class="error_msg">', '</p>');

            return FALSE;
        }
    }

    public function deactivate_user($user_id) {
        if (empty($user_id))
        {
            return FALSE;
        }

        $activation_token = sha1($this->generate_token(20));

        $sql_update = array(
            $this->auth->tbl_col_user_account['activation_token'] => $activation_token,
            $this->auth->tbl_col_user_account['active'] => 0
        );

        $this->db->update($this->auth->tbl_user_account, $sql_update, array($this->auth->tbl_col_user_account['id'] => $user_id));

        return $this->db->affected_rows() == 1;
    }

    /**
     * Allows strings or arrays to pass SQL SELECT, WHERE and GROUP BY statements. Defaults to return all.
     *
     * @return object
     * @author Rob Hussey
     */
    public function get_users($sql_select = FALSE, $sql_where = FALSE, $sql_group_by = TRUE) {
        // Left Join user group table to user account table.
        $this->db->join(
            $this->auth->tbl_user_group,
            $this->auth->tbl_col_user_account['group_id'].' = '.$this->auth->tbl_join_user_group, 'left'
        );

        // Left Join user custom data table(s) to user account table.
        foreach ($this->auth->tbl_custom_data as $table) {
            $this->db->join($table['table'], $this->auth->tbl_join_user_account.' = '.$table['join'], 'left');
        }

        // Group by users id to prevent multiple custom data rows to be listed per user.
        if ($sql_group_by === TRUE) {
            $this->db->group_by($this->auth->tbl_col_user_account['id']);
        }
        // Else, if a specific column is defined, group by that column.
        else if ($sql_group_by) {
            $this->db->group_by($sql_group_by);
        }

        // Set any custom defined SQL statements.
        $this->set_custom_sql_to_db($sql_select, $sql_where);

        return $this->db->get($this->auth->tbl_user_account);
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // CHECK USER IDENTITIES
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Check identity does not exist in any of the databases identifier columns (Username or Email) set via the config file.
     * The function also checks that values from different identity columns do not match each other.
     * Example: If user #1's EMAIL is 'x@y.com' and user #2's USERNAME is 'x@y.com', neither would be able to login to their account.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function identity_available($identity = FALSE, $user_id = FALSE)
    {
        if (empty($identity))
        {
            return FALSE;
        }

        // Try and get the $user_id from the users current session if not passed to function.
        if (!is_numeric($user_id) && $this->auth->session_data[$this->auth->session_name['user_id']])
        {
            $user_id = $this->auth->session_data[$this->auth->session_name['user_id']];
        }

        // If $user_id is set, remove user from query so their current identity values are not found during the duplicate identity check.
        if (is_numeric($user_id))
        {
            $this->db->where($this->auth->tbl_col_user_account['id'].' != ',$user_id);
        }

        // Get identity columns.
        $identity_cols = $this->auth->db_settings['identity_cols'];

        // Loop through identity columns to try and find any duplicates in any of the columns.
        $sql_where = '(';
        for ($i = 0; count($identity_cols) > $i; $i++) {
            $sql_where .= $identity_cols[$i].' = '.$this->db->escape($identity).' OR ';
        }
        $sql_where = rtrim($sql_where,' OR ').')';

        $this->db->where($sql_where, NULL, FALSE);

        return $this->db->count_all_results($this->auth->tbl_user_account) == 0;
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Check email does not exist in database.
     * NOTE: This should not be used if the email field is defined in the 'identity_cols' set via the config file.
     * In this case, use the identity_available() function instead.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function email_available($email = FALSE, $user_id = FALSE) {
        if (empty($email))
        {
            return FALSE;
        }

        // Try and get the $user_id from the users current session if not passed to function.
        if (!is_numeric($user_id) && $this->auth->session_data[$this->auth->session_name['user_id']])
        {
            $user_id = $this->auth->session_data[$this->auth->session_name['user_id']];
        }

        // If $user_id is set, remove user from query so their current email is not found during the duplicate email check.
        if (is_numeric($user_id))
        {
            $this->db->where($this->auth->tbl_col_user_account['id'].' != ',$user_id);
        }

        return $this->db->where($this->auth->tbl_col_user_account['email'], $email)
            ->count_all_results($this->auth->tbl_user_account) == 0;
    }

    /**
     * Validates a submitted 'Current' password against the database for a specific user.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function validate_current_password($current_password, $identity) {
        return ($this->CI->auth_model->verify_password($identity, $current_password));
    }

    /**
     * Verify that a submitted password matches a user database record.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function verify_password($identity, $verify_password) {
        if (empty($identity) || empty($verify_password))
        {
            return FALSE;
        }

        $sql_select = array(
            $this->auth->tbl_col_user_account['password'],
            $this->auth->tbl_col_user_account['salt']
        );

        $query = $this->db->select($sql_select)
            ->where($this->auth->primary_identity_col, $identity)
            ->limit(1)
            ->get($this->auth->tbl_user_account);

        $result = $query->row();

        if ($query->num_rows() !== 1) {
            return FALSE;
        }

        $database_password = $result->{$this->auth->database_config['user_acc']['columns']['password']};
        $database_salt = $result->{$this->auth->database_config['user_acc']['columns']['salt']};
        $static_salt = $this->auth->auth_security['static_salt'];

        require_once(APPPATH.'libraries/phpass/PasswordHash.php');
        $hash_token = new PasswordHash(8, FALSE);

        return $hash_token->CheckPassword($database_salt . $verify_password . $static_salt, $database_password);
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // MESSAGES AND ERRORS
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Set a status or error message to be displayed.
     *
     * @return void
     * @author Rob Hussey
     */
    private function set_message($message_type = FALSE, $message = FALSE, $target_user = 'public', $overwrite_existing = FALSE) {
        if (in_array($message_type, array('status', 'error')) && $message)
        {
            // Convert the target user to lowercase to ensure whether comparison values are matched.
            $target_user = strtolower($target_user);

            // Check whether to use the target user set via the config file.
            if ($target_user === 'config' && isset($this->auth->message_settings['target_user'][$message]))
            {
                $target_user = $this->auth->message_settings['target_user'][$message];
            }

            // If $target_user exactly equals TRUE, set the target user as public.
            $target_user = ($target_user === TRUE) ? 'public' : $target_user;

            // Check whether a message should be set, if FALSE is defined, do not set the message.
            if (in_array($target_user, array('public', 'admin')))
            {
                $message_alias = ($message_type == 'status') ? 'status_messages' : 'error_messages';

                // Check whether to overwrite existing messages.
                if ($overwrite_existing)
                {
                    $this->auth->{$message_alias} = array('public' => array(), 'admin' => array());
                }

                // Check message is not already in array to avoid displaying duplicates.
                if (! in_array($message, $this->auth->{$message_alias}[$target_user]))
                {
                    $this->auth->{$message_alias}[$target_user][] = $message;
                }
            }
        }

        return $message;
    }

    /**
     * Set a status message to be displayed.
     *
     * @return void
     * @author Rob Hussey
     */
    public function set_status_message($status_message = FALSE, $target_user = 'public', $overwrite_existing = FALSE)
    {
        return $this->set_message('status', $status_message, $target_user, $overwrite_existing);
    }

    /**
     * Set an error message to be displayed.
     *
     * @return void
     * @author Rob Hussey
     */
    public function set_error_message($error_message = FALSE, $target_user = 'public', $overwrite_existing = FALSE)
    {
        return $this->set_message('error', $error_message, $target_user, $overwrite_existing);
    }

    ###+++++++++++++++++++++++++++++++++###

    /**
     * Get any status or error message(s) that may have been set by recently run functions.
     */
    private function get_messages($message_type = FALSE, $target_user = 'public', $prefix_delimiter = FALSE, $suffix_delimiter = FALSE)
    {
        if (in_array($message_type, array('status', 'error')))
        {
            // If $target_user exactly equals TRUE, set the target user as public.
            $target_user = ($target_user === TRUE) ? 'public' : $target_user;

            // Convert the target user to lowercase to ensure whether comparison values are matched.
            $target_user = strtolower($target_user);

            // Set message delimiters, by checking they do not exactly equal FALSE, we can allow NULL or empty '' delimiter values.
            if (! $prefix_delimiter)
            {
                $prefix_delimiter = ($message_type == 'status') ?
                    $this->auth->message_settings['delimiters']['status_prefix'] : $this->auth->message_settings['delimiters']['error_prefix'];
            }
            if (! $suffix_delimiter)
            {
                $suffix_delimiter = ($message_type == 'status') ?
                    $this->auth->message_settings['delimiters']['status_suffix'] : $this->auth->message_settings['delimiters']['error_suffix'];
            }

            $message_alias = ($message_type == 'status') ? 'status_messages' : 'error_messages';

            // Get all messages for public users, or both public AND admin users.
            if ($target_user === 'public')
            {
                $messages = $this->auth->{$message_alias}['public'];
            }
            else
            {
                $messages = array_merge($this->auth->{$message_alias}['public'], $this->auth->{$message_alias}['admin']);
            }

            $statuses = FALSE;
            foreach ($messages as $message)
            {
                $message = ($this->lang->line($message)) ? $this->lang->line($message) : $message;
                $statuses .= $prefix_delimiter . $message . $suffix_delimiter;
            }

            return $statuses;
        }

        return FALSE;
    }

    /**
     * Get any status message(s) that may have been set by recently run functions.
     *
     * @return void
     * @author Rob Hussey
     */
    public function status_messages($target_user = 'public', $prefix_delimiter = FALSE, $suffix_delimiter = FALSE)
    {
        return $this->get_messages('status', $target_user, $prefix_delimiter, $suffix_delimiter);
    }

    /**
     * Get any error message(s) that may have been set by recently run functions.
     *
     * @return void
     * @author Rob Hussey
     */
    public function error_messages($target_user = 'public', $prefix_delimiter = FALSE, $suffix_delimiter = FALSE)
    {
        return $this->get_messages('error', $target_user, $prefix_delimiter, $suffix_delimiter);
    }
}