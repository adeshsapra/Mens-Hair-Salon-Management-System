<?php

if (!function_exists('ensureUserWallet')) {
    function ensureUserWallet($con, $userId) {
        $userId = (int) $userId;
        return mysqli_query(
            $con,
            "INSERT INTO wallets (user_id, balance) VALUES ({$userId}, 0.00) ON DUPLICATE KEY UPDATE user_id = user_id"
        );
    }
}

if (!function_exists('getUserWalletBalance')) {
    function getUserWalletBalance($con, $userId) {
        $userId = (int) $userId;
        ensureUserWallet($con, $userId);

        $result = mysqli_query($con, "SELECT balance FROM wallets WHERE user_id = {$userId} LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return (float) $row['balance'];
        }

        return 0.0;
    }
}

if (!function_exists('insertWalletTransaction')) {
    function insertWalletTransaction($con, $userId, $amount, $type, $source, $orderId = null) {
        $userId = (int) $userId;
        $amount = (float) $amount;
        $type = mysqli_real_escape_string($con, strtolower(trim($type)));
        $source = mysqli_real_escape_string($con, strtolower(trim($source)));
        $orderValue = $orderId === null ? 'NULL' : (int) $orderId;
        $saleValue = $orderId === null ? 'NULL' : (int) $orderId;

        $query = "
            INSERT INTO wallet_transactions (user_id, amount, type, source, order_id, sale_id, date)
            VALUES ({$userId}, {$amount}, '{$type}', '{$source}', {$orderValue}, {$saleValue}, NOW())
        ";

        return mysqli_query($con, $query);
    }
}

if (!function_exists('creditWalletBalance')) {
    function creditWalletBalance($con, $userId, $amount, $source = 'refund', $orderId = null) {
        $userId = (int) $userId;
        $amount = (float) $amount;

        if (!ensureUserWallet($con, $userId)) {
            return false;
        }

        $updated = mysqli_query($con, "UPDATE wallets SET balance = balance + {$amount} WHERE user_id = {$userId}");
        if (!$updated) {
            return false;
        }

        return insertWalletTransaction($con, $userId, $amount, 'credit', $source, $orderId);
    }
}

if (!function_exists('debitWalletBalance')) {
    function debitWalletBalance($con, $userId, $amount, $source = 'order_payment', $orderId = null) {
        $userId = (int) $userId;
        $amount = (float) $amount;

        if (!ensureUserWallet($con, $userId)) {
            return false;
        }

        $updated = mysqli_query(
            $con,
            "UPDATE wallets SET balance = balance - {$amount} WHERE user_id = {$userId} AND balance >= {$amount}"
        );
        if (!$updated || mysqli_affected_rows($con) === 0) {
            return false;
        }

        return insertWalletTransaction($con, $userId, $amount, 'debit', $source, $orderId);
    }
}

?>
