<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for authentication
 */
class MY_Controller extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(array('url', 'form'));

        // IMPORTANT! This global must be defined BEFORE the auth library is loaded!
        // It is used as a global that is accessible via both models and both libraries, without it, auth will not work.
        $this->auth = new stdClass;

        // Load 'standard' flexi auth library by default. (ez)
        $this->load->library('ez_auth');
    }

    protected function is_authenticated() {
        if ($this->ez_auth->is_logged_in() && uri_string() != 'logout')
        {
            // Preserve any flashdata messages so they are passed to the redirect page.
            if ($this->session->flashdata('message')) { $this->session->keep_flashdata('message'); }
            return true;
        }
        else
        {
//            redirect('login');
            $data['title'] = 'Create a free wallet';
            $this->load->view('auth/register', $data);
            return false;
        }
    }

    public function register_account() {
        // Redirect user away from registration page if already logged in.
        if ($this->ez_auth->is_logged_in())
        {
            redirect();
        }
        // If 'Registration' form has been submitted, attempt to register their details as a new account.
        else if ($this->input->post('register_user'))
        {
            $this->load->model('auth_model');
            $this->auth_model->register_account();
        }

        // Get any status message that may have been set.
        $this->data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];

        echo "<pre>".print_r($this->data)."</pre>";
        $this->data['title'] = 'Register account please';
        $this->load->view('welcome', $this->data);
    }

    /**
     * login
     * Login page used by all user types to log into their account.
     * This demo includes 3 example accounts that can be logged into via using either their email address or username. The login details are provided within the view page.
     * Users without an account can register for a new account.
     * Note: This page is only accessible to users who are not currently logged in, else they will be redirected.
     */
    public function login() {
        // If 'Login' form has been submited, attempt to log the user in.
        if ($this->input->post('login_user'))
        {
            $this->load->model('auth_model');
            $this->auth_model->login();
        } else if ($this->input->post('login_user_header')) {
            $this->load->model('auth_model');
            $this->auth_model->login();
        }
        else {
            $this->data['message'] = 'Authorization failed';
        }

        // CAPTCHA Example
        // Check whether there are any existing failed login attempts from the users ip address and whether those attempts have exceeded the defined threshold limit.
        // If the user has exceeded the limit, generate a 'CAPTCHA' that the user must additionally complete when next attempting to login.
        if ($this->ez_auth->ip_login_attempts_exceeded())
        {
            /**
             * reCAPTCHA
             * http://www.google.com/recaptcha
             * To activate reCAPTCHA, ensure the 'recaptcha()' function below is uncommented and then comment out the 'math_captcha()' function further below.
             *
             * A boolean variable can be passed to 'recaptcha()' to set whether to use SSL or not.
             * When displaying the captcha in a view, if the reCAPTCHA theme has been set to one of the template skins (See https://developers.google.com/recaptcha/docs/customization),
             *  then the 'recaptcha()' function generates all the html required.
             * If using a 'custom' reCAPTCHA theme, then the custom html must be PREPENDED to the code returned by the 'recaptcha()' function.
             * Again see https://developers.google.com/recaptcha/docs/customization for a template 'custom' html theme.
             *
             * Note: To use this example, you will also need to enable the recaptcha examples in 'models/demo_auth_model.php', and 'views/demo/login_view.php'.
             */
            $this->data['captcha'] = $this->ez_auth->recaptcha(FALSE);

            /**
             * flexi auths math CAPTCHA
             * Math CAPTCHA is a basic CAPTCHA style feature that asks users a basic maths based question to validate they are indeed not a bot.
             * For flexibility on CAPTCHA presentation, the 'math_captcha()' function only generates a string of the equation, see the example below.
             *
             * To activate math_captcha, ensure the 'math_captcha()' function below is uncommented and then comment out the 'recaptcha()' function above.
             *
             * Note: To use this example, you will also need to enable the math_captcha examples in 'models/demo_auth_model.php', and 'views/demo/login_view.php'.
             */
            # $this->data['captcha'] = $this->flexi_auth->math_captcha(FALSE);
        }

        // Get any status message that may have been set.
        $this->data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];

        $this->load->view('auth/register', $this->data);
    }

    /**
     * This example logs the user out of all sessions on all computers they may be logged into.
     * In this demo, this page is accessed via a link on the demo header once a user is logged in.
     */
    public function logout() {
        // By setting the logout functions argument as 'TRUE', all browser sessions are logged out.
        $this->ez_auth->logout(TRUE);

        // Set a message to the CI flashdata so that it is available after the page redirect.
        $this->session->set_flashdata('message', $this->ez_auth->get_messages());

        redirect();
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // Account Activation
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * User account activation via email.
     * The default setup of this demo requires that new account registrations must be authenticated via email before the account is activated.
     * In this demo, this page is accessed via an activation link in the 'views/includes/email/activate_account.tpl.php' email template.
     */
    public function activate_account($user_id, $token = FALSE)
    {
        // The 3rd activate_user() parameter verifies whether to check '$token' matches the stored database value.
        // This should always be set to TRUE for users verifying their account via email.
        // Only set this variable to FALSE in an admin environment to allow activation of accounts without requiring the activation token.
        $this->ez_auth->activate_user($user_id, $token, TRUE);

        // Save any public status or error messages (Whilst suppressing any admin messages) to CI's flash session data.
        $this->session->set_flashdata('message', $this->ez_auth->get_messages());

        redirect('auth');
    }

    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###
    // Forgotten Password
    ###++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++###

    /**
     * Send user an email to verify their identity. Via a unique link in this email, the user is redirected to the site so they can then reset their password.
     * In this demo, this page is accessed via a link on the login page.
     *
     * Note: This is step 1 of an example of allowing users to reset a forgotten password manually.
     * See the auto_reset_forgotten_password() function below for an example of directly emailing the user a new randomised password.
     */
    public function forgotten_password() {
        // If the 'Forgotten Password' form has been submitted, then email the user a link to reset their password.
        if ($this->input->post('send_forgotten_password'))
        {
            $this->load->model('auth_model');
            $this->auth_model->forgotten_password();
        }

        // Get any status message that may have been set.
        $this->data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];

        $this->load->view('auth/register', $this->data);
    }

    /**
     * Resend user an activation token via email.
     * If a user has not received/lost their account activation email, they can request a new activation email to be sent to them.
     * In this demo, this page is accessed via a link on the login page.
     */
    public function resend_activation_token() {
        // If the 'Resend Activation Token' form has been submitted, resend the user an account activation email.
        if ($this->input->post('send_activation_token'))
        {
            $this->load->model('auth_model');
            $this->auth_model->resend_activation_token();
        }

        // Get any status message that may have been set.
        $this->data['message'] = (! isset($this->data['message'])) ? $this->session->flashdata('message') : $this->data['message'];

        $this->load->view('auth/register', $this->data);
    }
}
