<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Ensuring that Host is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("You must be logged in as an admin to add accommodation.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic validation for the form
    if (empty($_POST['name'])) die("Accommodation name is required");
    if (empty($_POST['address'])) die("Address is required");
    if (empty($_POST['contact_num'])) die("Contact number is required");
    if (empty($_POST['amenities'])) die("Amenities are required");

    // Fetching the Accommodation data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact_num = $_POST['contact_num'];
    $amenities = $_POST['amenities'];

    // Insert accommodation
    $sql = "INSERT INTO accommodation (AdminID, Name, Address, ContactNum, Amenities)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issss", $_SESSION['user_id'], $name, $address, $contact_num, $amenities);

    if (!$stmt->execute()) {
        die("Error inserting accommodation: " . $stmt->error);
    }

    // Get accommodation ID
    $accommodation_id = $stmt->insert_id;
    $stmt->close();

    // Rooms data (arrays)
    $rm_types = $_POST['rm_type'];
    $tot_rms = $_POST['tot_rms'];
    $prices = $_POST['price'];

    $sql2 = "INSERT INTO rooms (AccommodationID, RmType, TotRms, AvailableRms, PricePerRmType)
             VALUES (?, ?, ?, ?, ?)";
    $stmt2 = $mysqli->prepare($sql2);

    //going through each room type
    foreach ($rm_types as $index => $type) {
        $type = $rm_types[$index];
        $total = (int)$tot_rms[$index];
        $available = $total;
        $price = (float)$prices[$index];

        $stmt2->bind_param("isiii", $accommodation_id, $type, $total, $available, $price);
        if (!$stmt2->execute()) {
            die("Error inserting room: " . $stmt2->error);
        }
    }
    $stmt2->close();

    echo "Accommodation and rooms added successfully!";
    header("Location: admin-panel.php");
    exit;
}
?>
