<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

if (!isset($_SESSION["temp_user_id"]) || !isset($_SESSION["temp_role"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["temp_user_id"];
$role = $_SESSION["temp_role"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = $_POST["otp"];

    $sql = "SELECT * FROM otp_verification 
            WHERE user_id = ? AND role = ? AND otp_code = ? AND expires_at > NOW()
            ORDER BY id DESC LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iss", $user_id, $role, $otp);
    $stmt->execute();
    $otpData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($otpData) {
        //login the user
        $_SESSION["user_id"] = $user_id;
        $_SESSION["role"] = $role;

        // Clean up after verification 
        $mysqli->query("DELETE FROM otp_verification WHERE user_id = $user_id AND role = '$role'");
        unset($_SESSION["temp_user_id"]);
        unset($_SESSION["temp_role"]);

        if ($role === "admin") {
            header("Location: admin-panel.php");
        } else {
            header("Location: homepage.php");
        }
        exit;
    } else {
        $error = "Invalid or expired OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP</title>
  <!-- <link rel="stylesheet" href="style.css"> -->
   <link rel ="stylesheet" href="login.css"/>
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
</header>
  <main class="form-container">
    <h2>OTP Verification</h2>
    <form method="post">
      <label for="otp">Enter the 6-digit OTP sent to your email:</label>
      <input type="text" id="otp" name="otp" maxlength="6" required>
      <button type="submit">Verify</button>
    </form>
      <form method="post" action="resend-otp.php" style="margin-top: 10px;">
      <button type="submit" style="background: none; border: none; color: #0073e6; text-decoration: none; cursor: pointer;">
      Resend OTP
      </button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  </main>
</body>
</html>
