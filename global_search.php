<?php
include_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=UTF-8');

$query = isset($_GET['query']) ? trim((string) $_GET['query']) : '';
$query_length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);

if ($query === '' || $query_length < 2) {
    echo json_encode([
        'query' => $query,
        'services' => [],
        'products' => [],
        'hasResults' => false
    ]);
    exit;
}

$like_term = '%' . $query . '%';

$run_prepared_query = static function (mysqli $db_connection, string $sql, string $param) {
    $statement = mysqli_prepare($db_connection, $sql);
    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 's', $param);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $rows = [];

    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    mysqli_stmt_close($statement);
    return $rows;
};

$services_sql = "
    SELECT service_name, service_price
    FROM (
        SELECT hair_service AS service_name, hair_price AS service_price FROM haircut_service
        UNION ALL
        SELECT beard_service AS service_name, beard_price AS service_price FROM beard_service
        UNION ALL
        SELECT skin_service AS service_name, skin_price AS service_price FROM skin_service
        UNION ALL
        SELECT spa_service AS service_name, spa_price AS service_price FROM spa_service
    ) AS all_services
    WHERE service_name LIKE ?
    ORDER BY service_name ASC
    LIMIT 8
";

$products_sql = "
    SELECT p_name, p_price, COALESCE(p_discount, 0) AS p_discount
    FROM products
    WHERE p_name LIKE ?
    ORDER BY p_name ASC
    LIMIT 8
";

$service_rows = $run_prepared_query($con, $services_sql, $like_term);
$product_rows = $run_prepared_query($con, $products_sql, $like_term);

$services = [];
foreach ($service_rows as $service_row) {
    $service_name = trim((string) ($service_row['service_name'] ?? ''));
    if ($service_name === '') {
        continue;
    }

    $services[] = [
        'name' => $service_name,
        'price' => (float) ($service_row['service_price'] ?? 0),
        'url' => 'service.php?search=' . rawurlencode($service_name) . '&q=' . rawurlencode($query)
    ];
}

$products = [];
foreach ($product_rows as $product_row) {
    $product_name = trim((string) ($product_row['p_name'] ?? ''));
    if ($product_name === '') {
        continue;
    }

    $base_price = (float) ($product_row['p_price'] ?? 0);
    $discount_percent = (float) ($product_row['p_discount'] ?? 0);
    $final_price = $base_price - (($base_price * $discount_percent) / 100);

    $products[] = [
        'name' => $product_name,
        'price' => $final_price,
        'url' => 'eshop.php?search=' . rawurlencode($product_name) . '&q=' . rawurlencode($query)
    ];
}

echo json_encode([
    'query' => $query,
    'services' => $services,
    'products' => $products,
    'hasResults' => (!empty($services) || !empty($products))
]);
exit;
