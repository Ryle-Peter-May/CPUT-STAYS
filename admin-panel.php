<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    die("Unauthorized access.");
}

$mysqli = require __DIR__ . "/database.php";
$admin_id = $_SESSION["user_id"];

// Fetch admin info
$sql = "SELECT FirstName, LastName FROM host WHERE AdminID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Count accommodations
$sql = "SELECT COUNT(*) AS total_accom FROM accommodation WHERE AdminID = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($total_accom);
$stmt->fetch();
$stmt->close();

// Count pending bookings
$sql = "SELECT COUNT(*) AS pending
    FROM booking b
    JOIN accommodation a ON b.AccommodationID = a.AccommodationID
    WHERE a.AdminID = ? AND b.BkStatus = 'Pending'
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($pending_bookings);
$stmt->fetch();
$stmt->close();

// Count confirmed occupants
$sql = "SELECT COUNT(*) AS occupants
    FROM booking b
    JOIN accommodation a ON b.AccommodationID = a.AccommodationID
    WHERE a.AdminID = ? AND b.BkStatus = 'Confirmed'
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($occupants);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin-panel.css" />
  <style>
    .stats {
      display: flex;
      gap: 20px;
      margin: 20px 0;
    }
    .stat-box {
      flex: 1;
      background: #f4f8ff;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .stat-box h2 {
      font-size: 32px;
      margin: 0;
      color: #0073e6;
    }
    .stat-box p {
      margin: 5px 0 0;
      font-size: 14px;
      color: #555;
    }
    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin: 30px 0;
    }
    .card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    .card h3 {
      margin: 10px 0;
      font-size: 20px;
    }
    .card p {
      font-size: 14px;
      color: #555;
    }
    .card a {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 15px;
      background: #0073e6;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background 0.2s;
    }
    .card a:hover {
      background: #005bb5;
    }
    .welcome {
      margin-top: 20px;
      font-size: 18px;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <a href="index.html" style="text-decoration: none; color: white;">
        <img src="logo.jpg" alt="cput logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">
        CPUT STAYS
      </a>
    </div>
    <nav>
      <a href="admins-profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="form-container">
    <h1>Host Dashboard</h1>
    <p class="welcome">Welcome back, <?= htmlspecialchars($admin["FirstName"]) ?>!</p>

    <!-- Quick Stats -->
    <div class="stats">
      <div class="stat-box">
        <h2><?= $total_accom ?></h2>
        <p>Accommodations</p>
      </div>
      <div class="stat-box">
        <h2><?= $pending_bookings ?></h2>
        <p>Pending Bookings</p>
      </div>
      <div class="stat-box">
        <h2><?= $occupants ?></h2>
        <p>Current Occupants</p>
      </div>
    </div>

    <!-- Dashboard Navigation -->
    <div class="dashboard">
      <div class="card">
        <h3>Add Accommodation</h3>
        <p>Register a new student residence under your account.</p>
        <a href="add_accommodation.html">Add Now</a>
      </div>
      <div class="card">
        <h3>View Applications</h3>
        <p>Review student booking requests for your residences.</p>
        <a href="admins-booking.php">Manage Applications</a>
      </div>

      <div class="card">
        <h3>View Occupants</h3>
        <p>See the current students staying in your residences.</p>
        <a href="admin-occupants.php">View Occupants</a>
      </div>
    </div>
  </main>
  <footer>
    <p>© 2025 CPUT STAYS. All rights reserved.</p>
  </footer>
</body>
</html>
