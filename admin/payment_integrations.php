<?php
include 'header.php';
include 'connect.php';
require_once 'page_header_helper.php';
require_once __DIR__ . '/../payment_integration_helpers.php';

$setupResult = paymentIntegrationEnsureSchema($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['disable_stripe'])) {
        $disableQuery = mysqli_query($con, "UPDATE payment_integrations SET is_enabled = 0 WHERE provider = 'stripe'");
        if ($disableQuery) {
            $_SESSION['toast-type'] = 'success';
            $_SESSION['toast-msg'] = 'Stripe has been disabled successfully.';
        } else {
            $_SESSION['toast-type'] = 'error';
            $_SESSION['toast-msg'] = 'Failed to disable Stripe: ' . mysqli_error($con);
        }
        header('Location: payment_integrations.php');
        exit();
    }

    if (isset($_POST['save_stripe_keys'])) {
        $mode = (($_POST['stripe_mode'] ?? 'sandbox') === 'live') ? 'live' : 'sandbox';
        $publishableInput = trim((string) ($_POST['publishable_key'] ?? ''));
        $secretInput = trim((string) ($_POST['secret_key'] ?? ''));

        $currentConfig = paymentIntegrationGetStripeConfig($con);
        $existingPublishable = $mode === 'live'
            ? (string) $currentConfig['live_publishable_key']
            : (string) $currentConfig['sandbox_publishable_key'];
        $existingSecret = $mode === 'live'
            ? (string) $currentConfig['live_secret_key']
            : (string) $currentConfig['sandbox_secret_key'];

        $publishable = $publishableInput !== '' ? $publishableInput : $existingPublishable;
        $secret = $secretInput !== '' ? $secretInput : $existingSecret;

        $errors = [];
        if ($publishable === '' || $secret === '') {
            $errors[] = 'Publishable key and secret key are required for the selected mode.';
        }

        if ($mode === 'sandbox') {
            if ($publishable !== '' && strpos($publishable, 'pk_test_') !== 0) {
                $errors[] = 'Sandbox publishable key should start with pk_test_.';
            }
            if ($secret !== '' && strpos($secret, 'sk_test_') !== 0) {
                $errors[] = 'Sandbox secret key should start with sk_test_.';
            }
        } else {
            if ($publishable !== '' && strpos($publishable, 'pk_live_') !== 0) {
                $errors[] = 'Live publishable key should start with pk_live_.';
            }
            if ($secret !== '' && strpos($secret, 'sk_live_') !== 0) {
                $errors[] = 'Live secret key should start with sk_live_.';
            }
        }

        if (empty($errors)) {
            $publishableColumn = $mode === 'live' ? 'live_publishable_key' : 'sandbox_publishable_key';
            $secretColumn = $mode === 'live' ? 'live_secret_key' : 'sandbox_secret_key';

            $sql = "
                UPDATE payment_integrations
                SET {$publishableColumn} = ?,
                    {$secretColumn} = ?,
                    active_mode = ?,
                    is_enabled = 1,
                    connected_at = NOW()
                WHERE provider = 'stripe'
            ";
            $stmt = mysqli_prepare($con, $sql);

            if (!$stmt) {
                $_SESSION['toast-type'] = 'error';
                $_SESSION['toast-msg'] = 'Failed to prepare update: ' . mysqli_error($con);
            } else {
                mysqli_stmt_bind_param($stmt, 'sss', $publishable, $secret, $mode);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['toast-type'] = 'success';
                    $_SESSION['toast-msg'] = 'Stripe connected successfully in ' . strtoupper($mode) . ' mode.';
                } else {
                    $_SESSION['toast-type'] = 'error';
                    $_SESSION['toast-msg'] = 'Failed to save Stripe keys: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            }

            header('Location: payment_integrations.php');
            exit();
        }

        $_SESSION['toast-type'] = 'error';
        $_SESSION['toast-msg'] = implode(' ', $errors);
        header('Location: payment_integrations.php');
        exit();
    }
}

