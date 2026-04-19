<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Ensure host is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die("Unauthorized access.");
}

$admin_id = $_SESSION["user_id"];

// Fetching confirmed occupants
$sql = "SELECT b.BookingID, b.StartDate, b.EndDate, b.TotCost, b.PaymentStatus,
           s.FirstName, s.LastName, s.Email, s.StudNum,
           a.Name AS AccommodationName,
           r.RmType
    FROM booking b
    JOIN student s ON b.StudNum = s.StudNum
    JOIN rooms r ON b.RmNum = r.RmNum
    JOIN accommodation a ON b.AccommodationID = a.AccommodationID
    WHERE a.AdminID = ? AND b.BkStatus = 'Confirmed'
    ORDER BY b.StartDate ASC
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$occupants = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Occupants</title>
  <link rel="stylesheet" href="admin-occupants.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f4f4f4; }
  </style>
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
    <nav>
      <a href="admins-profile.php">Profile</a>
      <a href="admins-booking.php">Bookings</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="form-container">
    <h1>Current Occupants</h1>
    <a href = "admin-panel.php">Back to Dashboard</a>
    <?php if ($occupants->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Accommodation</th>
            <th>Room Type</th>
            <th>Dates</th>
            <th>Payment</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($o = $occupants->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($o["FirstName"] . " " . $o["LastName"]) ?> (<?= $o["StudNum"] ?>)</td>
              <td><?= htmlspecialchars($o["AccommodationName"]) ?></td>
              <td><?= htmlspecialchars($o["RmType"]) ?></td>
              <td><?= htmlspecialchars($o["StartDate"]) ?> → <?= htmlspecialchars($o["EndDate"]) ?></td>
              <td><?= htmlspecialchars($o["PaymentStatus"]) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No current occupants.</p>
    <?php endif; ?>
  </main>
</body>
</html>
