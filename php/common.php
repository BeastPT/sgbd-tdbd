<?php


function connectDB() {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$connection)
        die("Connection Error: ". mysqli_error());

    return $connection;
}
?>