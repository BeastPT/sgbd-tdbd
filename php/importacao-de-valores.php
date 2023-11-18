<?php
require_once("custom/php/common.php");
//require 'vendor/autoload.php';

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function r($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

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


        $queryItems = "SELECT DISTINCT i.name AS name, i.id AS id from item AS i, subitem AS si WHERE
            i.state = 'active' AND
            i.item_type_id = '".$itemType["id"]."' AND
            si.item_id = i.id
        ";  
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
    $_SESSION["itemId"] = $_REQUEST["item"];

    $fLine = "";
    $sLine = "";
    $tLine = "";

    $querySubItems = "SELECT id, form_field_name AS ffn, value_type AS vt from subitem AS si WHERE
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
    <br> O ficheiro deve estar em /Applications/XAMPP/xamppfiles/htdocs/WP/wordpress/NOME_DO_FICHEIRO, sendo o nome import_to_insert.xlsx.
    <br> <a href='importacao-de-valores?estado=insercao'><button>Carregar ficheiro</button></a><br><br>";
    goBack();
} elseif ($_REQUEST['estado'] == "insercao") {
    echo "<br>Importação realizada!<br>
    <a href='importacao-de-valores'><button>Inicio</button></a><br><br>";
    goBack();
} else {
    die("Algum erro aconteceu!");
}

?>