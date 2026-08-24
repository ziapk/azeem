<?php
include_once dirname(__FILE__) . '/../include/settings.php';

/**
 * Read the order payload.
 *
 * The cart posts one input variable per item field, so a large bill blows past
 * php.ini's `max_input_vars` (1000 by default — note that `ini_set()` cannot
 * raise it, it is PHP_INI_PERDIR). PHP then silently TRUNCATES $_POST, dropping
 * both the tail of `items` and every key that sorts after it alphabetically
 * (subTotal, status, shopId, payment_amount, payment_with, summery), which is
 * how orders ended up saved with price = NULL and a partial item list.
 *
 * JSON bodies are read straight from php://input and are not subject to that
 * limit at all, so that is the path the pages use now. The urlencoded branch is
 * kept for older cached pages and refuses to run on a truncated request.
 */
function readOrderPayload()
{
    $contentType = !empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [null, 'Could not read the order data (invalid JSON).'];
        }
        return [$decoded, null];
    }

    // urlencoded fallback — detect the truncation described above.
    $raw = file_get_contents('php://input');
    if ($raw !== '') {
        $sent = substr_count($raw, '&') + 1;
        $limit = (int)ini_get('max_input_vars');
        if ($limit > 0 && $sent > $limit) {
            return [null, 'This bill is too large to be submitted by this page (' . $sent
                . ' fields, server limit ' . $limit . '). Nothing was saved — please reload the page and try again.'];
        }
    }

    return [$_POST, null];
}

try {
    $supply = new Supply();
    $orders = new Orders();

    list($payload, $error) = readOrderPayload();

    if ($error !== null) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => $error]);
        exit;
    }

    if (!empty($_GET['debug'])) {
        print_r($payload);
        exit;
    }

    $response = $orders->prepareOrder($payload);

    if ($response['status'] === 200) {
        // $order = [];
        // if ($response['order']['linked_shop']) {
        //     $order = $orders->getOrder($response['order']['id']);
        //     $supply->prepareSupplyAgainstOrder($order, ['payment_with' => $payload['payment_with']]);
        // }
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode($response);
    }
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage() . "<br/>");
}
