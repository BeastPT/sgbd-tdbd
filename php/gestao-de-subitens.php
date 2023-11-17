<?php

//Requer as funções presentes no common.php
require_once("custom/php/common.php");

$link = connectDB();

//Verificação da conecção à base de dados
if (!$link) {
    echo "<p>Não está conectado à base de dados</p>";
    die("Não há Conecção: " . mysqli_connect_error());
}

//verificação de sessão e capability
if (!verifyCapability('manage_subitems')) {
    echo "<p>Não tem autorização para aceder a esta página</p>";
} else {

    echo "<p>Hello</p>";
}
