<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {
    $booking_id = (int)$_POST["booking_id"];
    $room_id = (int)$_POST["room_id"];
    $student_id = $_SESSION["user_id"];

    // verifying that the booking belongs to student and checking payment status 
    $checkSql = "SELECT BkStatus, PaymentStatus FROM booking 
                 WHERE BookingID = ? AND StudNum = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param("ii", $booking_id, $student_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $booking = $result->fetch_assoc();
    $checkStmt->close();

    if (!$booking) {
        die("Booking not found or unauthorized.");
    }

    // Only allow cancel if not paid or bursary funded
    if (
        ($booking["BkStatus"] === "Pending" || $booking["BkStatus"] === "Confirmed") &&
        $booking["PaymentStatus"] !== "Paid"
        //$booking["PaymentStatus"] !== "Bursary Funded"
    ) {
        // Update booking + payment status to Cancelled
        $updateSql = "UPDATE booking 
                      SET BkStatus = 'Cancelled', PaymentStatus = 'Cancelled' 
                      WHERE BookingID = ?";
        $updateStmt = $mysqli->prepare($updateSql);
        $updateStmt->bind_param("i", $booking_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Restore room availability
        $roomSql = "UPDATE rooms SET AvailableRms = AvailableRms + 1 WHERE RmNum = ?";
        $roomStmt = $mysqli->prepare($roomSql);
        $roomStmt->bind_param("i", $room_id);
        $roomStmt->execute();
        $roomStmt->close();

        header("Location: booking-summary.php?cancel=success");
        exit;
    } else {
        die("This booking cannot be cancelled (already paid or processed).");
    }
} else {
    die("Invalid request.");
}
?>
