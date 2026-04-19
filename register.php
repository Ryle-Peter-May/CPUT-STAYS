<?php

session_start();

$mysqli = require __DIR__ . "/database.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $role = $_POST['role'] ?? null;

    if($role === "student"){
        
        if(empty($_POST["first_name"])) die("First Name is required");
        if(empty($_POST["last_name"])) die("Last Name is required");
        if(empty($_POST["stud_number"])) die("Student Number is required");
        if(! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) die("Email is required");
        if(empty($_POST["cell_number"])) die("Cell Number is required");
        if(empty($_POST["id_num"])) die("ID number is required");
        if(!preg_match('/^\d{13}$/', $_POST["id_num"])) {
         die("ID number must be exactly 13 digits.");
        };
        if(empty($_POST["enrollment_year"])) die("Enrolment year is required");
        if(strlen($_POST["password"])<8) die("Password must be at least 8 characters long");
        if(empty($_POST["funding"])) die("Funding is required");

        $id_num = $_POST['id_num'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $stud_number = $_POST['stud_number'];
        $email = $_POST['email'];
        $cell_number = $_POST['cell_number'];
        $enrollment_year = $_POST['enrollment_year'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $funding = $_POST['funding'];

        $sql = "INSERT INTO student(IDNum, FirstName, LastName, StudNum, Email, CellNumr, EnrollYr, Password, Funding)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssssss", $id_num, $first_name, $last_name, $stud_number, $email, $cell_number, $enrollment_year, $password, $funding);

        if($stmt->execute()){
            header("Location: login.php");
            exit;
        }else{
            $stmt->close();
            if($mysqli->errno === 1062){
                die("email already taken");
            }else{
                die($mysqli->error . " " . $mysqli->errno);
            }
        }

    } elseif($role === "admin"){
        
        if(empty($_POST["first_name"])) die("First Name is required");
        if(empty($_POST["last_name"])) die("Last Name is required");
        if(! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) die("Email is required");
        if(empty($_POST["cell_number"])) die("Cell Number is required");
        if(empty($_POST["id_num"])) die("ID number is required");
        if(!preg_match('/^\d{13}$/', $_POST["id_num"])) {
         die("ID number must be exactly 13 digits.");
        };
        if(strlen($_POST["password"])<8) die("Password must be at least 8 characters long");

        $id_num = $_POST['id_num'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $cell_number = $_POST['cell_number'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO host(IDNum, FirstName, LastName, Email,CellNumber, Password)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssss", $id_num, $first_name, $last_name, $email, $cell_number, $password);

        if($stmt->execute()){
    echo "Inserted successfully!";
    header("Location: login.php");
    exit;
}else{
    echo "Error executing statement: " . $stmt->error;
    $stmt->close();
    if($mysqli->errno === 1062){
        die("Email already taken");
    }else{
        die($mysqli->error . " " . $mysqli->errno);
    }
}


    }

}


?>