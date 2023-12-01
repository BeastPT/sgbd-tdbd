<?php
require_once("custom/php/common.php");
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!verifyCapability("values_import")){
    die("Não tem autorização para aceder a esta página");
}

if (!array_key_exists("estado", $_REQUEST)) {
    echo "<h3>Importação de Valores - escolher criança</h3>";

    $queryChilds = "SELECT * from child ORDER BY name ASC";
    $childs = mysqli_query($sql, $queryChilds);
    if (mysqli_num_rows($childs) == 0) {
        echo "<h3>Não existem crianças</h3>";
    } else {
        echo "
            <table>
                <tr>
                    <th>Nome</th><th>Data de nascimento</th><th>Enc. de educação</th><th>Telefone do Enc.</th><th>E-mail</th>
                </tr>
        ";
        while ($child = mysqli_fetch_assoc($childs)) {
            $url = "<a href='importacao-de-valores?estado=escolheritem&crianca=".$child["id"]."'>".$child["name"]."</a>";
            echo "
                <tr>
                    <td>".$url."</td><td>".$child["birth_date"]."</td><td>".$child["tutor_name"]."</td><td>".$child["tutor_phone"]."</td><td>".$child["tutor_email"]."</td>
                </tr>
            ";
        }

        echo "</table>";
    }
} elseif ($_REQUEST['estado'] == "escolheritem") {
    $queryItemTypes = "SELECT DISTINCT it.name AS name, it.id AS id from item_type AS it, item AS i WHERE i.state = 'active' AND i.item_type_id = it.id";
    $itemTypes = mysqli_query($sql, $queryItemTypes);
    $_SESSION["childId"] = $_REQUEST["crianca"];
    echo "<ul>";

    while ($itemType = mysqli_fetch_assoc($itemTypes)) {
        echo "
            <li>".str_replace("_", " ", $itemType["name"])."</li>
                <ul>
        ";


        $queryItems = "SELECT DISTINCT i.name AS name, i.id AS id FROM item AS i, subitem AS si WHERE
            i.state = 'active' AND
            i.item_type_id = '".$itemType["id"]."' 
        ";  
        //AND
        //si.item_id = i.id
        $items = mysqli_query($sql, $queryItems);
        while ($item = mysqli_fetch_assoc($items)) {
            $url = "<a href='importacao-de-valores?estado=introducao&crianca=".$_SESSION["childId"]."&item=".$item["id"]."'>[".$item["name"]."]</a>";
            echo "<li>".$url."</li>";
        }

        echo "</ul>";
    }

    echo "</ul>";
    goBack();
} elseif ($_REQUEST['estado'] == "introducao") {
    //1 linha : em cada coluna os formfieldnames dos subitens do item clicado. Para o caso de subitens do tipo enum, repetir nesta linha o nome deste campo o número de vezes igual ao total de valores permitidos
    //2 linha : apresentar os ids dos subitens
    //3 linha : apenas para os subitens com tipo de valor enum, nas respetivas colunas, os valores permitidos.
    $_SESSION["itemId"] = mysqli_real_escape_string($sql, $_REQUEST["item"]);
    $queryVerifyItem = "SELECT i.name FROM item AS i, subitem AS si WHERE si.item_id = '".$_SESSION["itemId"]."'";
    $verifyItem = mysqli_query($sql, $queryVerifyItem);
    if (mysqli_num_rows($verifyItem) == 0) {
        echo "O item não tem subitems associados <br>";
    } else {
        $fLine = "";
        $sLine = "";
        $tLine = "";
    
        $querySubItems = "SELECT id, form_field_name AS ffn, value_type AS vt FROM subitem AS si WHERE
                si.state = 'active' AND
                si.item_id = ".$_SESSION["itemId"]."
            ";
    
        $subItems = mysqli_query($sql, $querySubItems);
        while ($subItem = mysqli_fetch_assoc($subItems)) {
            if ($subItem["vt"] == "enum") {
                $queryAllowedValues = "SELECT value from subitem_allowed_value AS sav WHERE
                    sav.state = 'active' AND
                    sav.subitem_id = ".$subItem["id"]."
                ";
                $allowedValues = mysqli_query($sql, $queryAllowedValues);
                while ($allowedVallue = mysqli_fetch_assoc($allowedValues)) {
                    $fLine .= "<td>".$subItem["ffn"]."</td>";
                    $sLine .= "<td>".$subItem["id"]."</td>";
                    $tLine .= "<td>".$allowedVallue["value"]."</td>";
                }
            } else {
                $fLine .= "<td>".$subItem["ffn"]."</td>";
                $sLine .= "<td>".$subItem["id"]."</td>";
                $tLine .= "<td></td>";
            }
        }
        
        echo "<table>";
        echo "<tr>".$fLine."</tr><tr>".$sLine."</tr><tr>".$tLine."</tr>";
        echo "</table><br>";
        echo "Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar, sendo que, no caso dos subitens enum, deverá constar um 0 quando esse valor permitido não se aplique à instância em causa e um 1 quando esse valor se aplica.
        <br> O ficheiro deve estar em /opt/lampp/htdocs/sgbd/NOME_DO_FICHEIRO, sendo o nome import_to_insert.xlsx.
        <br> <a href='importacao-de-valores?estado=insercao'><button>Carregar ficheiro</button></a><br><br>";
    }
    goBack();
} elseif ($_REQUEST['estado'] == "insercao") {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("import_to_insert.xlsx");
    $arraySheet = $spreadsheet->getActiveSheet()->toArray();
    $lines = sizeof($arraySheet);
    $columns = sizeof($arraySheet[0]);
    $currentChildId = mysqli_real_escape_string($sql, $_SESSION["childId"]);
    $currentUser = mysqli_real_escape_string($sql, wp_get_current_user()->user_login);
    $currentDate = date("Y-m-d");
    $currentTime = date("H:i:s");
    mysqli_begin_transaction($sql , MYSQLI_TRANS_START_READ_WRITE);
    $insertQuery = "INSERT INTO `value` (child_id, subitem_id, value, date, time, producer) VALUES ('%d', '%d', '%s', '%s', '%s', '%s')";
    try {
        for ($i=0; $i < $columns; $i++) {    
            $siId = mysqli_real_escape_string($sql, $arraySheet[1][$i]);
            if ($arraySheet[2][$i] != "") {
                for ($j=3; $j < $lines; $j++) {
                    if ($arraySheet[$j][$i] == 1) {
                        $insertValue = sprintf($insertQuery,
                            $currentChildId,
                            $siId,
                            mysqli_real_escape_string($sql, $arraySheet[2][$i]),
                            $currentDate,
                            $currentTime,
                            $currentUser
                        );
                        mysqli_query($sql, $insertValue);
                    }
                }
            } else {
                for ($j=3; $j < $lines; $j++) { 
                    $insertValue = sprintf($insertQuery,
                        $currentChildId,
                        $siId,
                        mysqli_real_escape_string($sql, $arraySheet[$j][$i]),
                        $currentDate,
                        $currentTime,
                        $currentUser
                    );
                    mysqli_query($sql, $insertValue);
                }
            }
            
        }
        mysqli_commit($sql);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($sql);
        echo "<br>Aconteceu algum erro! Importação cancelada!<br>";
        die($exception);
    }
    echo "<br>Importação realizada!<br>
    <a href='importacao-de-valores'><button>Inicio</button></a><br><br>";
    goBack();
} else {
    die("Algum erro aconteceu!");
}

?>