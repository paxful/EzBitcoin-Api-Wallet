<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('includes/head'); ?> <!-- title, meta tags, mandatory CSS and JS -->
</head>

<body>
<?php $this->load->view('includes/menu'); ?>
<div class="container" role="main">
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
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <h2 class="text-left">0.00000000 BTC</h2>
            <span class="help-block">0.00 USD (645.01)</span>
        </div>
        <div class="col-md-8">
            <div class="pull-right currency-selector-container">
                <select class="form-control">
                    <option>USD</option>
                    <option>EUR</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><span class="glyphicon glyphicon-globe"></span> My Account</h4>
                </div>
                <div class="panel-body">
                    <select id="crypto_type" class="form-control">
                        <option value="BTC">Bitcoin</option>
                    </select>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><span class="glyphicon glyphicon-send"></span> Send</h4>
                </div>
                <div class="panel-body">

                    <div class="hide-for-large-up">
                        <span class="help-block">Clicking <strong>Scan QR</strong></strong> will open barcode scanner app</span>
                        <a href="" class="btn btn-info btn-sm btn-block">Scan QR</a>
                    </div>
                    <hr />
                    <form role="form" name="sendbtc" id="sendbtc" method="post" action="#">
                        <div class="form-group">
                            <label for="send_address" class="sr-only">Send address</label>
                            <input name="send_address" type="text" id="send_address" placeholder="Send to bitcoin address" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <label for="send_amount_crypto" class="sr-only">Send amount BTC</label>
                                        <div class="input-group-addon"><span class="glyphicon glyphicon-star"></span></div>
                                        <input name="send_amount_crypto" id="send_amount_crypto" type="number" placeholder="Amount BTC" class="form-control" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <label for="send_amount_fiat" class="sr-only">Send amount fiat</label>
                                        <div class="input-group-addon">$</div>
                                        <input name="send_amount_fiat" id="send_amount_fiat" type="number" placeholder="Or amount $" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="label" class="sr-only">Optional label</label>
                            <input name="label" id="label" placeholder="Optional label" class="form-control">
                        </div>
                        <input class="btn btn-primary btn-lg btn-block " id="button_send" value="Send Now" />
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <table class="transactions-table table table-striped table-responsive">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Balance</th>
                    </tr>
                </thead>
            <tbody>
                <tr>
                    <td>1111</td>
                    <td>Description</td>
                    <td>Amount</td>
                    <td>Balance</td>
                </tr>
                <tr>
                    <td>1111</td>
                    <td>Description</td>
                    <td>Amount</td>
                    <td>Balance</td>
                </tr>
                <tr>
                    <td>1111</td>
                    <td>Description</td>
                    <td>Amount</td>
                    <td>Balance</td>
                </tr>
                <tr>
                    <td>1111</td>
                    <td>Description</td>
                    <td>Amount</td>
                    <td>Balance</td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>