<?php
include_once dirname(__FILE__) . '/../include/settings.php';
$ordersObj = new Orders();
$items = $_POST['items'];
$order_id = $_POST['order_id'];
$ordersObj->deleteOrderItems($order_id);
$ordersObj->deleteOrderServices($order_id);
foreach ($items as $item) {
    $d = [
        'shopId' => $userData['shopId'],
        'owner_id' => $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'],
        'product_id' => $item['product_id'],
        'order_id' => $order_id,
        'description' => $item['description'],
        'quantity' => $item['quantity'],
        'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
        'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
        'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
        'discount' => 0,
        'discount_type' => !empty($item['discount_type']) ? $item['discount_type'] : 1,
        'price' => $item['price'],
        'services' => [],
        'raw_items' => [],
        'status' => 1,
        'item_status' => !empty($item['item_status']) ? $item['item_status'] : 1,
        'employee_id' => !empty($item['employee_id']) ? $item['employee_id'] : null,
        'start_date' => !empty($item['start_date']) ? $item['start_date'] : null,
        'end_date' => !empty($item['end_date']) ? $item['end_date'] : null,
        'priority' => !empty($item['priority']) ? $item['priority'] : 1,
    ];
    $created = $ordersObj->createOrderDetails($d);
}
echo json_encode(['message' => 'Prodcut Added Successfully!']);
