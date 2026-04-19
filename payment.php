<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

if (!isset($_GET["booking_id"])) {
    die("No booking selected.");
}
$booking_id = $_GET["booking_id"];
$student_id = $_SESSION["user_id"];

$sql = "SELECT b.BookingID, b.TotCost, a.Name AS AccommodationName
        FROM booking b
        JOIN accommodation a ON b.AccommodationID = a.AccommodationID
        WHERE b.BookingID = ? AND b.StudNum = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $booking_id, $student_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment</title>
  <link rel="stylesheet" href="payment.css" />
  <style>
    .credit-fields {
      display: none;
      margin-top: 10px;
    }

    .credit-fields input {
      display: block;
      width: 100%;
      margin-top: 8px;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
  </header>

  <main class="form-container">
    <h2>Payment for <?= htmlspecialchars($booking["AccommodationName"]) ?></h2>
    <p><strong>Amount Due:</strong> R<?= number_format($booking["TotCost"], 2) ?></p>

    <form action="process-payment.php" method="post">
      <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

      <label>Payment Method</label>
      <select name="method" id="paymentMethod" required>
        <option value="">--Select--</option>
        <option value="card">Credit/ Debit Card</option>
        <!-- <option value="bank">Bank Transfer</option> -->
        <option value="cash">Cash(in person)</option>
      </select>

      <div class="credit-fields" id="creditFields">
        <label>Cardholder Name</label>
        <input type="text" name="card_name" placeholder="Name on card" required>

        <label>Card Number</label>
        <input type="text" name="card_number" maxlength="16" placeholder="1234 5678 9012 3456" required>

        <label>Expiry Date</label>
        <input type="month" name="expiry_date" required>

        <label>CVV</label>
        <input type="password" name="cvv" maxlength="3" placeholder="123" required>
      </div>

      <label>Payment Date</label>
      <input type="date" name="date" required>

      <label>Amount</label>
      <input type="number" name="amount" value="<?= $booking['TotCost'] ?>" readonly>

      <button type="submit">Confirm Payment</button>
    </form>
  </main>
  <script>
    const paymentMethod = document.getElementById("paymentMethod");
    const creditFields = document.getElementById("creditFields");

    paymentMethod.addEventListener("change",()=>{
      if(paymentMethod.value === "card"){
        creditFields.style.display = "block";
      }else{
        creditFields.style.display = "none";
      }

    });
  </script>
</body>
</html>
