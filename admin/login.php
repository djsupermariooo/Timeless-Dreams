<?php
    session_start();

    include("../../db.php");

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Successfully connected, check login credentials
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM login WHERE username='$user'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $isverified = password_verify($pass, $row["password"]);

    if ($isverified) {
        $_SESSION['username'] = $user;
        header('Location: upload.php');
    } else {
        echo "Invalid login! Please press back and try again.";
    }

    // Close connection
    $conn->close();

?>