<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('includes/head'); ?> <!-- title, meta tags, mandatory CSS and JS -->
</head>

<body>

<?php $this->load->view('includes/menu'); ?>
<div class="container" role="main">
    <div class="row">
        <div class="col-md-12">
            <?php if (! empty($message)) { ?>
                <div id="message" class="alert alert-info" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="<?php echo base_url().'static/img/wallet.png'; ?>" />
        </div>
        <div class="col-md-8">
            <h2 class="text-uppercase">Get your free wallet</h2>


            <?php echo form_open('welcome/register_account', array('role' => 'form')); ?>
            <div class="form-group">
                <label for="email_address">Email Address:</label>
                <input class="form-control" name="email_address" type="text" id="email_address" placeholder="Your email" value="<?php echo set_value('register_email_address');?>"
                       title="This wallet requires that upon registration to use full functionality, you will need to activate your account via clicking a link that is sent to your email address.">
            </div>
            <button type="submit" name="register_user" id="register_user" class="btn btn-primary btn-block text-uppercase"><strong>Create Free Wallet</strong></button>
            <?php echo form_close();?>
        </div>
    </div>
    <div class="row" id="authentication-container">
        <div class="col-sm-6" id="login-container">
            <?php echo form_open('welcome/login', array('class' => 'form-horizontal', 'role' => 'form'));?>
            <div class="form-group">
                <label for="identity" class="col-sm-2 control-label">Email</label>
                <div class="col-sm-10">
                    <input type="text" id="identity" name="login_identity" value="<?php echo set_value('login_identity', 'skyzer@gmail.com');?>" class="form-control" placeholder="Enter email"/>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="col-sm-2 control-label">Password</label>
                <div class="col-sm-10">
                    <input type="password" id="password" name="login_password" value="<?php echo set_value('login_password', '938627aa70');?>" class="form-control" placeholder="Enter email"/>
                </div>
            </div>
                <?php
                # reCAPTCHA Example
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
            <input type="submit" name="login_user" value="Sign in" class="col-sm-offset-2 col-sm-10 btn btn-default" />
            <?php echo form_close();?>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-6">
                    <?php echo form_open('welcome/forgotten_password', array('role' => 'form')); ?>
                        <div class="form-group">
                            <label for="forgotten-password-email" class="control-label">Email address</label>
                                <input type="text" id="forgotten-password-email" name="forgotten-password-email" value="<?php echo set_value('forgotten-password-email');?>" class="form-control" placeholder="Enter email"/>
                        </div>
                    <input type="submit" name="forgotten_password" id="forgotten_password" value="Reset Forgotten Password" class="col-lg-12 btn btn-default" />
                    <?php echo form_close();?>
                </div>
                <div class="col-sm-6">
                    <?php echo form_open('welcome/resend_activation', array('role' => 'form')); ?>
                        <div class="form-group">
                            <label for="account-activation-email" class="control-label">Email address</label>
                            <input type="text" id="account-activation-email" name="account-activation-email" value="<?php echo set_value('account-activation-email');?>" class="form-control" placeholder="Enter email"/>
                        </div>
                        <input type="submit" name="send_activation_token" id="send_activation_token" value="Resend Account Activation Token" class="col-lg-12 btn btn-default" />
                    <?php echo form_close();?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" id="documentation-container">
            <h3 class="text-uppercase">Open Source BitCoin  Wallet</h3>

            <div>Made to be the simplest, fastest way to deploy a bitcoin wallet and introducing bitcoin to a whole new class of developers. </div>
            <br><br>

            <h4>Features</h4>
            <ul>
                <li>Supports over 100 fiat currencies.</li>
                <li>Supports Multiple Crypto Currencies.</li>
                <li>Runs on code igniter php framework for small footprint, easy install, secure database orm and mvc model.</li>
            </ul>

            <br><br>

            <h4>Security</h4>
            <ul>
                <li>Hashed passwords</li>
                <li>Protection against brute Force Logins</li>
                <li>Database ORM model used prepared statements to avoid sql injection attacks</li>
                <li>Protection against Security Scanners such as acutex etc..</li>
            </ul>

            <br><br>

            <h4>Requirements</h4>
            <ul>
                <li><a href="">LAMP</a> - Linux Apache MySql PHP platform. (comes installed by default on most linux servers)</li>
                <li>PDO mysql php module</li>
                <li>MYSQLI php module</li>
                <li>mcrypt php module</li>
                <li><a href="">Code Igniter PHP framework</a> (comes included)</li>
                <li>Ez Wallet BitCoin API or Blockchain.info API</li>
            </ul>

            <br><br>

            <h4>Install Guide</h4>
            <ul>
                <li><a href="http://github.com/">Get the Source Code Here</a></li>
            </ul>

            <br><br>

            <div>Please join us in making this solve even more problems for people</div>
            <br>
            <ul>
                <li><a href="http://github.com/">GitHub</a></li>
                <li><a href="http://bitcointalk.org">BitCoinTalk.org thread</a></li>
                <li><a href="http://bitcointalk.org">Reddit thread</a></li>
                <li>Donate to the cause </li>
            </ul>
        </div>
            <p><?php echo "User data: ".print_r($this->session->userdata['ez_auth']); ?></p>
    </div>
</div>

</body>
</html>