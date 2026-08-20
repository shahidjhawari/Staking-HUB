<?php
require('connection.inc.php');
require('functions.inc.php');
$msg='';
if(isset($_POST['submit'])){
	$username=get_safe_value($con,$_POST['username']);
	$password=get_safe_value($con,$_POST['password']);
	$sql="select * from admin_users where username='$username' and password='$password'";
	$res=mysqli_query($con,$sql);
	$count=mysqli_num_rows($res);
	if($count>0){
		$_SESSION['ADMIN_LOGIN']='yes';
		$_SESSION['ADMIN_USERNAME']=$username;
		header('location:categories.php');
		die();
	}else{
		$msg="Please enter correct login details";	
	}
	
}
?>
<!doctype html>
<html class="no-js" lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Admin Login | StakingHUB</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="icon" href="../img/logo2.png" type="image/x-icon">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="assets/css/bootstrap.min.css">
      <link rel="stylesheet" href="assets/css/font-awesome.min.css">
      <link rel="stylesheet" href="assets/css/glass-admin.css">
   </head>
   <body>
      <div class="admin-auth-shell">
         <div class="admin-auth-card">
            <div class="text-center mb-4">
               <img src="../img/logo2.png" alt="StakingHUB" width="60" style="filter:drop-shadow(0 0 14px rgba(23,230,176,0.5));">
               <h4 class="mt-3 mb-1">Admin Console</h4>
               <p style="color:var(--text-3);font-size:.88rem;">Sign in to manage StakingHUB</p>
            </div>
            <form method="post">
               <div class="form-group">
                  <label>Username</label>
                  <input type="text" name="username" class="form-control" placeholder="Username" required data-cy="admin-username">
               </div>
               <div class="form-group">
                  <label>Password</label>
                  <input type="password" name="password" class="form-control" placeholder="Password" required data-cy="admin-password">
               </div>
               <button type="submit" name="submit" class="btn btn-primary btn-block mt-3" data-cy="admin-submit">Sign In</button>
            </form>
            <?php if (!empty($msg)) : ?>
               <div class="alert alert-danger mt-3 mb-0 text-center py-2" data-cy="admin-login-error"><?php echo $msg; ?></div>
            <?php endif; ?>
         </div>
      </div>
      <script src="assets/js/vendor/jquery-2.1.4.min.js" type="text/javascript"></script>
      <script src="assets/js/popper.min.js" type="text/javascript"></script>
      <script src="assets/js/plugins.js" type="text/javascript"></script>
      <script src="assets/js/main.js" type="text/javascript"></script>
   </body>
</html>