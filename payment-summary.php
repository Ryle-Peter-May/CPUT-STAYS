<?php
session_start();
$mysqli = require __DIR__ . "/database.php";
$studNum = $_SESSION["user_id"];

$sql = "SELECT p.PaymentID, p.PaymentDate, p.PayMethod, p.AmtPaid,
               b.BookingID, a.Name AS AccommodationName
        FROM payment p
        JOIN booking b ON p.BookingID = b.BookingID
        JOIN accommodation a ON b.AccommodationID = a.AccommodationID
        WHERE p.StudNum = ?
        ORDER BY p.PaymentDate DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $studNum);
$stmt->execute();
$payments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payment Summary</title>
  <link rel="stylesheet" href="payment-summary.css" />
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
  </header>

  <main class="form-container">
    <h2>Your Payments</h2>
    <a href="profile.php">Back to Profile</a>

    <?php if ($payments->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Accommodation</th>
            <th>Method</th>
            <th>Date</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = $payments->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($p["AccommodationName"]) ?></td>
            <td><?= htmlspecialchars($p["PayMethod"]) ?></td>
            <td><?= htmlspecialchars($p["PaymentDate"]) ?></td>
            <td>R<?= number_format($p["AmtPaid"], 2) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No payments made yet.</p>
    <?php endif; ?>
  </main>
</body>
</html>
