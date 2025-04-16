<?php
ob_start();
require('header.php');
?>

<style>
    body {
        background: #f8f9fa;
        color: #333;
    }

    .container {
        margin-top: 50px;
    }

    .alert {
        margin-top: 20px;
    }
</style>

<div class="container">
    <h2 class="text-center">Reset Password</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" maxlength="20">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>