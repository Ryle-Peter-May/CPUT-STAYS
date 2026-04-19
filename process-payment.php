<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studNum = $_SESSION["user_id"];
    $booking_id = $_POST["booking_id"];
    $method = $_POST["method"];
    $date = $_POST["date"];
    $amount = $_POST["amount"];

    // Insert payment record
    $stmt = $mysqli->prepare("INSERT INTO payment (BookingID, StudNum, PaymentDate, PayMethod, AmtPaid) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $booking_id, $studNum, $date, $method, $amount);
    $stmt->execute();

    // Update booking payment status
    $update = $mysqli->prepare("UPDATE booking SET PaymentStatus = 'Paid' WHERE BookingID = ?");
    $update->bind_param("i", $booking_id);
    $update->execute();

    header("Location: payment-summary.php");
    exit;
}
?>
