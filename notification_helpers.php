<?php

function notificationTableExists($con)
{
    static $checked = false;
    static $exists = false;

    if ($checked) {
        return $exists;
    }

    $checked = true;
    $result = mysqli_query($con, "SHOW TABLES LIKE 'notifications'");
    $exists = $result && mysqli_num_rows($result) > 0;
    return $exists;
}

function notificationEnsureMigrationsTable($con)
{
    return (bool) mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT(11) NOT NULL AUTO_INCREMENT,
            migration_name VARCHAR(255) NOT NULL,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_migration_name (migration_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}

function notificationSafeValue($value, $allowed, $default)
{
    $value = strtolower(trim((string) $value));
    return in_array($value, $allowed, true) ? $value : $default;
}

function notificationCreate(
    $con,
    $recipientType,
    $recipientId,
    $eventType,
    $title,
    $message,
    $linkUrl = '',
    $actorType = 'system',
    $actorId = 0,
    $entityType = '',
    $entityId = 0
) {
    if (!notificationTableExists($con)) {
        return false;
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $actorType = notificationSafeValue($actorType, ['user', 'admin', 'system'], 'system');

    $recipientId = (int) $recipientId;
    if ($recipientId <= 0) {
        return false;
    }

    $actorId = (int) $actorId;
    if ($actorId <= 0) {
        $actorId = null;
    }

    $entityId = (int) $entityId;
    if ($entityId <= 0) {
        $entityId = null;
    }

    $eventType = substr(trim((string) $eventType), 0, 80);
    $title = substr(trim((string) $title), 0, 180);
    $message = trim((string) $message);
    $linkUrl = substr(trim((string) $linkUrl), 0, 255);
    $entityType = substr(trim((string) $entityType), 0, 60);

    if ($eventType === '' || $title === '' || $message === '') {
        return false;
    }

    $query = "INSERT INTO notifications (
        recipient_type,
        recipient_id,
        actor_type,
        actor_id,
        event_type,
        title,
        message,
        link_url,
        entity_type,
        entity_id,
        is_read,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sisssssssi',
        $recipientType,
        $recipientId,
        $actorType,
        $actorId,
        $eventType,
        $title,
        $message,
        $linkUrl,
        $entityType,
        $entityId
    );

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return (bool) $ok;
}

function notificationCreateForUser(
    $con,
    $userId,
    $eventType,
    $title,
    $message,
    $linkUrl = '',
    $actorType = 'system',
    $actorId = 0,
    $entityType = '',
    $entityId = 0
) {
    return notificationCreate(
        $con,
        'user',
        (int) $userId,
        $eventType,
        $title,
        $message,
        $linkUrl,
        $actorType,
        (int) $actorId,
        $entityType,
        (int) $entityId
    );
}

function notificationCreateForAdmin(
    $con,
    $adminId,
    $eventType,
    $title,
    $message,
    $linkUrl = '',
    $actorType = 'system',
    $actorId = 0,
    $entityType = '',
    $entityId = 0
) {
    return notificationCreate(
        $con,
        'admin',
        (int) $adminId,
        $eventType,
        $title,
        $message,
        $linkUrl,
        $actorType,
        (int) $actorId,
        $entityType,
        (int) $entityId
    );
}

function notificationCreateForAllAdmins(
    $con,
    $eventType,
    $title,
    $message,
    $linkUrl = '',
    $actorType = 'system',
    $actorId = 0,
    $entityType = '',
    $entityId = 0
) {
    if (!notificationTableExists($con)) {
        return false;
    }

    $adminIdColumn = 'admin_id';
    $hasAdminId = mysqli_query($con, "SHOW COLUMNS FROM admin LIKE 'admin_id'");
    if (!$hasAdminId || mysqli_num_rows($hasAdminId) === 0) {
        $hasId = mysqli_query($con, "SHOW COLUMNS FROM admin LIKE 'id'");
        if ($hasId && mysqli_num_rows($hasId) > 0) {
            $adminIdColumn = 'id';
        } else {
            return false;
        }
    }

    $admins = mysqli_query($con, "SELECT {$adminIdColumn} AS admin_id FROM admin");
    if (!$admins) {
        return false;
    }

    $created = false;
    while ($row = mysqli_fetch_assoc($admins)) {
        $adminId = (int) ($row['admin_id'] ?? 0);
        if ($adminId <= 0) {
            continue;
        }
        $ok = notificationCreateForAdmin(
            $con,
            $adminId,
            $eventType,
            $title,
            $message,
            $linkUrl,
            $actorType,
            $actorId,
            $entityType,
            $entityId
        );
        if ($ok) {
            $created = true;
        }
    }

    return $created;
}

function notificationGetUnreadCount($con, $recipientType, $recipientId)
{
    if (!notificationTableExists($con)) {
        return 0;
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $recipientId = (int) $recipientId;
    if ($recipientId <= 0) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $con,
        "SELECT COUNT(*) FROM notifications WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0"
    );
    if (!$stmt) {
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'si', $recipientType, $recipientId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int) $count;
}

function notificationFetchRows($stmt)
{
    $rows = [];

    if (function_exists('mysqli_stmt_get_result')) {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    $meta = mysqli_stmt_result_metadata($stmt);
    if (!$meta) {
        return $rows;
    }

    $fields = [];
    $row = [];
    $bindRefs = [];
    while ($field = mysqli_fetch_field($meta)) {
        $fields[] = $field->name;
        $row[$field->name] = null;
        $bindRefs[] = &$row[$field->name];
    }
    call_user_func_array([$stmt, 'bind_result'], $bindRefs);

    while (mysqli_stmt_fetch($stmt)) {
        $rowCopy = [];
        foreach ($fields as $field) {
            $rowCopy[$field] = $row[$field];
        }
        $rows[] = $rowCopy;
    }

    return $rows;
}

function notificationGetRecent($con, $recipientType, $recipientId, $limit = 4)
{
    if (!notificationTableExists($con)) {
        return [];
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $recipientId = (int) $recipientId;
    $limit = max(1, min(20, (int) $limit));

    if ($recipientId <= 0) {
        return [];
    }

    $stmt = mysqli_prepare(
        $con,
        "SELECT notification_id, event_type, title, message, link_url, is_read, created_at
         FROM notifications
         WHERE recipient_type = ? AND recipient_id = ?
         ORDER BY created_at DESC, notification_id DESC
         LIMIT ?"
    );
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'sii', $recipientType, $recipientId, $limit);
    mysqli_stmt_execute($stmt);
    $rows = notificationFetchRows($stmt);
    mysqli_stmt_close($stmt);
    return $rows;
}

function notificationCountAll($con, $recipientType, $recipientId)
{
    if (!notificationTableExists($con)) {
        return 0;
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $recipientId = (int) $recipientId;
    if ($recipientId <= 0) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $con,
        "SELECT COUNT(*) FROM notifications WHERE recipient_type = ? AND recipient_id = ?"
    );
    if (!$stmt) {
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 'si', $recipientType, $recipientId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int) $count;
}

function notificationGetPaginated($con, $recipientType, $recipientId, $limit = 10, $offset = 0)
{
    if (!notificationTableExists($con)) {
        return [];
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $recipientId = (int) $recipientId;
    $limit = max(1, min(100, (int) $limit));
    $offset = max(0, (int) $offset);

    if ($recipientId <= 0) {
        return [];
    }

    $stmt = mysqli_prepare(
        $con,
        "SELECT notification_id, event_type, title, message, link_url, is_read, created_at
         FROM notifications
         WHERE recipient_type = ? AND recipient_id = ?
         ORDER BY created_at DESC, notification_id DESC
         LIMIT ? OFFSET ?"
    );
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'siii', $recipientType, $recipientId, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $rows = notificationFetchRows($stmt);
    mysqli_stmt_close($stmt);
    return $rows;
}

function notificationMarkAllAsRead($con, $recipientType, $recipientId)
{
    if (!notificationTableExists($con)) {
        return false;
    }

    $recipientType = notificationSafeValue($recipientType, ['user', 'admin'], 'user');
    $recipientId = (int) $recipientId;

    if ($recipientId <= 0) {
        return false;
    }

    $stmt = mysqli_prepare(
        $con,
        "UPDATE notifications
         SET is_read = 1, read_at = NOW()
         WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0"
    );
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'si', $recipientType, $recipientId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return (bool) $ok;
}

function notificationFormatTimeAgo($datetime)
{
    $timestamp = strtotime((string) $datetime);
    if (!$timestamp) {
        return '';
    }

    $diff = time() - $timestamp;
    if ($diff < 0) {
        return 'just now';
    }

    if ($diff < 60) {
        return $diff . 's ago';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . 'd ago';
    }
    if ($diff < 2592000) {
        return floor($diff / 604800) . 'w ago';
    }

    return date('d M Y, h:i A', $timestamp);
}

function notificationResolveLink($linkUrl)
{
    $linkUrl = trim((string) $linkUrl);
    if ($linkUrl === '' || $linkUrl === '#') {
        return '#';
    }

    if (preg_match('/^(https?:|mailto:|tel:)/i', $linkUrl)) {
        return $linkUrl;
    }

    $clean = ltrim($linkUrl, '/');
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';

    if (strpos($scriptName, '/user/') !== false || strpos($scriptName, '/admin/') !== false) {
        return '../' . $clean;
    }

    return $clean;
}
