<?php
if (!isset($con)) {
    include_once __DIR__ . '/connect.php';
}
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$global_search_prefill = '';
if (isset($_GET['q'])) {
    $global_search_prefill = trim((string) $_GET['q']);
} elseif (isset($_GET['search'])) {
    $global_search_prefill = trim((string) $_GET['search']);
}

?>
<!-- header and navigation section -->

<header class="header">

    <a href="index.php" class="logo">
        <img src="photos/logoo.png" alt="ClassyCut">
    </a>
    <nav class="menu">
        <a href="index.php">Home</a>
        <a href="service.php">Services</a>
        <a href="eshop.php">E-shop</a>
        <a href="membership.php">Membership</a>
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="appointment.php">Appointment</a>';
        }
        ?>
    </nav>
    <div class="global-search" id="globalSearch">
        <div class="global-search__field">
            <i class="fas fa-search global-search__icon" aria-hidden="true"></i>
            <input
                type="search"
                id="global-search-input"
                class="global-search__input"
                placeholder="Search services & products..."
                autocomplete="off"
                aria-label="Search services and products"
                spellcheck="false"
                value="<?php echo htmlspecialchars($global_search_prefill, ENT_QUOTES); ?>">
            <button
                type="button"
                class="global-search__clear"
                id="global-search-clear"
                aria-label="Clear search">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="global-search__dropdown" id="global-search-dropdown" hidden></div>
    </div>
    <div class="icons">
        <div class="fas fa-search" id="search-btn"></div>
        <div class="fas fa-bars" id="menu-btn"></div>
    </div>
    <?php
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT username FROM user_reg WHERE id = '$user_id'";
        $result = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($result);

        $username = ($row && isset($row['username'])) ? $row['username'] : 'Member';
        echo '<div class="user-profile">';
        echo '<a href="user/index.php"><i class="fas fa-user-circle"></i></a>';
        echo '<a href="user/index.php" class="username">' . htmlspecialchars($username) . '</a>';
        echo '</div>';
    } else {
        echo '<div class="login">';
        echo '<a href="login.php">Sign-In</a>';
        echo '</div>';
    }
    ?>
</header>
<script src="js/script.js"></script>
