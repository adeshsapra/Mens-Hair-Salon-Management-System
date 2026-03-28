<?php
include 'connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

function getMysqldumpBinary()
{
    $candidates = array(
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        'mysqldump'
    );

    foreach ($candidates as $candidate) {
        if ($candidate === 'mysqldump' || file_exists($candidate)) {
            return $candidate;
        }
    }

    return 'mysqldump';
}

$backup_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'classycut';

    $backup_directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'backups';

    if (!is_dir($backup_directory) && !mkdir($backup_directory, 0755, true)) {
        $backup_error = 'Unable to create backup directory.';
    } else {
        $backup_filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_file_path = $backup_directory . DIRECTORY_SEPARATOR . $backup_filename;
        $mysqldump_binary = getMysqldumpBinary();

        $command_parts = array(
            escapeshellarg($mysqldump_binary),
            '--host=' . escapeshellarg($db_host),
            '--user=' . escapeshellarg($db_user)
        );

        if ($db_pass !== '') {
            $command_parts[] = '--password=' . escapeshellarg($db_pass);
        }

        $command_parts[] = '--single-transaction';
        $command_parts[] = '--routines';
        $command_parts[] = '--events';
        $command_parts[] = '--triggers';
        $command_parts[] = '--add-drop-table';
        $command_parts[] = '--default-character-set=utf8mb4';
        $command_parts[] = '--databases';
        $command_parts[] = escapeshellarg($db_name);
        $command_parts[] = '--result-file=' . escapeshellarg($backup_file_path);

        $command_output = array();
        $exit_code = 0;
        $command = implode(' ', $command_parts) . ' 2>&1';
        exec($command, $command_output, $exit_code);

        if ($exit_code !== 0 || !file_exists($backup_file_path) || filesize($backup_file_path) === 0) {
            if (file_exists($backup_file_path)) {
                unlink($backup_file_path);
            }

            $error_detail = trim(implode("\n", $command_output));
            $backup_error = 'Database backup failed.';
            if ($error_detail !== '') {
                $backup_error .= ' Error: ' . htmlspecialchars($error_detail);
            }
        } else {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
            header('Content-Length: ' . filesize($backup_file_path));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($backup_file_path);
            exit();
        }
    }
}

include 'header.php';
require_once 'page_header_helper.php';
?>

<style>
    .backup-page .backup-content {
        padding: 26px;
    }

    .backup-page .backup-panel {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 14px;
        flex-wrap: wrap;
    }

    .backup-page .backup-action-form {
        display: inline-flex !important;
        flex-direction: row !important;
        margin: 0;
    }

    .backup-page .backup-create-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-width: 240px;
        height: 46px;
        border: none !important;
        border-radius: 10px;
        background: var(--brand) !important;
        color: var(--bg1) !important;
        box-shadow: 0 8px 18px rgba(24, 21, 13, 0.2);
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.2px;
        text-transform: none !important;
        cursor: pointer;
    }

    .backup-page .backup-create-btn:hover {
        background: var(--bg1) !important;
        color: var(--bg2) !important;
        transform: translateY(-1px);
    }

    .backup-page .backup-create-btn i,
    .backup-page .backup-create-btn span {
        text-transform: none !important;
    }

    .backup-page .backup-note {
        margin: 0;
        color: #4b5563;
        font-size: 14px;
        font-weight: 500;
        text-transform: none;
    }

    .backup-page .backup-note code {
        background: #f3f4f6;
        padding: 3px 7px;
        border-radius: 5px;
        text-transform: none;
    }

    @media (max-width: 768px) {
        .backup-page .backup-content {
            padding: 18px;
        }

        .backup-page .backup-panel {
            align-items: stretch;
        }

        .backup-page .backup-action-form {
            width: 100%;
        }

        .backup-page .backup-create-btn {
            width: 100%;
        }
    }
</style>

<?php
renderAdminPageIntro(
    'Database Backup',
    'Backup & Recovery',
    'Generate a fresh SQL export to secure your latest application data and recovery point.'
);
?>

<div class="main-content backup-page">
    <div class="content backup-content">
        <?php if ($backup_error !== '') { ?>
            <div class="backup-error">
                <?php echo $backup_error; ?>
            </div>
        <?php } ?>

        <div class="backup-panel">
            <form method="post" class="backup-action-form">
                <button type="submit" name="create_backup" class="backup-create-btn">
                    <i class="fas fa-download"></i>
                    <span>Create Backup (.sql)</span>
                </button>
            </form>
            <p class="backup-note">
                Backup files are stored in <code>database/backups</code>.
            </p>
        </div>
    </div>
</div>

</body>
</html>