$stripeConfig = paymentIntegrationGetStripeConfig($con);
$activeMode = $stripeConfig['active_mode'] === 'live' ? 'live' : 'sandbox';
$enabled = (bool) $stripeConfig['is_enabled'];
$connected = (bool) $stripeConfig['is_connected'];

$sandboxPublishableRaw = (string) ($stripeConfig['sandbox_publishable_key'] ?? '');
$sandboxSecretRaw = (string) ($stripeConfig['sandbox_secret_key'] ?? '');
$livePublishableRaw = (string) ($stripeConfig['live_publishable_key'] ?? '');
$liveSecretRaw = (string) ($stripeConfig['live_secret_key'] ?? '');
$currentPublishableRaw = $activeMode === 'live' ? $livePublishableRaw : $sandboxPublishableRaw;
$currentSecretRaw = $activeMode === 'live' ? $liveSecretRaw : $sandboxSecretRaw;

$activePublishable = paymentIntegrationMaskKey((string) $stripeConfig['publishable_key']);
$activeSecret = paymentIntegrationMaskKey((string) $stripeConfig['secret_key']);
$sandboxPublishable = paymentIntegrationMaskKey($sandboxPublishableRaw);
$livePublishable = paymentIntegrationMaskKey($livePublishableRaw);
?>
<style>
        .integrations-shell {
            margin-left: 250px;
            padding: 0 20px 20px;
        }
        .integration-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .integration-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(24, 21, 13, 0.08);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
            padding: 22px;
        }
        .integration-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
        }
        .integration-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .integration-title h2 {
            margin: 0;
            color: #18150d;
        }
        .integration-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        .tag-connected {
            background: #e8f7ee;
            color: #157347;
        }
        .tag-disconnected {
            background: #fdecea;
            color: #b02a37;
        }
        .integration-meta {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 10px;
        }
        .integration-meta-item {
            background: #f8f9fb;
            border: 1px solid #eceef3;
            border-radius: 10px;
            padding: 12px;
            min-height: 82px;
        }
        .integration-meta-item .label {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .integration-meta-item .value {
            margin-top: 6px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
            font-family: Consolas, "Courier New", monospace;
            text-transform: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .integration-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .integration-btn {
            border: none;
            border-radius: 10px;
            cursor: pointer;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
        }
        .integration-btn.primary {
            background: #18150d;
            color: #eae3c2;
        }
        .integration-btn.primary:hover {
            background: #cbb90f;
            color: #18150d;
        }
        .integration-btn.danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .integration-btn.danger:hover {
            background: #b91c1c;
            color: #fff;
            border-color: #b91c1c;
        }
        .inline-form {
            margin: 0;
            display: inline;
        }
        .integration-hint {
            margin: 12px 0 0;
            color: #555;
            font-size: 13px;
            text-transform: none;
        }
        .migration-note {
            margin-top: 14px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #f7d7a8;
            background: #fff8ec;
            color: #7a4a00;
            font-size: 13px;
            text-transform: none;
            line-height: 1.45;
        }

        .integration-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 120000;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 14px;
        }
        .integration-modal-overlay.show {
            display: flex;
        }
        .integration-modal {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 25px 70px rgba(15, 23, 42, 0.35);
            overflow: hidden;
        }
        .integration-modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .integration-modal-header h3 {
            margin: 0;
            color: #0f172a;
        }
        .integration-close {
            border: none;
            background: transparent;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
        }
        .integration-modal-body {
            padding: 18px 20px 22px;
        }
        .mode-switch {
            display: inline-flex;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 4px;
            gap: 4px;
            margin-bottom: 16px;
            background: #f8fafc;
        }
        .mode-btn {
            border: none;
            border-radius: 999px;
            background: transparent;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 14px;
            cursor: pointer;
        }
        .mode-btn.active {
            background: #18150d;
            color: #eae3c2;
        }
        .integration-form-group {
            margin-bottom: 12px;
        }
        .integration-form-group label {
            display: block;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .integration-form-group input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
            text-transform: none;
        }
        .integration-form-group input:focus {
            outline: none;
            border-color: #cbb90f;
            box-shadow: 0 0 0 3px rgba(203, 185, 15, 0.15);
        }
        .integration-form-note {
            margin: 0 0 14px;
            font-size: 13px;
            color: #64748b;
            text-transform: none;
        }
        .integration-form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        @media (max-width: 1080px) {
            .integration-meta {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .integrations-shell {
                margin-left: 0;
                padding: 0 10px 16px;
            }
        }
</style>
<?php
renderAdminPageIntro(
    'Payments',
    'Payment Integrations',
    'Connect Stripe in sandbox or live mode and use the same keys for product and membership payments.'
);
?>

<div class="integrations-shell">
    <div class="integration-grid">
        <section class="integration-card">
            <div class="integration-top">
                <div class="integration-title">
                    <i class="fab fa-stripe-s" style="font-size: 28px; color: #635bff;"></i>
                    <h2>Stripe</h2>
                    <span class="integration-tag <?php echo $enabled ? 'tag-connected' : 'tag-disconnected'; ?>">
                        <?php echo $enabled ? 'Connected' : 'Not Connected'; ?>
                    </span>
                </div>
                <span class="integration-tag <?php echo $activeMode === 'live' ? 'tag-connected' : 'tag-disconnected'; ?>">
                    <?php echo strtoupper($activeMode); ?> MODE
                </span>
            </div>

            <div class="integration-meta">
                <div class="integration-meta-item">
                    <p class="label">Active Publishable Key</p>
                    <p class="value" title="<?php echo htmlspecialchars($activePublishable); ?>"><?php echo htmlspecialchars($activePublishable); ?></p>
                </div>
                <div class="integration-meta-item">
                    <p class="label">Active Secret Key</p>
                    <p class="value" title="<?php echo htmlspecialchars($activeSecret); ?>"><?php echo htmlspecialchars($activeSecret); ?></p>
                </div>
                <div class="integration-meta-item">
                    <p class="label">Sandbox Publishable</p>
                    <p class="value" title="<?php echo htmlspecialchars($sandboxPublishable); ?>"><?php echo htmlspecialchars($sandboxPublishable); ?></p>
                </div>
                <div class="integration-meta-item">
                    <p class="label">Live Publishable</p>
                    <p class="value" title="<?php echo htmlspecialchars($livePublishable); ?>"><?php echo htmlspecialchars($livePublishable); ?></p>
                </div>
            </div>

            <div class="integration-actions">
                <button type="button" class="integration-btn primary" id="open-stripe-modal">
                    Connect Account
                </button>
                <form method="POST" class="inline-form">
                    <button type="submit" name="disable_stripe" class="integration-btn danger" onclick="return confirm('Disable Stripe for checkout?');">
                        Disable Stripe
                    </button>
                </form>
            </div>

            <p class="integration-hint">
                Connect with sandbox keys to test payments, then switch to live keys for real customer transactions.
            </p>

            <?php if (!$setupResult['ok']): ?>
                <div class="migration-note">
                    <?php echo htmlspecialchars(implode(' ', $setupResult['messages'])); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="integration-modal-overlay" id="stripe-modal">
    <div class="integration-modal">
        <div class="integration-modal-header">
            <h3>Connect Stripe Account</h3>
            <button type="button" class="integration-close" id="close-stripe-modal">&times;</button>
        </div>
        <div class="integration-modal-body">
            <form method="POST" id="stripe-connect-form">
                <input type="hidden" name="save_stripe_keys" value="1">
                <input type="hidden" name="stripe_mode" id="stripe_mode" value="<?php echo htmlspecialchars($activeMode); ?>">

                <div class="mode-switch" role="group" aria-label="Stripe mode">
                    <button type="button" class="mode-btn <?php echo $activeMode === 'sandbox' ? 'active' : ''; ?>" data-mode="sandbox">Sandbox</button>
                    <button type="button" class="mode-btn <?php echo $activeMode === 'live' ? 'active' : ''; ?>" data-mode="live">Live</button>
                </div>

                <p class="integration-form-note" id="mode-helper-text">
                    <?php echo $activeMode === 'live'
                        ? 'Live mode selected. Use your production Stripe keys.'
                        : 'Sandbox mode selected. Use Stripe test keys for trial payments.'; ?>
                </p>

                <div class="integration-form-group">
                    <label for="publishable_key">Publishable Key</label>
                    <input
                        type="text"
                        id="publishable_key"
                        name="publishable_key"
                        value="<?php echo htmlspecialchars($currentPublishableRaw); ?>"
                        placeholder="<?php echo $activeMode === 'live' ? 'pk_live_...' : 'pk_test_...'; ?>"
                        autocomplete="off"
                    >
                </div>

                <div class="integration-form-group">
                    <label for="secret_key">Secret Key</label>
                    <input
                        type="password"
                        id="secret_key"
                        name="secret_key"
                        value="<?php echo htmlspecialchars($currentSecretRaw); ?>"
                        placeholder="<?php echo $activeMode === 'live' ? 'sk_live_...' : 'sk_test_...'; ?>"
                        autocomplete="off"
                    >
                </div>

                <div class="integration-form-actions">
                    <button type="button" class="integration-btn danger" id="cancel-stripe-modal">Cancel</button>
                    <button type="submit" class="integration-btn primary" id="save-stripe-btn">
                        <?php echo $currentPublishableRaw !== '' && $currentSecretRaw !== '' ? 'Update & Connect' : 'Save & Connect'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const overlay = document.getElementById('stripe-modal');
        const openBtn = document.getElementById('open-stripe-modal');
        const closeBtn = document.getElementById('close-stripe-modal');
        const cancelBtn = document.getElementById('cancel-stripe-modal');
        const modeInput = document.getElementById('stripe_mode');
        const publishableInput = document.getElementById('publishable_key');
        const secretInput = document.getElementById('secret_key');
        const helperText = document.getElementById('mode-helper-text');
        const saveBtn = document.getElementById('save-stripe-btn');
        const savedKeys = {
            sandbox: {
                publishable: <?php echo json_encode($sandboxPublishableRaw, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                secret: <?php echo json_encode($sandboxSecretRaw, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
            },
            live: {
                publishable: <?php echo json_encode($livePublishableRaw, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                secret: <?php echo json_encode($liveSecretRaw, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
            }
        };

        function refreshSubmitLabel(mode) {
            const hasExisting = (savedKeys[mode]?.publishable || '') !== '' && (savedKeys[mode]?.secret || '') !== '';
            saveBtn.textContent = hasExisting ? 'Update & Connect' : 'Save & Connect';
        }

        function closeModal() {
            overlay.classList.remove('show');
        }

        function openModal() {
            setMode(modeInput.value || 'sandbox');
            overlay.classList.add('show');
        }

        function setMode(mode) {
            document.querySelectorAll('.mode-btn').forEach(btn => {
                const isActive = btn.getAttribute('data-mode') === mode;
                btn.classList.toggle('active', isActive);
            });
            modeInput.value = mode;

            if (mode === 'live') {
                publishableInput.placeholder = 'pk_live_...';
                secretInput.placeholder = 'sk_live_...';
                helperText.textContent = 'Live mode selected. Use your production Stripe keys.';
            } else {
                publishableInput.placeholder = 'pk_test_...';
                secretInput.placeholder = 'sk_test_...';
                helperText.textContent = 'Sandbox mode selected. Use Stripe test keys for trial payments.';
            }

            publishableInput.value = savedKeys[mode]?.publishable || '';
            secretInput.value = savedKeys[mode]?.secret || '';
            refreshSubmitLabel(mode);
        }

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        overlay.addEventListener('click', function(event) {
            if (event.target === overlay) {
                closeModal();
            }
        });

        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                setMode(this.getAttribute('data-mode'));
            });
        });
    })();
</script>
