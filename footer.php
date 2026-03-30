<!-- footer sections -->

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-grid">
            <div class="f-section footer-brand" id="company">
                <a href="index.php" class="f-logo">ClassyCut</a>
                <p class="f-text">We believe style is an experience—the perfect blend of luxury and professional expertise. Whether you&rsquo;re here for a cut, a relaxing skin treatment, or a full refresh, we&rsquo;re glad you chose us.</p>
                <div class="media">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="f-section" id="links">
                <h3>Links</h3>
                <nav class="f-menu" aria-label="Footer links">
                    <a href="index.php#about">About</a>
                    <a href="service.php">Services</a>
                    <a href="eshop.php">E-shop</a>
                    <a href="membership.php">Membership</a>
                    <a href="index.php#contact">Contact</a>
                </nav>
            </div>

            <div class="f-section" id="service">
                <h3>Services</h3>
                <nav class="f-menu" aria-label="Services">
                    <a href="service.php">Stylish haircut</a>
                    <a href="service.php">Hair color</a>
                    <a href="service.php">Stylish beard trim</a>
                    <a href="service.php">Beard trim</a>
                    <a href="service.php">Skin treatment</a>
                    <a href="service.php">Spa services</a>
                </nav>
            </div>

            <div class="f-section" id="contact">
                <h3>Contact</h3>
                <div class="footer-contact-list">
                    <a class="detail" href="tel:+917575852866">
                        <span class="detail-icon" aria-hidden="true"><i class="fa fa-phone"></i></span>
                        <span class="detail-text">(+91) 75758 52866</span>
                    </a>
                    <a class="detail" href="mailto:classycut007@gmail.com">
                        <span class="detail-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                        <span class="detail-text">classycut007@gmail.com</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p class="footer-copy">&copy; <?php echo date('Y'); ?> ClassyCut. All rights reserved.</p>
        <p class="footer-credit">Designed by Ak-Developer</p>
    </div>
</footer>

<!-- ========================================== -->
<!-- GLOBAL TOAST & MODAL SYSTEM (Frontend) -->
<!-- ========================================== -->
<style>
/* Global Custom Confirm Modal */
#global-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    z-index: 99999; display: flex; align-items: center; justify-content: center;
    visibility: hidden; opacity: 0; transition: all 0.3s ease;
}
#global-confirm-overlay.show { visibility: visible; opacity: 1; }
.global-confirm-box {
    background: #fff; padding: 30px; border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 350px; text-align: center;
    transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
#global-confirm-overlay.show .global-confirm-box { transform: translateY(0) scale(1); }
.global-confirm-icon { font-size: 40px; color: #f59e0b; margin-bottom: 15px; }
.global-confirm-text { font-size: 18px; color: #333; margin-bottom: 25px; font-weight: 500; }
.global-confirm-actions { display: flex; gap: 15px; justify-content: center; }
.global-confirm-btn {
    padding: 10px 20px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; font-weight: 600; transition: all 0.2s;
}
.gc-cancel { background: #e2e8f0; color: #475569; }
.gc-cancel:hover { background: #cbd5e1; }
.gc-confirm { background: #ef4444; color: #fff; }
.gc-confirm:hover { background: #dc2626; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4); }

/* Global Toast System */
#global-toast-container {
    position: fixed; bottom: 20px; right: 20px; z-index: 100000;
    display: flex; flex-direction: column; gap: 10px;
}
.global-toast {
    min-width: 250px; background: #333; color: #fff; padding: 15px 20px;
    border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500;
    transform: translateX(120%); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.global-toast.show { transform: translateX(0); }
.toast-success { background: #10b981; border-left: 5px solid #059669; }
.toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
.toast-info { background: #3b82f6; border-left: 5px solid #2563eb; }
</style>

<div id="global-confirm-overlay">
    <div class="global-confirm-box">
        <div class="global-confirm-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="global-confirm-text" id="global-confirm-msg">Are you sure?</div>
        <div class="global-confirm-actions">
            <button class="global-confirm-btn gc-cancel" id="gc-cancel-btn">Cancel</button>
            <button class="global-confirm-btn gc-confirm" id="gc-confirm-btn">Yes, I'm sure</button>
        </div>
    </div>
</div>
<div id="global-toast-container"></div>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('global-toast-container');
    const toast = document.createElement('div');
    toast.className = `global-toast toast-${type}`;
    let icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    // Convert PHP static messages to toasts
    document.querySelectorAll('.message, .success, .confirm, .error').forEach(alert => {
        let text = alert.innerText.trim();
        if(text) {
            let type = (alert.classList.contains('message') || alert.classList.contains('error')) ? 'error' : 'success';
            showToast(text, type);
        }
        alert.style.display = 'none';
    });

    // Check for PHP Session toasts
    <?php if (isset($_SESSION['toast-msg'])): ?>
        showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo isset($_SESSION['toast-type']) ? $_SESSION['toast-type'] : 'success'; ?>");
    <?php
    unset($_SESSION['toast-msg']);
    unset($_SESSION['toast-type']);
    endif;
    ?>

    // Check for URL Parameter toasts
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('toast')) {
        let msg = urlParams.get('msg') || 'Action completed successfully!';
        showToast(msg, urlParams.get('toast'));
        window.history.replaceState(null, '', window.location.pathname);
    }
});

let confirmCallback = null;
const confirmOverlay = document.getElementById('global-confirm-overlay');
const confirmMsgElem = document.getElementById('global-confirm-msg');
document.getElementById('gc-cancel-btn').onclick = () => { confirmOverlay.classList.remove('show'); };
document.getElementById('gc-confirm-btn').onclick = () => {
    if(confirmCallback) confirmCallback();
    confirmOverlay.classList.remove('show');
};

document.addEventListener('click', function(e) {
    let el = e.target.closest('[onclick*="confirm("]');
    if (el) {
        let match = el.getAttribute('onclick').match(/confirm\(\s*['"](.*?)['"]\s*\)/);
        if (match) {
            e.preventDefault();
            e.stopImmediatePropagation();
            confirmMsgElem.innerText = match[1];
            confirmCallback = () => {
                el.removeAttribute('onclick');
                if (el.tagName === 'A' && el.href) window.location.href = el.href;
                else el.click();
            };
            confirmOverlay.classList.add('show');
        }
    }
}, true);
</script>
</body>
</html>
