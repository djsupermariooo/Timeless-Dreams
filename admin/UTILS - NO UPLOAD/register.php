<?php

    $servername = "localhost";
    $username = "timebnzl_admin";
    $password = "preppy01!";
    $dbname = "timebnzl_photos";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $user = "jennie";
    $pass_to_hash = "JenLynn87";
    $hashed_pass = password_hash($pass_to_hash, PASSWORD_DEFAULT);

    $sql = "INSERT INTO login (username, password) VALUES ('$user', '$hashed_pass')";
    $result = $conn->query($sql);

    if ($result == false) {
        echo "Error!";
    }
    else {
        echo 'User ' . $user . 'successfully created!';
    }

    // Close connection
    $conn->close();
?>