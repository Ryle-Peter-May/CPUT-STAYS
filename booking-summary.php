<?php
session_start();

if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "student") {
    $mysqli = require __DIR__ . "/database.php";

    $sql = "SELECT b.BookingID, b.RmNum, b.StartDate, b.EndDate, b.TotCost, 
               b.PaymentStatus, b.BkStatus,
               a.Name AS AccommodationName,
               r.RmType
        FROM booking b
        JOIN accommodation a ON b.AccommodationID = a.AccommodationID
        JOIN rooms r ON b.RmNum = r.RmNum
        WHERE b.StudNum = ?
        ORDER BY b.BookingDate DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $bookings = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Booking Summary</title>
  <link rel="stylesheet" href="booking-summary.css"/>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
    }
    th {
      background: #f4f4f4;
      text-align: left;
    }
    button.cancel-btn {
      background: red;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 5px 10px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <a href="homepage.php" style="text-decoration: none; color: white;">
        <img src="logo.jpg" alt="cput logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
        CPUT STAYS
      </a>
    </div>
    <nav>
    </nav>
  </header>

  <main class="form-container">
    <h1>Your Bookings</h1>
    <div style="margin: 15px 0;">
  <a href="profile.php" style="
      color: #0073e6;
      text-decoration: none;
  ">Back to Profile</a>
</div>

    <?php if ($bookings && $bookings->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Accommodation</th>
            <th>Room Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Total Cost</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Cancel</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($b = $bookings->fetch_assoc()): ?>
            <?php $startDate = strtotime($b["StartDate"]); ?>
            <tr>
              <td><?= htmlspecialchars($b["AccommodationName"]) ?></td>
              <td><?= htmlspecialchars($b["RmType"]) ?></td>
              <td><?= htmlspecialchars($b["StartDate"]) ?></td>
              <td><?= htmlspecialchars($b["EndDate"]) ?></td>
              <td>R<?= number_format($b["TotCost"], 2) ?></td>
              <td><?= htmlspecialchars($b["BkStatus"]) ?></td>
              <td>
                <?php 
                  $status = strtolower(trim($b["PaymentStatus"]));
                  if ($status === "pending" || $status === "pending payment" || $status === "unpaid"): ?>
                    <a href="payment.php?booking_id=<?= $b['BookingID'] ?>" class="cta-button">Make Payment</a>
                <?php elseif ($status === "bursary" || $status === "bursary funded"): ?>
                    <span style="color: green;">Covered by Bursary</span>
                <?php elseif ($status === "paid"): ?>
                    <span style="color: green;">Paid</span>
                <?php else: ?>
                    <span><?= htmlspecialchars($b["PaymentStatus"]) ?></span>
                <?php endif; ?>
            </td>
              <td>
                <?php if (
                    ($b["BkStatus"] === "Pending" || $b["BkStatus"] === "Confirmed") &&
                    $b["PaymentStatus"] !== "Paid" &&
                    //$b["PaymentStatus"] !== "Bursary Funded" &&
                    $startDate > time()
                ): ?>
                  <form method="post" action="cancel-booking.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    <input type="hidden" name="booking_id" value="<?= $b['BookingID'] ?>">
                    <input type="hidden" name="room_id" value="<?= $b['RmNum'] ?>">
                    <button type="submit" class="cancel-btn">Cancel</button>
                  </form>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No bookings yet.</p>
    <?php endif; ?>
  </main>

  <footer>
    <p>© 2025 CPUT STAYS. All rights reserved.</p>
  </footer>
</body>
</html>
