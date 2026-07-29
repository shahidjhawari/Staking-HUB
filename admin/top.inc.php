<?php
require('connection.inc.php');
require('functions.inc.php');
if (isset($_SESSION['ADMIN_LOGIN']) && $_SESSION['ADMIN_LOGIN'] != '') {
} else {
    header('location:login.php');
    die();
}

$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    ['activate.php', 'Activation Request', 'fa-user-check'],
    ['admin_deposits.php', 'Deposit Request', 'fa-arrow-down'],
    ['admin_staking.php', 'Staking Request', 'fa-layer-group'],
    ['admin.php', 'Daily Earning', 'fa-coins'],
    ['manage_earnings.php', 'Manage Daily Earning', 'fa-chart-line'],
    ['admin_staking_accepted.php', 'Staking Request Accepted', 'fa-check-circle'],
    ['admin_staking_rejected.php', 'Staking Request Rejected', 'fa-times-circle'],
    ['users.php', 'Users', 'fa-users'],
    ['trans_rejected.php', 'Rejected Activation', 'fa-user-times'],
    ['trans_accepted.php', 'Accepted Activation', 'fa-user-check'],
    ['deposit_rejected.php', 'Rejected Deposit', 'fa-times'],
    ['deposit_accepted.php', 'Accepted Deposit', 'fa-check'],
    ['admin_refer_btn.php', 'Refer', 'fa-share-alt'],
    ['process_referral_bonus.php', 'Refer 2', 'fa-share-alt-square'],
    ['admin_add_message.php', 'Dollar Rate', 'fa-dollar-sign'],
    ['admin_manage_payments.php', 'Withdraw Request', 'fa-arrow-up'],
    ['accepted_payments.php', 'Withdraw Request Accepted', 'fa-check-double'],
    ['rejected_payments.php', 'Withdraw Request Rejected', 'fa-ban'],
    ['add_announcement.php', 'Add Announcement', 'fa-bullhorn'],
    ['manage_announcements.php', 'Edit & Delete Announcement', 'fa-edit'],
    ['admin_change_password.php', 'Update Admin Password', 'fa-key'],
    ['admin_view_contacts.php', 'Contact Us', 'fa-envelope'],
    ['toggle_bonus.php', 'Bonus Reward', 'fa-gift'],
    ['admin_signup_settings.php', 'Account Limit', 'fa-user-cog'],
    ['logout.php', 'Logout', 'fa-sign-out-alt'],
];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>StakingHUB Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/logo2.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/glass-admin.css">
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside id="left-panel">
            <a class="navbar-brand" href="index.php">StakingHUB Admin</a>
            <nav class="navbar navbar-expand-lg flex-column align-items-start w-100">
                <div class="navbar-collapse show w-100" id="navbarNav">
                    <ul class="navbar-nav flex-column w-100">
                        <?php foreach ($nav_items as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page === $item[0]) ? 'active' : ''; ?>" href="<?php echo $item[0]; ?>">
                                    <i class="fas <?php echo $item[2]; ?>"></i>
                                    <span><?php echo $item[1]; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="main-panel">
            <div class="admin-topbar">
                <div class="d-flex align-items-center">
                    <button id="sidebarToggle" class="mr-3">
                        <i class="fas fa-bars"></i>
                    </button>
                    <p class="page-title mb-0">Admin Console</p>
                </div>
                <div class="admin-user">
                    <span class="d-none d-sm-inline"><?php echo isset($_SESSION['ADMIN_USERNAME']) ? htmlspecialchars($_SESSION['ADMIN_USERNAME']) : 'Admin'; ?></span>
                    <div class="avatar"><i class="fas fa-user-shield"></i></div>
                </div>
            </div>
