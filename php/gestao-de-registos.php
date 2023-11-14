<?php
require_once("custom/php/common.php");
$sql = connectDB();

function r($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

$queryChilds = "SELECT * from child ORDER BY name ASC";
$childs = mysqli_query($sql, $queryChilds);
if (mysqli_num_rows($childs) == 0) {
    echo "
        <h3>Não há crianças</h3>
    ";
} else {
    echo "
        <table>
            <tr>
                <th>Nome</th><th>Data de nascimento</th><th>Enc. de educação</th><th>Telefone do Enc.</th><th>E-mail</th><th>Registos</th>
            </tr>
    ";
    while ($child = mysqli_fetch_assoc($childs)) {
        echo "
            <tr>
                <td>".$child["name"]."</td><td>".$child["birth_date"]."</td><td>".$child["tutor_name"]."</td><td>".$child["tutor_phone"]."</td><td>".$child["tutor_email"]."</td><td>
            
        ";
        // Reformular para: Pegar todas as Item c/ values > Escrever Item > Pegar todos os values > Escrever Val 

        $queryItems = "SELECT DISTINCT i.name AS name, i.id as id FROM item AS i, subitem AS si, value AS v WHERE v.subitem_id = si.id AND i.id = si.item_id AND v.child_id = ".$child["id"]." ORDER BY subitem_id ASC";
        $items = mysqli_query($sql, $queryItems);
        while ($item = mysqli_fetch_assoc($items)) {
            echo "<br>".strtoupper($item["name"]).":<br>";
            $queryEdits = "
            SELECT DISTINCT v.date AS date, v.producer as author
            FROM value AS v, subitem AS si WHERE
                v.child_id = ".$child["id"]." AND
                si.item_id = ".$item["id"]." AND
                si.id = v.subitem_id
            ORDER BY si.name ASC
            ";
            $edits = mysqli_query($sql, $queryEdits);
            while ($edit = mysqli_fetch_assoc($edits)) {
                echo "<br>[editar][apagar] - <b>".$edit["date"]."</b> (".$edit["author"].") -";

                $queryValues = "
                SELECT v.date AS date, v.producer AS author, si.name AS name, v.value AS value, si_type.name AS type
                FROM subitem AS si, value AS v, subitem_unit_type AS si_type
                WHERE
                    v.date = ".$edit["date"]." AND
                    si.item_id = ".$item["id"]." AND
                    si_type.id = si.unit_type_id AND
                    si.id = v.subitem_id AND
                    v.child_id = ".$child["id"]."
                ORDER BY si.name ASC
                ";
                $values = mysqli_query($sql, $queryValues);
                while ($value = mysqli_fetch_assoc($values)) {
                    r($value);
                    //echo "
                    //<b>".$value["name"]."</b> (".$value["value"]." ".$value["type"]."); 
                    //";
                }
            }
        }
        echo "
                </td>
            </tr>
        ";
    }
    
    echo "
        </table>
    ";
}

?>