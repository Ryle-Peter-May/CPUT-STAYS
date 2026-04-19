<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die("Unauthorized access.");
}

$admin_id = $_SESSION["user_id"];

// Fetch admin info
$sql = "SELECT * FROM host WHERE AdminID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Fetch accommodations owned by this admin
$sql = "SELECT * FROM accommodation WHERE AdminID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$accommodations = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Profile</title>
  <link rel="stylesheet" href="admins-profile.css">
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
    <nav>
      <a href="admins-booking.php">Bookings</a>
      <a href="admin-occupants.php">Occupants</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="form-container">
    <h1>Welcome, <?= htmlspecialchars($admin["FirstName"]) ?></h1>
    <a href = "admin-panel.php">Back to host dashboard</a>
    <section>
      <h2>Your Info</h2>
      <p><strong>ID Number:</strong> <?= htmlspecialchars($admin["IDNum"]) ?></p>
      <p><strong>Name:</strong> <?= htmlspecialchars($admin["FirstName"] . " " . $admin["LastName"]) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($admin["Email"]) ?></p>
      <p><strong>Cell:</strong> <?= htmlspecialchars($admin["CellNumber"]) ?></p>
    </section>

    <section>
      <h2>Your Accommodations</h2>
      <?php if ($accommodations->num_rows > 0): ?>
        <ul>
          <?php while ($a = $accommodations->fetch_assoc()): ?>
            <li>
              <strong><?= htmlspecialchars($a["Name"]) ?></strong> - <?= htmlspecialchars($a["Address"]) ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No accommodations registered yet. <a href="add_accommodation.html">Add one here</a>.</p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
