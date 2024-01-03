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
if ($_GET['comp'] == 'gestao-de-unidades') {
    $id = $_GET['id'];
    $query_tipo_de_unidade = "SELECT * FROM subitem_unit_type WHERE id = $id";
    $tipo_de_unidade = mysqli_query($link, $query_tipo_de_unidade);
    $tipo_de_unidade_row = mysqli_fetch_assoc($tipo_de_unidade);
    if (!$tipo_de_unidade_row) {
        echo "<p>Id não encontrado</p>";
    } else {
        if ($_GET['estado'] == 'editar') {
            echo "
        <form >
            <table>
                <tr>
                    <th>Id</th>
                    <th>Nome</th>
                </tr>
                <tr>
                    <td class = 'id'>
                        " . $tipo_de_unidade_row['id'] . "
                    </td>
                    <td> 
                        <input type='text' id='name' value='" . $tipo_de_unidade_row['name'] . "'>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='estado' value='editado'>
            <input type='hidden' name='comp' value='gestao-de-unidades'>
            <input type='hidden' name='id' value='" . $tipo_de_unidade_row['id'] . "'>
            <input type='submit' value='Submeter'>
        </form>
        ";
            goBack();
        } elseif ($_GET['estado'] == 'apagar') {
            echo "
            <p>
                Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?
            </p>
        ";
            echo "
        <form>
            <table>
                <tr>
                    <th>Id</th>
                    <th>Nome</th>
                </tr>
                <tr>
                    <td class = 'id'>
                        " . $tipo_de_unidade_row['id'] . "
                    </td>
                    <td> 
                        " . $tipo_de_unidade_row['name'] . "
                    </td>
                </tr>
            </table>
            <input type='hidden' name='estado' value='apagado'>
            <input type='hidden' name='comp' value='gestao-de-unidades'>
            <input type='hidden' name='id' value='" . $tipo_de_unidade_row['id'] . "'>
            <input type='submit' value='Submeter'>
        </form>
        ";
            goBack();
        } elseif ($_GET['estado'] == 'editado') {
            $name = $_GET['name'];
            $query_editar_dados = "UPDATE subitem_unit_type SET name = '$name' WHERE id = $id";
            $editar_dados = mysqli_query($link, $query_editar_dados);
            if (!$editar_dados) {
                echo "<p>Não pode introduzir dados nulos</p>";
                goBack();
            } else {
                echo "<p>Edições realizadas com sucesso</p>";
            }
            echo "
                <a href='gestao-de-unidades'>
                    Continuar
                </a>
            ";
        } elseif ($_GET['estado'] == 'apagado') {
            $query_apagar_dados = "DELETE FROM subitem_unit_type WHERE id = $id";
            $apagar_dados = mysqli_query($link, $query_apagar_dados);
            echo "<p>Eliminações realizadas com sucesso</p>";
            echo "
                <a href='gestao-de-unidades'>
                    Continuar
                </a>
            ";
            echo "</br>";
        }
    }
} elseif ($_GET['comp'] == 'gestao-de-subitens') {
} elseif ($_REQUEST['comp'] == 'gestao-de-itens') {
}
