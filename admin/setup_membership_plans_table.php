<?php
/**
 * One-time setup: creates membership_plans and migrates data from
 * royal_membership, classic_membership, standard_membership (feature rows → JSON per billing period).
 * Open once in the browser while logged into admin context, or run via CLI: php setup_membership_plans_table.php
 */
require_once __DIR__ . '/../connect.php';

if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$create_sql = "
CREATE TABLE IF NOT EXISTS `membership_plans` (
  `mp_id` int(11) NOT NULL AUTO_INCREMENT,
  `pass_key` varchar(20) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `billing_plan` varchar(20) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `features_json` longtext NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mp_id`),
  UNIQUE KEY `uq_pass_billing` (`pass_key`,`billing_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (!mysqli_query($con, $create_sql)) {
    die('Failed to create membership_plans: ' . mysqli_error($con));
}

$featured_chk = mysqli_query($con, "SHOW COLUMNS FROM membership_plans LIKE 'is_featured'");
if ($featured_chk && mysqli_num_rows($featured_chk) === 0) {
    mysqli_query(
        $con,
        'ALTER TABLE membership_plans ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER features_json'
    );
}

$count_res = mysqli_query($con, 'SELECT COUNT(*) AS c FROM membership_plans');
$count_row = mysqli_fetch_assoc($count_res);
if ((int) $count_row['c'] > 0) {
    echo 'Table membership_plans already has data. Schema ensured; migration skipped.';
    exit;
}

$migrate_map = [
    'royal' => [
        'table' => 'royal_membership',
        'plan_col' => 'royal_plan',
        'desc_col' => 'royal_desc',
        'price_col' => 'royal_price',
        'default_name' => 'Royal Pass',
    ],
    'classic' => [
        'table' => 'classic_membership',
        'plan_col' => 'classic_plan',
        'desc_col' => 'classic_desc',
        'price_col' => 'classic_price',
        'default_name' => 'Classic Pass',
    ],
    'standard' => [
        'table' => 'standard_membership',
        'plan_col' => 'standard_plan',
        'desc_col' => 'standard_desc',
        'price_col' => 'standard_price',
        'default_name' => 'Standard Pass',
    ],
];

$insert_stmt = mysqli_prepare(
    $con,
    'INSERT INTO membership_plans (pass_key, display_name, billing_plan, price, features_json) VALUES (?, ?, ?, ?, ?)'
);
if (!$insert_stmt) {
    die('Prepare failed: ' . mysqli_error($con));
}

$migrated = 0;
foreach ($migrate_map as $pass_key => $meta) {
    $table = $meta['table'];
    $chk = mysqli_query($con, "SHOW TABLES LIKE '" . mysqli_real_escape_string($con, $table) . "'");
    if (!$chk || mysqli_num_rows($chk) === 0) {
        continue;
    }

    foreach (['yearly', 'monthly'] as $billing) {
        $plan_c = $meta['plan_col'];
        $desc_c = $meta['desc_col'];
        $price_c = $meta['price_col'];
        $billing_esc = mysqli_real_escape_string($con, $billing);
        $q = "SELECT `{$desc_c}` AS feat, `{$price_c}` AS pr FROM `{$table}` WHERE `{$plan_c}` = '{$billing_esc}' ORDER BY 1";
        $r = mysqli_query($con, $q);
        if (!$r || mysqli_num_rows($r) === 0) {
            continue;
        }

        $features = [];
        $max_price = 0;
        while ($row = mysqli_fetch_assoc($r)) {
            $t = trim((string) $row['feat']);
            if ($t !== '') {
                $features[] = $t;
            }
            $p = (int) $row['pr'];
            if ($p > $max_price) {
                $max_price = $p;
            }
        }

        $json = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = '[]';
        }

        $display_name = $meta['default_name'];
        mysqli_stmt_bind_param(
            $insert_stmt,
            'sssis',
            $pass_key,
            $display_name,
            $billing,
            $max_price,
            $json
        );
        if (mysqli_stmt_execute($insert_stmt)) {
            $migrated++;
        }
    }
}

mysqli_stmt_close($insert_stmt);

echo 'membership_plans table ready. Migrated ' . (int) $migrated . ' plan row(s) from legacy tables.';
