<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('includes/head'); ?> <!-- title, meta tags, mandatory CSS and JS -->
</head>

<body>

<div class="container-fluid">
    <?php $this->load->view('includes/menu'); ?>
    <div class="row">
        <div class="col-lg-12">
            <h2>User Signup</h2>

            <?php if (! empty($message)) { ?>
                <div id="message">
                    <?php echo $message; ?>
                </div>
            <?php } ?>

            <?php echo form_open('welcome/register_account'); ?>
            <fieldset>
                <legend>Enter Your Details</legend>
                <ul>
                    <li class="info_req">
                        <label for="email_address">Email Address:</label>
                        <input type="text" id="email_address" name="register_email_address" value="<?php echo set_value('register_email_address');?>" class="tooltip_trigger"
                               title="This demo requires that upon registration, you will need to activate your account via clicking a link that is sent to your email address."
                            />
                    </li>
                    <li>
                        <hr/>
                        <label for="submit">Register:</label>
                        <input type="submit" name="register_user" id="submit" value="Create free wallet" class="btn btn-lg"/>
                    </li>
                </ul>
            </fieldset>
            <?php echo form_close();?>
            <h3>Already registered?</h3>
            <div class="col100">
                <h2>User Login</h2>
                <?php echo form_open('welcome/login');?>
                <fieldset class="w50 parallel_target">
                    <legend>Registered Users</legend>
                    <ul>
                        <li>
                            <label for="identity">Email or Username:</label>
                            <input type="text" id="identity" name="login_identity" value="<?php echo set_value('login_identity', 'skyzer@gmail.com');?>" class="tooltip_parent"/>
                        </li>
                        <li>
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="login_password" value="<?php echo set_value('login_password', '938627aa70');?>"/>
                        </li>
                        <?php
                        # Below are 2 examples, the first shows how to implement 'reCaptcha' (By Google - http://www.google.com/recaptcha),
                        # the second shows 'math_captcha' - a simple math question based captcha that is native to the flexi auth library.
                        # This example is setup to use reCaptcha by default, if using math_captcha, ensure the 'auth' controller and 'demo_auth_model' are updated.

                        # reCAPTCHA Example
                        # To activate reCAPTCHA, ensure the 'if' statement immediately below is uncommented and then comment out the math captcha 'if' statement further below.
                        # You will also need to enable the recaptcha examples in 'controllers/auth.php', and 'models/demo_auth_model.php'.
                        #/*
                        if (isset($captcha))
                        {
                            echo "<li>\n";
                            echo $captcha;
                            echo "</li>\n";
                        }
                        #*/

                        ?>
                        <li>
                            <label for="remember_me">Remember Me:</label>
                            <input type="checkbox" id="remember_me" name="remember_me" value="1" <?php echo set_checkbox('remember_me', 1); ?>/>
                        </li>
                        <li>
                            <label for="submit">Login:</label>
                            <input type="submit" name="login_user" id="submit" value="Submit" class="link_button large"/>
                        </li>
                        <li>
                            <hr/>
                            <a href="auth/forgotten_password">Reset Forgotten Password</a>
                        </li>
                        <li>
                            <a href="auth/resend_activation_token">Resend Account Activation Token</a>
                        </li>
                    </ul>
                </fieldset>
                <?php echo form_close();?>
            </div>
            <p><?php echo "User data: <pre>".print_r($this->session->userdata['ez_auth'])."</pre>";?></p>
        </div>
    </div>
</div>

</body>
</html>