<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">

        <?php if ($this->ez_auth->is_logged_in()): ?>
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#wallet-navbar-menu">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Web wallet</a>
            </div>
            <div class="collapse navbar-collapse" id="wallet-navbar-menu">
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="" class="dropdown-toggle" data-toggle="dropdown">Hi <?php echo $this->session->userdata['ez_auth']['user_identifier']; ?><span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="">Settings</a></li>
                            <li class="divider"></li>
                            <li><a href="welcome/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#login-navbar-menu">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Web wallet</a>
            </div>
            <div id="login-navbar-menu" class="navbar-collapse collapse" style="height: 1px;">
                <?php echo form_open('welcome/login', array('class' => 'navbar-form navbar-right', 'role' => 'form'));?>
                    <div class="form-group">
                        <input type="text" name="login_identity" value="<?php echo set_value('login_identity', 'skyzer@gmail.com');?>" placeholder="Email" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="password" placeholder="Password" name="login_password" value="<?php echo set_value('login_password', '938627aa70');?>" class="form-control">
                    </div>
                    <input type="submit" name="login_user_header" value="Sign in" class="btn btn-primary" />
                <?php echo form_close();?>
            </div>
        <?php endif; ?>

    </div>
</div>