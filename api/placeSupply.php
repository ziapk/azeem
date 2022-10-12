<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];

// 1. add supplier if not get id or if didn't get id or name set no supplier [done]
// 2. add product with minimal detail as new product [done]
// 3. add barcode in product barcode table if new [done]
// 4. update product purchase price and qty and sale price if demanded [done]
// 5. make a transation base on payment [done]
// 6. maintain a supplier wallet base on what we paid to him/her. [done]
// 7. success or error response 

$supplierId = 1;
$supplierObj = new Suppliers();
if(empty($_POST['supplierId']) && !empty($_POST['supplierName'])) {
    $data = [
        'name' => $_POST['supplierName'],
        'contact' => !empty($_POST['supplierContact']) ? $_POST['supplierContact'] : "",
        'address' => "",
        'wallet' => 0,
    ];

    $supplierId = $supplierObj->createSupplier($data);
}


$products = new Products();


$items = [];

if(sizeof($_POST['items'])) {
    foreach($_POST['items'] as $item) {
        if(!empty($item['id'])) {

            $items[] = [
                'id' => $item['id'],
                'pprice' => $item['pprice'],
                'price' => $item['price'],
                'barcode' => $item['barcode'],
                'full_name' => $item['full_name'],
                'qty' => $item['qty'],
            ];
        }
        else {
            $id = $products->createProduct([
                'full_name' => $item['full_name'],
                'owner_id' => $ownerId,
                'user_id' => $userData['id'],
                'price' => $item['price'],
                'pprice' => $item['pprice'],
                'in_hand' => 0,
                'min_qty' => 0,
                'pack_size' => 0,
                'pack_price' => 0,
                'image' => null,
                'code' => null,
                'description' => "",
                'group' => null,
                'barcode' => !empty($item['barcode']) ? $item['barcode'] : null,
            ]);

            if(!empty($item['barcode'])) {
                $products->createProductCode([
                    'product_id' => $id,
                    'code' => $item['barcode']
                ]);
            }

            $items[] = [
                'id' => $id,
                'pprice' => $item['pprice'],
                'price' => $item['price'],
                'barcode' => !empty($item['barcode']) ? $item['barcode'] : null,
                'full_name' => $item['full_name'],
                'qty' => $item['qty'],
            ];
        }
    }
}



if(sizeof($items)) {

    foreach($items as $item) {
        $products->addProductSupply($item);
    }
}

$supply = new Supply();
$data = [
    'user_id' => $userData['id'],
    'supplier_id' => !empty($supplierId) ? $supplierId : 1,
    'status' => 2,
    'price' => $_POST['subTotal'],
    'discount' => $_POST['discount'],
    'shopId' => $userData['shopId'],
    'supply_date' => date('Y-m-d')
];

$supply_id = $supply->createSupply($data);

if($supply_id) {

    if(sizeof($items)) {
        foreach ($items as $item) {
            $d = [
                'supply_id' => $supply_id,
                'product_id' => $item['id'],
                'quantity' => $item['qty'],
                'price' => $item['pprice'],  
            ];
           /*  $items[] = [
                'qty' => 0,
                'stock_out' => $item['qty'],
                'shopId' => $userData['shopId'],
                'product_id' => $item['id']
            ]; */
            $supply->createSupplyDetails($d);
        }
    }

    $transaction = [
        'supplier_id' => !empty($supplierId) ? $supplierId : 1,
        'user_id' => $userData['id'],
        'amount' => !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0,
        'payment_date' => date('Y-m-d H:i:s'),
        'transaction_type' => 1,        
        'supply_id' => $supply_id,
        'shopId' => $userData['shopId']
    ];

    $transactionId = $supply->makeTransaction($transaction);

    $amount = !empty($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;

    $wallet = [
        'id' => !empty($supplierId) ? $supplierId : 1,
        'wallet' => $amount - ($_POST['subTotal'] - $_POST['discount'])
    ];

    
    $manageWallet = $supply->manageWallet($wallet);
    /* $productUpdated = [];
    foreach ($items as $value) {
        $productUpdated[] = $products->assignProduct($value);
    }
 */

    echo json_encode(['status' => 200, 'message' => 'successfully done', 'supply' => [ 'id'=> $supply_id ]]);
}

?>