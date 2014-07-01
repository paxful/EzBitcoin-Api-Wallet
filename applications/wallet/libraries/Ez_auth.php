<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ez_auth {

    public function __construct() {

        $this->CI =& get_instance();

        $this->CI->load->model('auth_model');

        ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
        // CHECK LOGIN CREDENTIALS ON LOAD
        ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

        // Validate login credentials on every page load if set via config file.
        if ($this->is_logged_in() && $this->CI->auth->auth_security['validate_login_onload']  && !isset($this->CI->auth_model->auth_verified))
        {
            $this->CI->auth_model->validate_database_login_session();
        }
        // Auto log in the user if they have 'Remember me' cookies.
        else if (!$this->is_logged_in() && get_cookie($this->CI->auth->cookie_name['user_id']) &&
            get_cookie($this->CI->auth->cookie_name['remember_series']) && get_cookie($this->CI->auth->cookie_name['remember_token']))
        {
            $this->CI->auth_model->login_remembered_user();
        }
    }

    public function __call($method, $arguments)
    {
        $extension_types = array('_num_rows', '_row_array', '_array', '_result', '_row');
        $method_substr = str_replace(array_values($extension_types), FALSE, $method);
        $method_substr_query = $method_substr.'_query';
        $method_substr_extension = str_replace($method_substr, FALSE, $method);

        // Get flexi auth class name.
        $libraries = array('ezauth');
        foreach($libraries as $library)
        {
            if (isset($this->CI->$library))
            {
                if (method_exists($this->CI->$library, $method_substr_query))
                {
                    $target_library = $library;
                    break;
                }
            }
        }

        if (isset($target_library))
        {
            // Pass the first 5 submitted arguments to the function (Usually the SQL SELECT and WHERE statements).
            // Note: The search_users() function requires the 4th and 5th arguments.
            $argument_1 = (isset($arguments[0])) ? $arguments[0] : FALSE; // Usually $sql_select
            $argument_2 = (isset($arguments[1])) ? $arguments[1] : FALSE; // Usually $sql_where
            $argument_3 = (isset($arguments[2])) ? $arguments[2] : FALSE; // Other
            $argument_4 = (isset($arguments[3])) ? $arguments[3] : FALSE; // Other
            $argument_5 = (isset($arguments[4])) ? $arguments[4] : FALSE; // Other
            $data = $this->CI->$target_library->$method_substr_query($argument_1, $argument_2, $argument_3, $argument_4, $argument_5);

            if (! empty($data))
            {
                if ($method_substr_extension == '_result')
                {
                    return $data->result();
                }
                else if ($method_substr_extension == '_row')
                {
                    return $data->row();
                }
                else if ($method_substr_extension == '_array')
                {
                    return $data->result_array();
                }
                else if ($method_substr_extension == '_row_array')
                {
                    return $data->row_array();
                }
                else if ($method_substr_extension == '_num_rows')
                {
                    return $data->num_rows();
                }
                else // '_query'
                {
                    return $data;
                }
            }
        }

        echo 'Call to an unknown method : "'.$method.'"';
        return FALSE;
    }

    /**
     * Verifies a users identity and password, if valid, they are logged in.
     *
     * @return void
     * @author Mathew Davies
     */
    public function login($identity = FALSE, $password = FALSE, $remember_user = TRUE) {
        if ($this->CI->auth_model->perform_login($identity, $password, $remember_user)) {
            $this->CI->auth_model->set_status_message('login_successful', 'config');
            return TRUE;
        }

        // If no specific error message has been set, set a generic error.
        if (! $this->CI->auth_model->error_messages()) {
            $this->CI->auth_model->set_error_message('login_unsuccessful', 'config');
        }

        return FALSE;
    }

    /**
     * Logs a user out of their account.
     * Note: The $all_sessions variable allows you to define whether to delete all database sessions or just the current session.
     * When set to FALSE, this can be used to logout a user off of one computer (Internet Cafe) but not another (Home).
     *
     * @return bool
     * @author Rob Hussey
     */
    public function logout($all_sessions = TRUE)
    {
        $this->CI->auth_model->logout($all_sessions);

        $this->CI->auth_model->set_status_message('logout_successful', 'config');

        return TRUE;
    }

    public function is_logged_in() {
        return (bool) $this->CI->auth->session_data[$this->CI->auth->session_name['user_identifier']];
    }

    /**
     * Validates whether the number of failed login attempts from a unique IP address has exceeded a defined limit.
     * The function can be used in conjunction with showing a Captcha for users repeatedly failing login attempts.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function ip_login_attempts_exceeded() {
        return $this->CI->auth_model->ip_login_attempts_exceeded();
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // USER MANAGEMENT / CRUD FUNCTIONS
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Inserts user account and profile data, returning the users new id.
     *
     * @return void
     * @author Rob Hussey
     */
    public function insert_user($email, $password, $group_id = FALSE, $activate = TRUE) {
        $user_id = $this->CI->auth_model->insert_user($email, $password, $group_id);

        if ($user_id) {
            // Check whether to auto activate the user.
            if ($activate) {
                // If an account activation time limit is set by the config file, retain activation token.
                $clear_token = ($this->CI->auth->auth_settings['account_activation_time_limit'] > 0) ? FALSE : TRUE;

                $this->CI->auth_model->activate_user($user_id, FALSE, FALSE, $clear_token);
            }

            $sql_select = array(
                $this->CI->auth->primary_identity_col,
                $this->CI->auth->tbl_col_user_account['activation_token']
            );

            $sql_where[$this->CI->auth->tbl_col_user_account['id']] = $user_id;

            $user = $this->CI->auth_model->get_users($sql_select, $sql_where)->row();

            if (!is_object($user)) {
                $this->CI->auth_model->set_error_message('account_creation_unsuccessful', 'config');
                return FALSE;
            }

            $identity = $user->{$this->CI->auth->db_settings['primary_identity_col']};
            $activation_token = $user->{$this->CI->auth->database_config['user_acc']['columns']['activation_token']};

            // Prepare account activation email.
            // If the $activation_token is not empty, the account must be activated via email before the user can login.
            if (!empty($activation_token)) {
                // Set email data.
                $email_to = $email;
                $email_title = ' - Account Activation';

                $user_data = array(
                    'user_id' => $user_id,
                    'identity' => $identity,
                    'activation_token' => $activation_token
                );
                $template = $this->CI->auth->email_settings['email_template_directory'].$this->CI->auth->email_settings['email_template_activate'];

                if ($this->CI->auth_model->send_email($email_to, $email_title, $user_data, $template)) {
                    $this->CI->auth_model->set_status_message('activation_email_successful', 'config');
                    return $user_id;
                }

                $this->CI->auth_model->set_error_message('activation_email_unsuccessful', 'config');
                return FALSE;
            }

            $this->CI->auth_model->set_status_message('account_creation_successful', 'config');
            return $user_id;
        }
        else {
            $this->CI->auth_model->set_error_message('account_creation_unsuccessful', 'config');
            return FALSE;
        }
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // EMAIL FUNCTIONS
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Emails a user a predefined email template.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function send_email($email_to = FALSE, $email_title = FALSE, $template = FALSE, $email_data = array()) {
        if (!$email_to || !$template || empty($email_data)) {
            return FALSE;
        }

        $template = $this->CI->auth->email_settings['email_template_directory'].$template;

        return $this->CI->auth_model->send_email($email_to, $email_title, $email_data, $template);
    }

    /**
     * flexi auth sends emails for a number of functions, this function can set additional data variables that can then be used by the template files.
     *
     * @return void
     * @author Rob Hussey
     */
    public function template_data($template, $template_data) {
        if (empty($template) && empty($template_data))
        {
            return FALSE;
        }

        // Set template data placeholder.
        $data = $this->CI->auth->template_data;

        // Change default template if set
        if (!empty($template))
        {
            $data['template'] = $template;
        }

        // Add additional template data if set
        if (!empty($template_data))
        {
            $data['template_data'] = $template_data;
        }

        $this->CI->auth->template_data = $data;
    }

    /**
     * Activates a users account allowing them to login to their account.
     * If $verify_token = TRUE, a valid $activation_token must also be submitted.
     *
     * @return void
     * @author Rob Hussey
     */
    public function activate_user($user_id, $activation_token = FALSE, $verify_token = TRUE)
    {
        if ($this->CI->auth_model->activate_user($user_id, $activation_token, $verify_token))
        {
            $this->CI->auth_model->set_status_message('activate_successful', 'config');
            return TRUE;
        }

        $this->CI->auth_model->set_error_message('activate_unsuccessful', 'config');
        return FALSE;
    }

    /**
     * Resends user a new activation token incase they have lost the previous one.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function resend_activation_token($identity)
    {
        // Get primary identity.
        $identity = $this->CI->auth_model->get_primary_identity($identity);

        if (empty($identity))
        {
            $this->CI->auth_model->set_error_message('activation_email_unsuccessful', 'config');
            return FALSE;
        }

        // Get user information.
        $sql_select = array(
            $this->CI->auth->tbl_col_user_account['id'],
            $this->CI->auth->tbl_col_user_account['active']
        );

        $sql_where[$this->CI->auth->primary_identity_col] = $identity;

        $user = $this->CI->auth_model->get_users($sql_select, $sql_where)->row();

        $user_id = $user->{$this->CI->auth->database_config['user_acc']['columns']['id']};
        $active_status = $user->{$this->CI->auth->database_config['user_acc']['columns']['active']};

        // If account is already activated.
        if ($active_status == 1)
        {
            $this->CI->auth_model->set_status_message('account_already_activated', 'config');
            return TRUE;
        }
        // Else, run the deactivate_user() function to reset the users activation token.
        else if ($this->CI->auth_model->deactivate_user($user_id))
        {
            // Get user information.
            $sql_select = array(
                $this->CI->auth->primary_identity_col,
                $this->CI->auth->tbl_col_user_account['activation_token'],
                $this->CI->auth->tbl_col_user_account['email']
            );
            $sql_where[$this->CI->auth->primary_identity_col] = $identity;
            $user = $this->CI->auth_model->get_users($sql_select, $sql_where)->row();

            $email = $user->{$this->CI->auth->database_config['user_acc']['columns']['email']};
            $activation_token = $user->{$this->CI->auth->database_config['user_acc']['columns']['activation_token']};

            // Set email data.
            $email_to = $email;
            $email_title = ' - Account Activation';

            $user_data = array(
                'user_id' => $user_id,
                'identity' => $identity,
                'activation_token' => $activation_token
            );
            $template = $this->CI->auth->email_settings['email_template_directory'].$this->CI->auth->email_settings['email_template_activate'];

            if ($this->CI->auth_model->send_email($email_to, $email_title, $user_data, $template))
            {
                $this->CI->auth_model->set_status_message('activation_email_successful', 'config');
                return TRUE;
            }
        }

        $this->CI->auth_model->set_error_message('activation_email_unsuccessful', 'config');
        return FALSE;
    }

    /**
     * Generates the html for Google reCAPTCHA.
     * Note: If the reCAPTCHA is located on an SSL secured page (https), set $ssl = TRUE.
     *
     * @return string
     * @author Rob Hussey
     */
    public function recaptcha($ssl = FALSE) {
        return $this->CI->auth_model->recaptcha($ssl);
    }

    /**
     * Validates if a Google reCAPTCHA answer submitted via http POST data is correct.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function validate_recaptcha() {
        return $this->CI->auth_model->validate_recaptcha();
    }

    /**
     * Returns whether a user identity is available in the database.
     * The identity columns are defined via the $config['database']['settings']['identity_cols'] variable in the config file.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function identity_available($identity = FALSE, $user_id = FALSE) {
        return $this->CI->auth_model->identity_available($identity, $user_id);
    }

    /**
     * Returns whether an email address is available in the database.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function email_available($email = FALSE, $user_id = FALSE) {
        return $this->CI->auth_model->email_available($email, $user_id);
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
     * Gets the minimum valid password character length.
     *
     * @return int
     * @author Rob Hussey
     */
    public function min_password_length() {
        return $this->CI->auth->auth_security['min_password_length'];
    }

    /**
     * Validate whether the submitted password only contains valid characters defined by the config file.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function valid_password_chars($password = FALSE) {
        return (bool) preg_match("/^[".$this->CI->auth->auth_security['valid_password_chars']."]+$/i", $password);
    }

    /**
     * Validates if a submitted math captcha answer is correct.
     *
     * @return bool
     * @author Rob Hussey
     */
    public function validate_math_captcha($answer = FALSE) {
        return ($answer == $this->CI->session->flashdata($this->CI->auth->session_name['math_captcha']));
    }

    /**
     * Get any operational function messages and groups them into a status and error array.
     * An additional array key named 'type' is also returned to clearly indicate what message types are returned.
     *
     * @return void
     * @author Rob Hussey
     */
    public function get_messages_array($target_user = 'admin', $prefix_delimiter = FALSE, $suffix_delimiter = FALSE) {
        $messages['status'] = $this->CI->auth_model->status_messages($target_user, $prefix_delimiter, $suffix_delimiter);
        $messages['errors'] = $this->CI->auth_model->error_messages($target_user, $prefix_delimiter, $suffix_delimiter);

        // Set a message type identifier to state whether they are either status, error or mixed messages.
        if (! empty($messages['status']) && empty($messages['errors']))
        {
            $messages['type'] = 'status';
        }
        else if (empty($messages['status']) && ! empty($messages['errors']))
        {
            $messages['type'] = 'error';
        }
        else if (! empty($messages['status']) && ! empty($messages['errors']))
        {
            $messages['type'] = 'mixed';
        }
        else
        {
            $messages['type'] = FALSE;
        }

        // If message type is FALSE, no messages are set, so return FALSE.
        return ($messages['type']) ? $messages : FALSE;
    }

    /**
     * Get any operational function messages whether of status or error type and format their output with delimiters.
     *
     * @return void
     * @author Rob Hussey
     */
    public function get_messages($target_user = 'admin', $prefix_delimiter = FALSE, $suffix_delimiter = FALSE) {
        $messages = $this->get_messages_array($target_user, $prefix_delimiter, $suffix_delimiter);

        return ($messages) ? $messages['status'].$messages['errors'] : FALSE;
    }

}

