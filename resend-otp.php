<?php
session_start();
$mysqli = require __DIR__ . "/database.php";
require __DIR__ . "/mailer.php";

if (!isset($_SESSION["temp_user_id"]) || !isset($_SESSION["temp_role"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["temp_user_id"];
$role = $_SESSION["temp_role"];

// Fetching emails
$table = ($role === "admin") ? "admin" : "student";
$emailColumn = "Email";
$idColumn = ($role === "admin") ? "AdminID" : "StudNum";

$sql = "SELECT $emailColumn FROM $table WHERE $idColumn = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$email = $stmt->get_result()->fetch_assoc()[$emailColumn] ?? null;
$stmt->close();

if (!$email) {
    die("Could not find user email.");
}

// Remove old OTPs
$mysqli->query("DELETE FROM otp_verification WHERE user_id = $user_id AND role = '$role'");

// Generate new OTP
$otp = random_int(100000, 999999);
$expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$sql = "INSERT INTO otp_verification (user_id, role, otp_code, expires_at) VALUES (?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("isss", $user_id, $role, $otp, $expires_at);
$stmt->execute();
$stmt->close();

// Send OTP via email
sendMail($email, "Your new OTP Code", "Your new OTP is: $otp. It will expire in 5 minutes.");

$_SESSION["otp_message"] = "A new OTP has been sent to your email.";
header("Location: verify-otp.php");
exit;
?>