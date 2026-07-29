<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require('connection.inc.php');

// Check if user_id and user_name are set in the session
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;

$user_rewards = null;
$transaction_status = null;
$deposit_status = null;
$wallet_balance = 0;

if ($user_id) {
    // Fetch user-specific data
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_rewards = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch the user's transaction status
    $stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $transaction_status_row = $stmt->get_result()->fetch_assoc();
    $transaction_status = $transaction_status_row['status'] ?? null;
    $stmt->close();

    // Fetch the latest deposit status
    $stmt = $conn->prepare("SELECT status FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $deposit_status_row = $stmt->get_result()->fetch_assoc();
    $deposit_status = $deposit_status_row['status'] ?? null;
    $stmt->close();

    // Calculate the wallet balance (sum of accepted deposits)
    $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet_balance_row = $stmt->get_result()->fetch_assoc();
    $wallet_balance = $wallet_balance_row['wallet_balance'] ?? 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StakingHUB</title>
    <link rel="icon" href="img/logo2.png" type="image/x-icon">
    <meta name="description" content="Invest your money on staking and get 3x & get user join with referral reward and get lots of rewards">
    <meta name="keywords" content="stakinghub, stackinghub, staking hub, stacking hub, staking, stacking, stake hub, stack hub, jhawarian website, sargodha website, investment website, online earning, reward website, staking hub website, stakinghub website, shahid iqbal, nawab academy, nawab">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Glass Theme -->
    <link href="assets/css/glass-theme.css" rel="stylesheet">
</head>

<body>
    <?php if ($user_id && $user_name): ?>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                    <img src="img/logo2.png" width="36" alt="StakingHUB">
                    <span class="ml-2 text-gradient font-weight-bold" style="font-family:var(--font-display);font-size:1.1rem;">StakingHUB</span>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars" style="color: var(--text-1);"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php"><i class="fas fa-user mr-1"></i> My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="one_dollar.php"><i class="fas fa-dice mr-1"></i> 1$ Game</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-wallet mr-1"></i> Wallet</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="transactionsDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-exchange-alt mr-1"></i> Transactions
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="transactionsDropdown">
                                <?php if ($transaction_status === 'accepted') : ?>
                                    <li><a class="dropdown-item" href="deposit.php">Deposit</a></li>
                                <?php elseif ($transaction_status === 'rejected') : ?>
                                    <li>
                                        <p class="dropdown-item-text mb-0 small">Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                    </li>
                                    <li><a href="activate.php" class="dropdown-item">Resend Activation Request</a></li>
                                <?php elseif ($transaction_status === 'pending') : ?>
                                    <li>
                                        <p class="dropdown-item-text mb-0 small">Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                    </li>
                                <?php elseif (!$transaction_status) : ?>
                                    <li>
                                        <p class="mb-0"><a href="activate.php" class="dropdown-item">Activate Account</a></p>
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="user_payment.php">Withdrawal</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="stackingDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-layer-group mr-1"></i> Staking
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="stackingDropdown">
                                <li><a class="dropdown-item" href="staking.php">Staking</a></li>
                                <li><a class="dropdown-item" href="sendamount.php">P2P Transfer</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stacking_dummy.php"><i class="fas fa-chart-line mr-1"></i> Daily Earning</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="teamDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-users mr-1"></i> Team Building
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="teamDropdown">
                                <li><a class="dropdown-item" href="team_member.php">Team Building Member</a></li>
                                <li><a class="dropdown-item" href="team_earning.php">Team Building Earning</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bonus_rewards.php"><i class="fas fa-gift mr-1"></i> Bonus Reward</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php"><i class="fas fa-headset mr-1"></i> Contact Us</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="telegram-container">
            <a href="#" class="telegram-icon" id="telegramIcon">
                <i class="fab fa-telegram-plane"></i>
            </a>
            <div class="extra-icons" id="extraIcons">
                <a href="https://t.me/Staking_HUB" class="telegram-icon" target="_blank">
                    <i class="fab fa-telegram-plane"></i>
                    <span class="icon-label">Support</span>
                </a>
                <a href="https://t.me/+CANG0BzUsWM2NmFk" class="profile-icon" target="_blank">
                    <i class="fas fa-user"></i>
                    <span class="icon-label">Community Group</span>
                </a>
            </div>
        </div>

        <script>
            document.getElementById('telegramIcon').addEventListener('click', function() {
                var extraIcons = document.getElementById('extraIcons');
                if (extraIcons.style.display === 'none' || extraIcons.style.display === '') {
                    extraIcons.style.display = 'flex';
                    setTimeout(function() {
                        extraIcons.style.display = 'none';
                    }, 3000);
                } else {
                    extraIcons.style.display = 'none';
                }
            });
        </script>
    <?php endif; ?>
    <main>
