<?php
require_once("custom/php/common.php");

if (!verifyCapability("manage_allowed_values")){
    die("Não tem autorização para aceder a esta página");
}

if (!array_key_exists("estado", $_REQUEST)) {
    $queryItems = "SELECT DISTINCT i.name AS item, i.id AS id FROM item AS i, subitem AS si WHERE si.value_type = 'enum' AND si.item_id = i.id ORDER BY item";
    $items = mysqli_query($sql, $queryItems);
    if (mysqli_num_rows($items) == 0) {
        die("NENHUM ITEM ASSOCIADO");
    } else {
        echo "
            <table>
                <tr>
                    <th>item</th><th>id</th><th>subitem</th><th>id</th><th>valores permitidos</th><th>estado</th><th>ação</th>
                </tr>
            ";
        while ($item = mysqli_fetch_assoc($items)) {
            $counter = 0;
            $queryAmountRows = "SELECT * FROM subitem AS si LEFT JOIN subitem_allowed_value AS av ON si.id = av.subitem_id WHERE si.value_type = 'enum' AND si.item_id = '".$item["id"]."'";
            $AmountRows = mysqli_query($sql, $queryAmountRows);
            $rows = mysqli_num_rows($AmountRows);
            echo "<tr>
                <td rowspan='".$rows."'>".$item["item"]."</td>
            ";
            
            $querySubItems = "SELECT si.id AS id, si.name AS name FROM subitem AS si WHERE si.value_type = 'enum' AND si.item_id = '".$item["id"]."'";
            $subItems = mysqli_query($sql, $querySubItems);
            while ($subItem = mysqli_fetch_assoc($subItems)) {
                $queryAllowedValues = "SELECT av.id AS id, av.value AS value, av.state AS state FROM subitem_allowed_value AS av WHERE av.subitem_id = '".$subItem["id"]."'";
                $allowedValues = mysqli_query($sql, $queryAllowedValues);
                $amtAllowed = mysqli_num_rows($allowedValues);
                $url = "<a href='gestao-de-valores-permitidos?estado=introducao&subitem=".$subItem["id"]."'>[".$subItem["name"]."]</a>";
                if ($amtAllowed == 0) {
                    echo "<td>".$subItem["id"]."</td><td>".$url."</td><td colspan='4'>Não há valores permitidos definidos</td></tr>";
                } else {
                    echo "<td rowspan='".$amtAllowed."'>".$subItem["id"]."</td><td rowspan='".$amtAllowed."'>".$url."</td>";
                    while ($allowedValue = mysqli_fetch_assoc($allowedValues)) {
                        echo "<td>".$allowedValue["id"]."</td><td>".$allowedValue["value"]."</td><td>".$allowedValue["state"]."</td><td>[editar][desativar][apagar]</td></tr>";
                    }
                }
            }
        }
        echo "
            </table>
        ";
    }

} elseif ($_REQUEST['estado'] == "introducao") {
    $_SESSION["subitem_id"] = $_REQUEST["subitem"];
    echo "<h3>Gestão de valores permitidos - introdução</h3>";
    echo "<br><p>* Obrigatório<p>";

    //action='".$current_page."'
    $clientSideVerification = ($clientsideval) ? "onsubmit='return validateForm(this)'" : "";
    echo "
        <form method='post' ".$clientSideVerification.">
            <label for='value'>Valor: </label>*
            <span class='fieldError' id='valueError'></span>
            <input id ='value' type='text' name='value'><br>
            <input type='hidden' name='estado' value='inserir'>
            <input type='submit' value='Inserir valor permitido'>
        </form>
    ";
    goBack();
} elseif ($_REQUEST['estado'] == "inserir") {
    echo "<h3>Gestão de valores permitidos - inserção</h3>";
    $valor = test_input($_REQUEST["value"]);
    if(empty($valor)) {
        echo "O campo <strong>'Valor'</strong> é obrigatório.";
    } else {
        $insertAllowed = sprintf("INSERT INTO subitem_allowed_value (subitem_id, value, state) VALUES ('%d', '%s', '%s')",
            mysqli_real_escape_string($sql, $_SESSION["subitem_id"]),
            mysqli_real_escape_string($sql, $valor),
            'active'
        );

        if (mysqli_query($sql, $insertAllowed)) {
            echo "
            <strong>Inseriu os dados do novo valor permitido com sucesso.<br>Clique em Continuar para avançar</strong> <br>
            <a href='gestao-de-valores-permitidos'><button>Continuar</button></a>
            ";
        } else {
            die("Erro na Query ".$insertAllowed." <br> ".mysqli_error($sql));
        }
    }
    goBack();
} else {
    die("Algum erro aconteceu!");
}
?>

<script src="/sgbd/custom/js/gestao-de-valores-permitidos.js"> </script>