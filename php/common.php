<?php
global $current_page, $sql, $clientsideval;
$current_page = get_site_url() . '/' . basename(get_permalink());
$clientsideval = 1;
$sql = connectDB();

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function goBack()
{
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
    <noscript>
    <a href='" . $_SERVER['HTTP_REFERER'] . "‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
    </noscript>";
}

function verifyCapability($capability)
{
    return current_user_can($capability) && is_user_logged_in();
}

function connectDB()
{
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$connection)
        die("Connection Error: " . mysqli_error());

    return $connection;
}

function get_enum_values($table, $column)
{
    $sql = connectDB();
    $query = " SHOW COLUMNS FROM `$table` LIKE '$column' ";
    $result = mysqli_query($sql, $query);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $regex = "/'(.*?)'/";
    preg_match_all($regex, $row[1], $enum_array);
    $enum_fields = $enum_array[1];
    return ($enum_fields);
}
