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
if (!verifyCapability('manage_unit_types')) {
    echo "<p>Não tem autorização para aceder a esta página</p>";
} else {

    //Código a executar quando não existe estado
    if (!array_key_exists("estado", $_REQUEST)) {

        //query das unidades do sub itens TABLE SUBITEM_UNIT_TYPE
        $query_tipo_de_unidade = "SELECT id, name FROM subitem_unit_type ORDER BY subitem_unit_type.id";
        $tipo_de_unidade = mysqli_query($link, $query_tipo_de_unidade);

        if (mysqli_num_rows($tipo_de_unidade) == 0) {

            echo "<p>Não há tipos de unidades</p>";
        } else {

            //criação da table
            echo "<table>";

            //criação dos headers do que vai ser representado
            echo "
            <tr>
                <th>id</th>
                <th>Unidade</th>
                <th>Subitem</th>
                <th>Ação</th>
            </tr>
         ";

            while ($tipo_de_unidade_rows = mysqli_fetch_assoc($tipo_de_unidade)) {

                //query dos subitems associados aos tipos de subitem, bem como os itens associados aos subitens
                $query_dos_subitens = "SELECT subitem.name as subitem_nome, item.name as item_nome FROM subitem, item WHERE subitem.unit_type_id = " . $tipo_de_unidade_rows['id'] . " AND item.id=subitem.item_id";
                $subitens = mysqli_query($link, $query_dos_subitens);

                //criação das rows que vão conter a informação 
                echo "
                <tr>
                    <td>" . $tipo_de_unidade_rows['id'] . "</td>
                    <td>" . $tipo_de_unidade_rows['name'] . "</td>
                    <td>
                    ";
                while ($subitens_rows = mysqli_fetch_assoc($subitens)) {
                    echo "" .  $subitens_rows['subitem_nome'] . " (" . $subitens_rows['item_nome'] . "), ";
                }
                echo  "
                    </td>
                    <td>[editar][apagar]</td>
                </tr>

            ";
            }
            echo "</table>";

            echo "<h3>Gestão de unidades - introdução</h3>";

            echo "
            <form>
                Nome: <input type='text' name=tipo></br>
                <input type='hidden' name='estado' value='inserir'>
                <input type='submit'>
            </form>
        ";
        }

        //Código a executar quando o Estado é "inserir"    
    } elseif ($_REQUEST['estado'] == "inserir") {

        echo "<h3>Gestão de unidades - inserção</h3>";

        $tipo = $_REQUEST['tipo'];

        //query de inserção de um novo tipo de unidade
        $query_inserir_tipo_de_unidade = "INSERT INTO subitem_unit_type (name) VALUES ('" . $tipo . "')";
        $inserir_tipo_de_unidade = mysqli_query($link, $query_inserir_tipo_de_unidade);

        if (!$inserir_tipo_de_unidade) {
            echo "<p>Erro ao inserir o tipo de unidade</p>";
        } else {
            echo "<p>Tipo de unidade inserido com sucesso</p>";
        }
    }
    goBack();
}
