<?php
global $current_page;
$current_page = get_site_url().'/'.basename(get_permalink());

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function goBack(){
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
    <noscript>
    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
    </noscript>";
}

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