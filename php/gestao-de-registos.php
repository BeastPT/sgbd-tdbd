<?php
require_once("custom/php/common.php");

function validade_input($data, $type) {
    if (empty($data)) {
        return "Campo obrigatório em falta<br>";
    }
    if($type == "name") {
        if (!preg_match("/^[\p{L}]+$/u", $data)) {
            return "Apenas letras e espaços são permitidos no nome.<br>";
        }
    } elseif($type == "date") {
        $time = strtotime($data);
        if (!preg_match("/^([2-9][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/", $data) || !$time || $time<(-1580688000))
            return "Data inválida<br>";
    } elseif($type == "email") {
        if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return "E-mail em formato inválido.<br>";
        }
    } elseif($type == "phone") { // verificar melhor
        if (!preg_match("/^[0-9]{9}$/",$data)) {
            return "Apenas são permitidos 9 números.<br>";
        }
    }
}

function r($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

if (!verifyCapability("manage_records")){
    die("Não tem autorização para aceder a esta página");
}

if (!array_key_exists("state",$_REQUEST)) {
    $queryChilds = "SELECT * from child ORDER BY name ASC";
    $childs = mysqli_query($sql, $queryChilds);
    if (mysqli_num_rows($childs) == 0) {
        echo "<h3>Não existem crianças</h3>";
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

            $queryItems = "SELECT DISTINCT i.name AS name, i.id as id FROM item AS i, subitem AS si, value AS v WHERE v.subitem_id = si.id AND i.id = si.item_id AND v.child_id = ".$child["id"]." ORDER BY subitem_id ASC";
            $items = mysqli_query($sql, $queryItems);
            while ($item = mysqli_fetch_assoc($items)) {
                echo "<u>".strtoupper($item["name"]).":</u><br>";
                $queryEdits = "
                SELECT DISTINCT v.date AS date, v.producer as author
                FROM value AS v, subitem AS si WHERE
                    v.child_id = '".$child["id"]."' AND
                    si.item_id = '".$item["id"]."' AND
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
                        si.item_id = '".$item["id"]."' AND
                        si_type.id = si.unit_type_id AND
                        si.id = v.subitem_id AND
                        v.child_id = '".$child["id"]."'
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

    echo "
        <form method='post' action='".$current_page."'.>
            <label for='fullname_child'>Nome completo: </label>*<br>
            <input id ='fullname_child' type='text' name='fullname_child'><br>
            <label for='birthdate'>Data de Nascimento (AAAA-MM-DD): </label>*<br>
            <input id ='birthdate' type='text' name='birthdate' placeholder='AAAA-MM-DD'><br>
            <label for='fullname_tutor'>Nome completo do encarregado de educação: </label>*<br>
            <input id ='fullname_tutor' type='text' name='fullname_tutor'><br>
            <label for='cellphone'>Telefone do encarregado de educação (9 dígitos): </label>*<br>
            <input id ='cellphone' type='text' name='cellphone' size='9'><br>
            <label for='email'>Endereço de e-mail do tutor: </label><br>
            <input id ='email' type='text' name='email' placeholder='example@mail.com'><br>
            <input type='hidden' name='state' value='confirmData'>
            <input type='submit' value='Submeter'>
        </form>
    ";
} elseif ($_REQUEST['state'] == "confirmData") {
    // Validar os dados
    $data = [];
    foreach ($_REQUEST as $key => $value) {
        $data[$key] = test_input($value);
    }

    $dataErrors = validade_input($data["fullname_child"], "name");
    $dataErrors .= validade_input($data["birthdate"], "date");
    $dataErrors .= validade_input($data["fullname_tutor"], "name");
    $dataErrors .= validade_input($data["cellphone"], "phone");
    if (!empty($data["email"])) {
        $dataErrors .= validade_input($data["email"],"email");
    }

    if (empty($dataErrors)) { // Dadados válidos
        echo "
            <h3>Dados de registo - validação</h3>

            <p>Estamos prestes a inserir os dados abaixo na base de dados.</p><br>
            <p>Confirma que os dados estão corretos e pretende submeter os mesmos?</p><br>
        ";

        echo "
            <strong>Nome: </strong> ".$data["fullname_child"]."
            <strong>Data de Nascimento: </strong> ".$data["birthdate"]."
            <strong>Enc. de educação: </strong> ".$data["fullname_tutor"]."
            <strong>Telefone do Enc.: </strong> ".$data["cellphone"]."
            <strong>e-mail: </strong> ".$data["email"]."<br><br>

        	<form method='post' action='".$current_page."'.>
                <input type='hidden' name='fullname_child' value=".$data["fullname_child"].">
                <input type='hidden' name='birthdate' value=".$data["birthdate"].">
                <input type='hidden' name='fullname_tutor' value=".$data["fullname_tutor"].">
                <input type='hidden' name='cellphone' value=".$data["cellphone"].">
                <input type='hidden' name='email' value=".$data["email"].">
                <input type='hidden' name='state' value='insertData'>
                <input type='submit' value='Submeter'>
            </form>

            <br>
        ";
    } else { // Dadods inválidos
        echo $dataErrors;
    }
    goBack();
} elseif ($_REQUEST['state'] == "insertData") {

    echo "<h3>Dados de registo - inserção</h3>";

    foreach ($_REQUEST as $key => $value) {
        $data[$key] = mysqli_real_escape_string($sql, $value);
    }

    $insertChild = sprintf("INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email) VALUES ('%s', '%s', '%s', '%s', '%s')",
        $data["fullname_child"],
        $data["birthdate"],
        $data["fullname_tutor"],
        $data["cellphone"],
        $data["email"]
    );
    if (mysqli_query($sql, $insertChild)) {
        echo "
            Nome: ".$data["fullname_child"]." <br>
            Data de Nascimento: ".$data["birthdate"]." <br>
            Enc. de educação: ".$data["fullname_tutor"]." <br>
            Telefone do Enc.: ".$data["cellphone"]." <br>
            e-mail: ".$data["email"]."<br>

            <strong>Inseriu os dados de registo com sucesso.<br>Clique em Continuar para avançar</strong> <br>
            <a href='gestao-de-registos'><button>Continuar</button></a>
        ";
    } else {
        die("ERROR ".$insertChild." <br> ".mysqli_error($sql));
    }
} else {
    die("Algum erro aconteceu!");
}

?>