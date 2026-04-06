<?php

if (!function_exists('paymentIntegrationTableExists')) {
    function paymentIntegrationTableExists(mysqli $con, string $tableName): bool
    {
        $safe = mysqli_real_escape_string($con, $tableName);
        $result = mysqli_query($con, "SHOW TABLES LIKE '{$safe}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('paymentIntegrationColumnExists')) {
    function paymentIntegrationColumnExists(mysqli $con, string $tableName, string $columnName): bool
    {
        $tableSafe = mysqli_real_escape_string($con, $tableName);
        $columnSafe = mysqli_real_escape_string($con, $columnName);
        $result = mysqli_query($con, "SHOW COLUMNS FROM `{$tableSafe}` LIKE '{$columnSafe}'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('paymentIntegrationEnsureSchema')) {
    function paymentIntegrationEnsureSchema(mysqli $con): array
    {
        $messages = [];
        $ok = true;

        if (!paymentIntegrationTableExists($con, 'payment_integrations')) {
            $ok = false;
            $messages[] = 'payment_integrations table is missing.';
        } else {
            $messages[] = 'payment_integrations table exists.';
        }

        if ($ok && paymentIntegrationTableExists($con, 'payment_integrations')) {
            $stripeRow = mysqli_query($con, "SELECT integration_id FROM payment_integrations WHERE provider = 'stripe' LIMIT 1");
            if (!$stripeRow || mysqli_num_rows($stripeRow) === 0) {
                $ok = false;
                $messages[] = 'Stripe integration row is missing.';
            } else {
                $messages[] = 'Stripe integration row exists.';
            }
        }

        if (paymentIntegrationTableExists($con, 'payment')) {
            $requiredColumns = ['m_id', 'payment_for', 'p_amount', 'payment_note'];
            foreach ($requiredColumns as $columnName) {
                if (!paymentIntegrationColumnExists($con, 'payment', $columnName)) {
                    $ok = false;
                    $messages[] = "payment.{$columnName} column is missing.";
                } else {
                    $messages[] = "payment.{$columnName} column exists.";
                }
            }
        } else {
            $ok = false;
            $messages[] = 'payment table is missing.';
        }

        if (!paymentIntegrationTableExists($con, 'membership_plans')) {
            $ok = false;
            $messages[] = 'membership_plans table is missing.';
        } else {
            $messages[] = 'membership_plans table exists.';
        }

        if (!paymentIntegrationTableExists($con, 'membership_transactions')) {
            $ok = false;
            $messages[] = 'membership_transactions table is missing.';
        } else {
            $messages[] = 'membership_transactions table exists.';
            foreach (paymentIntegrationMembershipTransactionColumns() as $columnName) {
                if (!paymentIntegrationColumnExists($con, 'membership_transactions', $columnName)) {
                    $ok = false;
                    $messages[] = "membership_transactions.{$columnName} column is missing.";
                } else {
                    $messages[] = "membership_transactions.{$columnName} column exists.";
                }
            }
        }

        return ['ok' => $ok, 'messages' => $messages];
    }
}

if (!function_exists('paymentIntegrationGetStripeConfig')) {
    function paymentIntegrationGetStripeConfig(mysqli $con): array
    {
        $defaults = [
            'provider' => 'stripe',
            'active_mode' => 'sandbox',
            'is_enabled' => false,
            'is_connected' => false,
            'publishable_key' => '',
            'secret_key' => '',
            'sandbox_publishable_key' => '',
            'sandbox_secret_key' => '',
            'live_publishable_key' => '',
            'live_secret_key' => '',
            'connected_at' => null,
            'updated_at' => null,
        ];

        if (!paymentIntegrationTableExists($con, 'payment_integrations')) {
            return $defaults;
        }

        $query = mysqli_query(
            $con,
            "SELECT * FROM payment_integrations WHERE provider = 'stripe' LIMIT 1"
        );
        if (!$query || mysqli_num_rows($query) === 0) {
            return $defaults;
        }

        $row = mysqli_fetch_assoc($query);
        $activeMode = (($row['active_mode'] ?? 'sandbox') === 'live') ? 'live' : 'sandbox';

        $sandboxPublishable = trim((string) ($row['sandbox_publishable_key'] ?? ''));
        $sandboxSecret = trim((string) ($row['sandbox_secret_key'] ?? ''));
        $livePublishable = trim((string) ($row['live_publishable_key'] ?? ''));
        $liveSecret = trim((string) ($row['live_secret_key'] ?? ''));

        $publishable = $activeMode === 'live' ? $livePublishable : $sandboxPublishable;
        $secret = $activeMode === 'live' ? $liveSecret : $sandboxSecret;

        $connected = ($publishable !== '' && $secret !== '');
        $enabled = ((int) ($row['is_enabled'] ?? 0) === 1) && $connected;

        return [
            'provider' => 'stripe',
            'active_mode' => $activeMode,
            'is_enabled' => $enabled,
            'is_connected' => $connected,
            'publishable_key' => $publishable,
            'secret_key' => $secret,
            'sandbox_publishable_key' => $sandboxPublishable,
            'sandbox_secret_key' => $sandboxSecret,
            'live_publishable_key' => $livePublishable,
            'live_secret_key' => $liveSecret,
            'connected_at' => $row['connected_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }
}

if (!function_exists('paymentIntegrationMaskKey')) {
    function paymentIntegrationMaskKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return 'Not set';
        }
        $len = strlen($key);
        if ($len <= 14) {
            return str_repeat('*', $len);
        }
        return substr($key, 0, 10) . '...' . substr($key, -6);
    }
}

if (!function_exists('paymentIntegrationMembershipTransactionColumns')) {
    function paymentIntegrationMembershipTransactionColumns(): array
    {
        return [
            'mt_id',
            'pay_id',
            'user_id',
            'mp_id',
            'membership_name',
            'billing_plan',
            'amount',
            'start_date',
            'end_date',
            'status',
            'subscribed_at',
            'cancelled_at',
            'cancelled_by_admin_id',
            'created_at',
            'updated_at',
        ];
    }
}

if (!function_exists('paymentIntegrationMembershipTransactionsReady')) {
    function paymentIntegrationMembershipTransactionsReady(mysqli $con): bool
    {
        if (!paymentIntegrationTableExists($con, 'membership_transactions')) {
            return false;
        }
        foreach (paymentIntegrationMembershipTransactionColumns() as $columnName) {
            if (!paymentIntegrationColumnExists($con, 'membership_transactions', $columnName)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('paymentIntegrationCalculateMembershipEndDate')) {
    function paymentIntegrationCalculateMembershipEndDate(string $startDate, string $billingPlan): string
    {
        $date = DateTime::createFromFormat('Y-m-d', $startDate);
        if (!$date) {
            $date = new DateTime();
        }
        if (strtolower(trim($billingPlan)) === 'yearly') {
            $date->modify('+1 year');
        } else {
            $date->modify('+1 month');
        }
        return $date->format('Y-m-d');
    }
}

if (!function_exists('paymentIntegrationNormalizeMembershipStatus')) {
    function paymentIntegrationNormalizeMembershipStatus(string $status, string $endDate): string
    {
        $normalized = strtolower(trim($status));
        if ($normalized === '') {
            $normalized = 'active';
        }

        if ($normalized === 'active') {
            $end = DateTime::createFromFormat('Y-m-d', $endDate);
            if ($end) {
                $today = new DateTime('today');
                if ($end < $today) {
                    return 'expired';
                }
            }
        }

        return $normalized;
    }
}

if (!function_exists('paymentIntegrationMembershipRemainingDays')) {
    function paymentIntegrationMembershipRemainingDays(string $endDate, string $status): int
    {
        $normalized = paymentIntegrationNormalizeMembershipStatus($status, $endDate);
        if ($normalized !== 'active') {
            return 0;
        }

        $end = DateTime::createFromFormat('Y-m-d', $endDate);
        if (!$end) {
            return 0;
        }

        $today = new DateTime('today');
        if ($end < $today) {
            return 0;
        }

        return (int) $today->diff($end)->days;
    }
}
