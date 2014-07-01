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
            <?php } ?>
            <h2>You are now logged in -name-</h2>
        </div>
    </div>
</div>

</body>
</html>