<?php
global $current_page;
$current_page = get_site_url().'/'.basename(get_permalink());

function verifyCapability($capability) {
    return current_user_can($capability) && is_user_logged_in();
}

function connectDB() {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$connection)
        die("Connection Error: ". mysqli_error());

    return $connection;
}
?>