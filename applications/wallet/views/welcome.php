<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('includes/head'); ?> <!-- title, meta tags, mandatory CSS and JS -->
</head>

<body>
<?php $this->load->view('includes/menu'); ?>
<div class="container-fluid" role="main">
    <div class="row">
        <div class="col-lg-12">
            <?php if (! empty($message)) { ?>
                <div id="message" class="alert alert-info" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php }
            if ($this->session->flashdata('message') != ''):
                echo $this->session->flashdata('message');
            endif;
            ?>
            <h2>Welcome</h2>
            <p><?php echo "User data: <pre>".print_r($this->session->userdata['ez_auth']['user_identifier'])."</pre>";?></p>
            <?php echo $this->session->userdata['ez_auth']['user_identifier']; ?>
            <?php if ($this->ez_auth->is_logged_in()) { ?>
                <li>
                    <a href="welcome/logout">Logout</a>
                </li>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>