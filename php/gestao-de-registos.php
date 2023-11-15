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
        <h3>Não existem crianças</h3>
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
            echo "<u>".strtoupper($item["name"]).":</u><br>";
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
                echo "[editar][apagar] - <strong>".$edit["date"]."</strong> (".$edit["author"].") -";

                $queryValues = "
                SELECT si.name AS name, v.value AS value, si_type.name AS type
                FROM subitem AS si, value AS v, subitem_unit_type AS si_type
                WHERE
                    v.date = '".$edit["date"]."' AND
                    si.item_id = ".$item["id"]." AND
                    si_type.id = si.unit_type_id AND
                    si.id = v.subitem_id AND
                    v.child_id = ".$child["id"]."
                ORDER BY si.name ASC
                ";
                $values = mysqli_query($sql, $queryValues);
                while ($value = mysqli_fetch_assoc($values)) {
                    echo " <strong>".$value["name"]."</strong> (".$value["value"]." ".$value["type"].");";
                }
                echo "<br>";
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
echo "<h3>Dados de registo - introdução</h3>";
echo "<br><p>* Campos obrigatórios<p>";

echo '
    <form>
        <label for="fullname_child">Nome completo: </label>*<br>
        <input id ="fullname_child" type="text" name="fullname_child"><br>
        <label for="birthdate">Data de Nascimento (AAAA-MM-DD): </label>*<br>
        <input id ="birthdate" type="text" name="birthdate" placeholder="AAAA-MM-DD"><br>
        <label for="fullname_tutor">Nome completo do encarregado de educação: </label>*<br>
        <input id ="fullname_tutor" type="text" name="fullname_tutor"><br>
        <label for="cellphone">Telefone do encarregado de educação (9 dígitos): </label>*<br>
        <input id ="cellphone" type="number" name="cellphone" size="9"><br>
        <label for="email">Endereço de e-mail do tutor: </label><br>
        <input id ="email" type="email" name="email" placeholder="example@mail.com"><br>
        <input type="submit" value="Submeter">
    </form>
';

?>