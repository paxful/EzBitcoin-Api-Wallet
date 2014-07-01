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
            <?php if (! empty($message)) { ?>
                <div id="message">
                    <?php echo $message; ?>
                </div>
            <?php }
            if ($this->session->flashdata('message') != ''):
                echo $this->session->flashdata('message');
            endif;
            ?>
            <h2>Welcome</h2>
            <p><?php echo "User data: <pre>".print_r($this->session->userdata['ez_auth'])."</pre>";?></p>
        </div>
    </div>
</div>

</body>
</html>