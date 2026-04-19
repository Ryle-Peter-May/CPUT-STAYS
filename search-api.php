<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

$q = $_GET['q'] ?? '';
$results = [];

if (empty($q)) {
    // if nothing is in the search bar, diplay all accommodations
    $sql = "SELECT a.AccommodationID, a.Name, a.Address, a.ContactNum, a.Amenities,
                   r.RmType, r.PricePerRmType, r.AvailableRms
            FROM accommodation a
            LEFT JOIN rooms r ON a.AccommodationID = r.AccommodationID
            ORDER BY a.Name ASC";
    $stmt = $mysqli->prepare($sql);
} else {
    // Searching with filter
    $sql = "SELECT a.AccommodationID, a.Name, a.Address, a.ContactNum, a.Amenities,
                   r.RmType, r.PricePerRmType, r.AvailableRms
            FROM accommodation a
            LEFT JOIN rooms r ON a.AccommodationID = r.AccommodationID
            WHERE a.Name LIKE ? OR a.Address LIKE ? OR a.Amenities LIKE ?
            ORDER BY a.Name ASC LIMIT 20";
    $stmt = $mysqli->prepare($sql);
    $param = '%' . $q . '%';
    $stmt->bind_param("sss", $param, $param, $param);
}

$stmt->execute();
$res = $stmt->get_result();

// Group rooms under accommodations
$accommodations = [];
while ($row = $res->fetch_assoc()) {
    $id = $row['AccommodationID'];
    if (!isset($accommodations[$id])) {
        $accommodations[$id] = [
            'AccommodationID' => $row['AccommodationID'],
            'Name' => $row['Name'],
            'Address' => $row['Address'],
            'ContactNum' => $row['ContactNum'],
            'Amenities' => $row['Amenities'],
            'Rooms' => []
        ];
    }

    if ($row['RmType']) {
        $accommodations[$id]['Rooms'][] = [
            'RmType' => $row['RmType'],
            'Price' => $row['PricePerRmType'],
            'Available' => $row['AvailableRms']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(array_values($accommodations));
