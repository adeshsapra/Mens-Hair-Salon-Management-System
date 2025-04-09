<?php

    $_SESSION['appointments'] = [
        ['id' => 1, 'clientName' => 'John Doe', 'service' => 'Haircut', 'date' => '2024-08-24', 'time' => '10:00 AM', 'status' => 'Pending'],
        ['id' => 2, 'clientName' => 'Jane Smith', 'service' => 'Spa', 'date' => '2024-08-25', 'time' => '1:00 PM', 'status' => 'Pending']
       
    ];


// Handle Accept, Decline, or Cancel button clicks
if (isset($_POST['action']) && isset($_POST['id'])) {
    foreach ($_SESSION['appointments'] as $key => &$appointment) {
        if ($appointment['id'] == $_POST['id']) {
            if ($_POST['action'] == 'accept') {
                $appointment['status'] = 'Accepted';
            } elseif ($_POST['action'] == 'decline') {
                //$appointment['status'] = 'Pending';
                unset($_SESSION['appointments'][$key]);
            } elseif ($_POST['action'] == 'cancel') {
                $appointment['status'] = 'Pending';
            }
            break;
        }
    }
    // Re-index the array after any removal
    $_SESSION['appointments'] = array_values($_SESSION['appointments']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Admin Panel - Manage Appointments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Salon Appointments</h1>
        </header>

        <div class="appointment-list">
            <h2>Appointments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['appointments'] as $appointment): ?>
                    <tr class="<?= strtolower($appointment['status']) ?>">
                        <td><?= htmlspecialchars($appointment['clientName']) ?></td>
                        <td><?= htmlspecialchars($appointment['service']) ?></td>
                        <td><?= htmlspecialchars($appointment['date']) ?></td>
                        <td><?= htmlspecialchars($appointment['time']) ?></td>
                        <td><span class="status"><?= htmlspecialchars($appointment['status']) ?></span></td>
                        <td>
                            <?php if ($appointment['status'] == 'Pending'): ?>
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
                                    <button type="submit" name="action" value="accept" class="btn-accept">Accept</button>
                                </form>
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
                                    <button type="submit" name="action" value="decline" class="btn-decline">Decline</button>
                                </form>
                            <?php elseif ($appointment['status'] == 'Accepted'): ?>
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
                                    <button type="submit" name="action" value="cancel" class="btn-cancel">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span>No actions available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
