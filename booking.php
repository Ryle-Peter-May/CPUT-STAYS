<?php
session_start();
$mysqli = require __DIR__ . "/database.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("You must be logged in as a student to make a booking.");
}

// Fetch all accommodations with rooms so that the page only displays availabe accommodations
$sql = "SELECT a.AccommodationID, a.Name, r.RmNum, r.RmType, r.PricePerRmType, r.AvailableRms
        FROM accommodation a
        JOIN rooms r ON a.AccommodationID = r.AccommodationID
        WHERE r.AvailableRms > 0";
$result = $mysqli->query($sql);

// Grouping by accommodation
$accommodations = [];
while ($row = $result->fetch_assoc()) {
    $accommodations[$row['AccommodationID']]['name'] = $row['Name'];
    $accommodations[$row['AccommodationID']]['rooms'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Booking</title>
  <link rel="stylesheet" href="booking.css" />
</head>
<body>
  <header>
    <div class="logo">CPUT STAYS</div>
    <nav>
      <a href="homepage.php">Home</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <main class="form-container">
    <h2>Make a Booking</h2>
    <form action="booking-form.php" method="post">
      <label for="accommodation">Select Accommodation</label>
      <select id="accommodation" name="accommodation_id" required>
        <option value="">-- Select Accommodation --</option>
        <?php foreach ($accommodations as $accomId => $accom): ?>
          <option value="<?= $accomId ?>"><?= htmlspecialchars($accom['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="room_type">Room Type</label>
      <select id="room_type" name="room_id" required>
        <option value="">-- Select Room --</option>
        <?php foreach ($accommodations as $accomId => $accom): ?>
          <?php foreach ($accom['rooms'] as $room): ?>
            <option value="<?= $room['RmNum'] ?>" data-accom="<?= $accomId ?>">
              <?= htmlspecialchars($room['RmType']) ?> - 
              R<?= number_format($room['PricePerRmType'], 2) ?> 
              (<?= $room['AvailableRms'] ?> left)
            </option>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </select>

      <label for="starting_date">Start Date</label>
      <input type="date" id="starting_date" name="starting_date" required />

      <label for="end_date">End Date</label>
      <input type="date" id="end_date" name="end_date" required />

      <label><input type="checkbox" required /> I accept the terms & conditions</label>
      <button type="submit">Submit Booking</button>
    </form>
  </main>

  <footer>
    <p>© 2025 CPUT STAYS. All rights reserved.</p>
  </footer>

  <script>
    // Filter room options to match selected accommodation
    const accomSelect = document.getElementById("accommodation");
    const roomSelect = document.getElementById("room_type");

    accomSelect.addEventListener("change", () => {
      const selectedAccom = accomSelect.value;
      for (let option of roomSelect.options) {
        if (option.value === "") continue;
        option.hidden = option.dataset.accom !== selectedAccom;
      }
      roomSelect.value = "";
    });
  </script>
</body>
</html>
