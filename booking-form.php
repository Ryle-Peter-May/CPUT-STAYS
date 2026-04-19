<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {
    $studnum = $_SESSION["user_id"];
    $accommodation_id = (int)$_POST["accommodation_id"];
    $room_id = (int)$_POST["room_id"];
    $start_date = $_POST["starting_date"];
    $end_date = $_POST["end_date"];
    $booking_date = date("Y-m-d");
    $bkStatus = "Pending";

    //need to name queries, using sql1 for names isnt helpful or descriptive, change them

    // Fetch room price
    $roomQuery = $mysqli->prepare("SELECT PricePerRmType FROM rooms WHERE RmNum = ?");
    $roomQuery->bind_param("i", $room_id);
    $roomQuery->execute();
    $priceResult = $roomQuery->get_result()->fetch_assoc();
    $price = $priceResult["PricePerRmType"] ?? 0;
    $roomQuery->close();

    $startObj = new DateTime($start_date);
    $endObj = new DateTime($end_date);
    $interval = $startObj->diff($endObj);
    $months = ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);
    if($months <= 0)$months = 1;
    $totalCost = $months * $price;

    //Trying to check the funding type
    $fundQuery = $mysqli->prepare("SELECT Funding FROM student WHERE StudNum = ?");
    $fundQuery->bind_param("i", $studnum);
    $fundQuery->execute();
    $fundResult= $fundQuery->get_result()->fetch_assoc();
    $fundType = $fundResult["Funding"] ?? "Self-Funded";
    $fundQuery->close();

    $paymentStatus=($fundType === "Bursary") ? "Bursary Funded" : "Pending Payment";

    // Insert booking
    $sql = "INSERT INTO booking 
            (AccommodationID, StudNum, RmNum, BookingDate, StartDate, EndDate, PaymentStatus, TotCost, BkStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iiissssds", $accommodation_id, $studnum, $room_id, $booking_date, $start_date, $end_date, $paymentStatus,$totalCost,$bkStatus);
    $stmt->execute();
    $stmt->close();
        ?>
        
        <!DOCTYPE html>
            <html lang="en">
            <heade>
                <meta charset="UTF-8">
                <title>Booking Confirmation</title>
                <link rel="stylesheet" href="booking-form.css">
                <style>
                    .confirmation{
                        max-width: 600px;
                        margin: 50px auto;
                        background: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                        text-align: center;
                    }

                    .confirmation h2{color: #0073e6;}
                    .confirmation p {margin: 10px 0;}
                    .button{
                        display: inline-block;
                        margin-top:20px;
                        background: #0073e6;
                        color: white;
                        padding: 10px 15px;
                        border-radius: 5px;
                        text-decoration: none;
                    }

                    .button:hover{background: #005bb5;}
                </style>
            </head>
            <body>
                <div class="confirmation">
                    <h2>Booking Successful!</h2>
                    <p><strong>Accommodation ID:</strong> <?= htmlspecialchars($accommodation_id) ?></p>
                    <p><strong>Room Number:</strong> <?= htmlspecialchars($room_id) ?></p>
                    <p><strong>Start Date:</strong> <?= htmlspecialchars($start_date) ?></p>
                    <p><strong>End Date:</strong> <?= htmlspecialchars($end_date) ?></p>
                    <p><strong>Total Duration:</strong> <?= $months ?> month(s)</p>
                    <p><strong>Total Cost:</strong> R<?= number_format($totalCost, 2) ?></p>
                    <p><strong>Payment Status:</strong> <?= htmlspecialchars($paymentStatus) ?></p>
                    <a href="profile.php" class="button">Go to Profile</a>
                </div>
            </body>
            </html>
            <?php
        exit;
    } else {
        die("Error creating booking: " . $stmt->error);
    }

?>
