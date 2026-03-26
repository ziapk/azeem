<?php 

$user = $_SESSION['user_credentials'];
// if(!empty($user) && $user['role'] == 'manager') {
    // $customers = new Customers();
    // $user = $customers->getCustomerByLinkedShop($user['shopId']);

    // if(!empty($user['account'])) {
    //     $de = new DoubleEntry();

    //     $journel = $de->getLedgerByAccount(['account_id' => $user['account']['id'], 'type' => 'c', 'from' => $from, 'to' => $to, 'user' => $user['account']]);
    //     $summery = $journel['summery'];

    //     $paid = $summery['paid'];
    //     $amount = $summery['due'];
    //     $balance = $summery['balance'];

    //     $url = '?';

    //     foreach ($_GET as $key => $value) {
    //         $url .= $key . "=" . $value . "&";
    //     }


        ?>
        <!-- <div class="container" ng-controller="coaController">
            <div class="row">
                <div class="col-sm-8">
                    <h2>Account Summary</h2>
                    <p><?php echo $user['full_name']; ?></p>
                    <p><?php echo $user['address']; ?> (<?php echo $user['company']; ?>) </p>
                    <p>Contact No: <?php echo $user['phoneNumber']; ?></p>
                </div>
                <div class="col-sm-4 form-group">
                    <table class="table table-sm table-striped">
                        <tr>
                            <td>Openining Balance:</td>
                            <td style="font-weight: bold; font-size: 1.3em" width="140"><?php echo number_format($user['account']['opening_balance'], 0); ?><br /></td>
                        </tr>
                        <tr>
                            <td>Total Invoices:</td>
                            <td style="font-weight: bold; font-size: 1.3em"><?php echo $summery['total']; ?><br /></td>
                        </tr>
                        <tr>
                            <td>Total Amount:</td>
                            <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($amount, 0); ?><br /></td>
                        </tr>
                        <tr>
                            <td>Total Paid:</td>
                            <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($paid, 0); ?><br /></td>
                        </tr>
                        <tr>
                            <td>Closing Balance:</td>
                            <td style="font-weight: bold; font-size: 1.3em"><?php echo number_format($balance, 0); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div> -->
<?php
        
    // }
// }

include_once dirname(__FILE__).'/../product/index.php';