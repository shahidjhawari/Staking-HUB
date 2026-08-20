<?php
ob_start();
require('header.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);                
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); 
        return $data;
    }

    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    $emailError = "";
    $passwordError = "";

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $name);
        $stmt->fetch();

        if (!password_verify($password, $hashed_password)) {
            $passwordError = "Invalid password.";
        }

        if (empty($passwordError)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['login_time'] = time(); // Store the login time
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $emailError = "Invalid email.";
    }
    $stmt->close();
}
?>

<div class="auth-shell">
    <div class="auth-card">
        <div class="logo-wrap">
            <img src="img/logo2.png" alt="StakingHUB" width="64">
        </div>
        <h4 class="text-center mb-1">Welcome back</h4>
        <p class="auth-subtitle">Log in to manage your stake and earnings</p>
        <?php
        if (!empty($emailError)) {
            echo '<div class="alert alert-danger py-2 px-3 mb-3" data-cy="login-error">' . $emailError . '</div>';
        }
        if (!empty($passwordError)) {
            echo '<div class="alert alert-danger py-2 px-3 mb-3" data-cy="login-error">' . $passwordError . '</div>';
        }
        ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required data-cy="login-email">
            </div>
            <div class="form-group password-container">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required data-cy="login-password">
                <i class="fas fa-eye toggle-password" data-target="password"></i>
            </div>
            <div class="form-group text-right mb-3">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block" data-cy="login-submit">Log In</button>
        </form>
        <div class="text-center mt-4" style="color:var(--text-3);">
            <p class="mb-0">Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            const target = document.getElementById(this.getAttribute('data-target'));
            if (target.getAttribute('type') === 'password') {
                target.setAttribute('type', 'text');
                this.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.setAttribute('type', 'password');
                this.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>

<?php require('footer.php'); ?>