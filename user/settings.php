<?php 
include 'connect.php';
include 'header.php'; 

?>
<main class="content">
    <div class="header-with-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0;">Account Settings</h1>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px;">
        <!-- Profile Card -->
        <div class="setting-card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); padding: 32px; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div style="background: var(--bg2); color: var(--bg1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; color: var(--bg1); margin: 0;">My Profile</h2>
                    <p style="font-size: 13px; color: #666; margin: 0;">Personal Information</p>
                </div>
            </div>

            <div style="background: #fafafa; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600;">Display Name</label>
                    <p style="font-size: 16px; color: var(--bg1); font-weight: 500; margin: 4px 0 0;"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600;">Email Address</label>
                    <p style="font-size: 16px; color: var(--bg1); font-weight: 500; margin: 4px 0 0;"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                </div>
            </div>

            <a href="edit_profile.php?id=<?php echo $_SESSION["user_id"]; ?>" class="app_more" style="display: block; text-align: center; padding: 12px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>

        <!-- Security Card -->
        <div class="setting-card" style="background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid rgba(0,0,0,0.05); padding: 32px; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <div style="background: #fdf2f2; color: #d93025; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; color: var(--bg1); margin: 0;">Security</h2>
                    <p style="font-size: 13px; color: #666; margin: 0;">Password & Account Safety</p>
                </div>
            </div>

            <p style="color: #666; font-size: 14px; margin-bottom: 24px; line-height: 1.6;">
                Ensure your account is using a strong password. We recommend changing it periodically for better security.
            </p>

            <a href="change_password.php?id=<?php echo $_SESSION["user_id"]; ?>" class="app_more" style="display: block; text-align: center; padding: 12px; border-radius: 8px; font-weight: 600; text-decoration: none; background: var(--bg1); color: var(--brand);">
                <i class="fas fa-key"></i> Change Password
            </a>
        </div>
    </div>
</main>
