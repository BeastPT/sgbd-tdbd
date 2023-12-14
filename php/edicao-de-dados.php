<?php
//Requer as funções presentes no common.php
require_once("custom/php/common.php");
//Requer o css
require_once("custom/css/ag.css");

$link = connectDB();

//Verificação da conecção à base de dados
if (!$link) {
    echo "<p>Não está conectado à base de dados</p>";
    die("Não há Conecção: " . mysqli_connect_error());
}

if (!array_key_exists("estado", $_REQUEST)) {
} elseif ($_REQUEST['estado'] == 'editar') {
} elseif ($_REQUEST['estado'] == 'ativar') {
} elseif ($_REQUEST['estado'] == 'desativar') {
} elseif ($_REQUEST['estado'] == 'apagar') {
}
