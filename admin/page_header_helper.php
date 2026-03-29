<?php
if (!function_exists('renderAdminPageIntro')) {
    function renderAdminPageIntro($menu, $title, $description)
    {
        ?>
        <div class="main-content">
            <div class="content page-title-card">
                <?php if (!empty($menu)) { ?>
                    <span class="page-title-menu"><?php echo htmlspecialchars($menu, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
                <h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <?php
    }
}
?>
