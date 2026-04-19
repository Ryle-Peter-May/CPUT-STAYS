<?php
session_start();
$is_invalid=false;
$mysqli = require __DIR__ . "/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Try login for both student and admin
    $user = null;
    $role = null;

    // Check student table first
    $stmt = $mysqli->prepare("SELECT StudNum AS id, FirstName, Email, Password FROM student WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($student && password_verify($password, $student["Password"])) {
        $user = $student;
        $role = "student";
    } else {
        // Then check admin
        $stmt = $mysqli->prepare("SELECT AdminID AS id, FirstName, Email, Password FROM host WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin["Password"])) {
            $user = $admin;
            $role = "admin";
        }
    }

    if ($user) {
        // Step 1: Generate OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Step 2: Store OTP
        $sql = "INSERT INTO otp_verification (user_id, role, otp_code, expires_at) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("isss", $user["id"], $role, $otp, $expiry);
        $stmt->execute();
        $stmt->close();

        // Step 3: Send OTP via email
        $to = $user["Email"];
        $subject = "Your CPUT STAYS Login OTP";
        $message = "Dear " . $user["FirstName"] . ",\n\nYour OTP for login is: $otp\n\nThis code will expire in 5 minutes.\n\nCPUT STAYS";
        $headers = "From: no-reply@cputstays.ac.za";

        require_once 'mailer.php';
        sendMail($to, $subject, $message);


        // Step 4: Redirect for OTP verification
        $_SESSION["temp_user_id"] = $user["id"];
        $_SESSION["temp_role"] = $role;

        header("Location: verify_otp.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
    <nav>
      <a href="index.html">Home</a>
      <a href="register.html">Register</a>
    </nav>
  </header>

  <main class="form-container">

    <h2>Login</h2>
    <?php if($is_invalid): ?>
      <em>Invalid Login</em>
      <?php endif; ?>

    <form method ="post">
      <label for ="email">Email</label>
      <input type="email" name="email" id="email"  
      value="<?= htmlspecialchars($_POST["email"] ?? "")?>" required/>

      <label for ="password">Password</label>
      <input type="password" name="password" id ="password" required />


      <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.html">Register here</a></p>
  </main>
</body>
</html>
