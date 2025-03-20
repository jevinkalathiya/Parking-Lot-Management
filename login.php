<?php
ini_set('display_errors', 'On'); // Not to show errors on page
require("connection.inc.php");
require("function.inc.php");

session_start(); // Start the session
$msg = '';


if (isset($_POST['submit'])) {
  $email = get_safe_value($con, $_POST['email']); // Fetch and sanitize input values
  $password = get_safe_value($con, $_POST['password']);
  $sql = "SELECT * FROM user WHERE email='$email'"; // Query to fetch user data by email
  $result = mysqli_query($con, $sql);
  $count = mysqli_num_rows($result);
  if ($count > 0) {
    $user = mysqli_fetch_assoc($result); // Fetch the user data
    $password_stored = $user['password']; // Retrieve the Base64 encoded password from the database
    $encryption_key = $user['encryption_key'];
    $original_password = decryptPassword($password_stored, $encryption_key); // Decode the stored password
    if ($original_password === $password) { // Check if the decoded password matches the entered password
      session_start(); // Password matches, start session and redirect to the index page
      $_SESSION['Admin_Login'] = 'yes';
      header("Location: index"); // Redirect to the index page
      exit();
    } else {
      $_SESSION['error_msg'] = "<div id='error'><i class='fa-regular fa-circle-exclamation'></i> Invalid Login credentials.</div>"; // Password does not match
    }
  } else {
    $_SESSION['error_msg'] = "<div id='error'><i class='fa-regular fa-circle-exclamation'></i> Invalid Login credentials.</div>"; // No user found with the given email
  }
}
if (isset($_SESSION['error_msg'])) { // Check if the error message exists // Display and clear the error message if page is reload
  $msg = $_SESSION['error_msg'];
  if (!isset($_POST['submit'])) { // If the page was reloaded manually (not by form submission), clear the error message
    unset($_SESSION['error_msg']);
  }
}



?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | PLM</title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="./assets/imgs/favicon.png">
  <link rel="stylesheet" href="././assets/css/style.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/all.css">
</head>

<body class="login">
  <!-------- Login form -------->
  <div class="container-login">
    <?php if (isset($msg)) {
      echo $msg;
    } ?><br>
    <div class="wrapper">
      <div class="title"><span>Parking Slot Management</span></div>
      <form method="POST">
        <div class="row">
          <i class="fas fa-user"></i>
          <input type="email" id="email" name="email" placeholder="Email" required autofocus>
        </div>
        <div class="row">
          <i class="fas fa-lock"></i>
          <input type="password" class="password" id="password" name="password" placeholder="Password" required>
        </div>
        <div class="row button">
          <input type="submit" name="submit" value="Login"><br>
        </div>
      </form>
    </div>
  </div>
  <script>
    /*================================ To remove error msg ==================================*/
    document.addEventListener('DOMContentLoaded', function() { // Wait for the document to fully load
      var sessionMsg = document.getElementById('error'); // Find the session message element
      if (sessionMsg) { // If the session message element exists, set a timeout to remove it
        setTimeout(function() {
          sessionMsg.style.display = 'none';
        }, 10000); // 10000 milliseconds = 10 seconds
      }
    });
  </script>
</body>

</html>