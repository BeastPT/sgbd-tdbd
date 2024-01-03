<?php
//Requer as funções presentes no common.php
require_once("custom/php/common.php");
//Requer o css
require_once("custom/css/ag.css");

$link = connectDB();
//Função para concatenar as 3 primeiras letras do primeiro parâmetro, id do segundo parametro e terceiro parametro
function concatenate($nome_item, $id_item, $nome_subitem)
{
    //faz com que o nome do item tenha apenas 3 letras
    $nome_item = substr($nome_item, 0, 3);
    //faz as substituições necessárias para que o nome do subitem deixe de ter espaços
    $nome_subitem = str_replace(" ", "_", $nome_subitem);

    //concatenação dos 3 parametros com - entre eles
    $concatenated = $nome_item . "-" . $id_item . "-" . $nome_subitem;
    return $concatenated;
}

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
                        <input type='text' name='nome' value='" . $tipo_de_unidade_row['name'] . "'>
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
            $name = $_GET['nome'];
            $query_editar_dados = "UPDATE subitem_unit_type SET name = '$name' WHERE id = $id";
            if (!$name) {
                echo "<p>Não pode introduzir valores nulos</p>";
                goBack();
                echo "</br>";
            } else {
                $editar_dados = mysqli_query($link, $query_editar_dados);

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
    $id = $_GET['id'];
    $query_subitem = "SELECT * FROM subitem WHERE id = $id";
    $subitem = mysqli_query($link, $query_subitem);
    $subitem_row = mysqli_fetch_assoc($subitem);
    if (!$subitem_row) {
        echo "<p>
                Id não encontrado
              </p>";
    } else {
        if ($_GET['estado'] == 'editar') {
            //query dos nomes dos itens
            $item_name_query = "SELECT name FROM item";
            $item_name = mysqli_query($link, $item_name_query);

            //query dos tipos de unidade
            $query_tipo_de_unidade = "SELECT name FROM subitem_unit_type";
            $tipo_de_unidade = mysqli_query($link, $query_tipo_de_unidade);

            //query dos tipos de valor
            $value_type = get_enum_values("subitem", "value_type");

            //query dos tipos de campo no formulário
            $form_field_type = get_enum_values("subitem", "form_field_type");
            echo "
                <form>
                    <table>
                        <tr>
                            <th>
                                Id
                            </th>
                            <th>
                                name
                            </th>
                            <th>
                                item_id
                            </th>
                            <th>
                                value_type
                            </th>
                            <th>
                                form_field_name
                            </th>
                            <th>
                                form_field_type
                            </th>
                            <th>
                                unit_type_id
                            </th>
                            <th>
                                form_field_order
                            </th>
                            <th>
                                mandatory
                            </th>
                            <th>
                                state
                            </th>
                        </tr>
                        <tr>
                            <td class='id'>
                                " . $subitem_row['id'] . "
                            </td>
                            <td>
                                <input type='text' name='nome' value='" . $subitem_row['name'] . "'>
                            </td>
                            <td>
                                <select name='item'>";
            while ($item_name_rows = mysqli_fetch_assoc($item_name)) {
                echo "<option value=" . $item_name_rows['name'] . ">" . $item_name_rows['name'] . "</option>";
            }
            echo "
                                </select>
                            </td>
                            <td>
                                " . $subitem_row['value_type'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_name'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_type'] . "
                            </td>
                            <td>
                                <select name='unit_type'>";
            while ($tipo_de_unidade_rows = mysqli_fetch_assoc($tipo_de_unidade)) {
                echo "
                                    <option value=" . $tipo_de_unidade_rows['name'] . ">
                                        " . $tipo_de_unidade_rows['name'] . "
                                    </option>
                                ";
            }
            echo "
                                </select>
                            </td>
                            <td>
                                <input type='text' name='form_field_order' value='" . $subitem_row['form_field_order'] . "'>
                            </td>
                            <td>
                                <select name='mandatory' selected=" . $subitem_row['mandatory'] . ">
                                    <option value='1'>
                                        Sim
                                    </option>
                                    <option value='0'>
                                        Não
                                    </option>
                                </select>
                            </td>
                            <td>
                                " . $subitem_row['state'] . "
                            </td>
                        </tr>
                    </table>
                    <input type='hidden' name='estado' value='editado'>
                    <input type='hidden' name='comp' value='gestao-de-subitens'>
                    <input type='hidden' name='id' value='" . $subitem_row['id'] . "'>
                    <input type='submit' value='Submeter'>
                </form>
            ";
            goBack();
        }
        if ($_GET['estado'] == 'ativar') {
            echo "
                <p>
                    Pretende ativar o item?
                </p>
                <form>
                <table>
                <tr>
                            <th>
                                Id
                            </th>
                            <th>
                                name
                            </th>
                            <th>
                                item_id
                            </th>
                            <th>
                                value_type
                            </th>
                            <th>
                                form_field_name
                            </th>
                            <th>
                                form_field_type
                            </th>
                            <th>
                                unit_type_id
                            </th>
                            <th>
                                form_field_order
                            </th>
                            <th>
                                mandatory
                            </th>
                            <th>
                                state
                            </th>
                        </tr>
                        <tr>
                            <td class='id'>
                                " . $subitem_row['id'] . "
                            </td>
                            <td>
                                " . $subitem_row['name'] . "
                            </td>
                            <td>
                                " . $subitem_row['item_id'] . "
                            </td>
                            <td>
                                " . $subitem_row['value_type'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_name'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_type'] . "
                            </td>
                            <td>
                                " . $subitem_row['unit_type_id'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_order'] . "
                            </td>
                            <td>
                                " . $subitem_row['mandatory'] . "
                            </td>
                            <td class='ative'>
                                " . $subitem_row['state'] . "
                            </td>

                </table>
                <input type='hidden' name='estado' value='ativado'>
                <input type='hidden' name='comp' value='gestao-de-subitens'>
                <input type='hidden' name='id' value='" . $subitem_row['id'] . "'>
                <input type='submit' value='Submeter'>
                </form>
            ";
        }
        if ($_GET['estado'] == 'desativar') {
            echo "
                <p>
                    Pretende desativar o item?
                </p>
                <form>
                <table>
                    <tr>
                        <th>
                            Id
                        </th>
                        <th>
                            name
                        </th>
                        <th>
                            item_id
                        </th>
                        <th>
                            value_type
                        </th>
                        <th>
                            form_field_name
                        </th>
                        <th>
                            form_field_type
                        </th>
                        <th>
                            unit_type_id
                        </th>
                        <th>
                            form_field_order
                        </th>
                        <th>
                            mandatory
                        </th>
                        <th>
                            state
                        </th>
                    </tr>
                    <tr>
                        <td class='id'>
                            " . $subitem_row['id'] . "
                        </td>
                        <td>
                            " . $subitem_row['name'] . "
                        </td>
                        <td>
                            " . $subitem_row['item_id'] . "
                        </td>
                        <td>
                            " . $subitem_row['value_type'] . "
                        </td>
                        <td>
                            " . $subitem_row['form_field_name'] . "
                        </td>
                        <td>
                            " . $subitem_row['form_field_type'] . "
                        </td>
                        <td>
                            " . $subitem_row['unit_type_id'] . "
                        </td>
                        <td>
                            " . $subitem_row['form_field_order'] . "
                        </td>
                        <td>
                            " . $subitem_row['mandatory'] . "
                        </td>
                        <td class='ative'>
                            " . $subitem_row['state'] . "
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='estado' value='desativado'>
                <input type='hidden' name='comp' value='gestao-de-subitens'>
                <input type='hidden' name='id' value='" . $subitem_row['id'] . "'>
                <input type='submit' value='Submeter'>
                </form>
            ";
        }
        if ($_GET['estado'] == 'apagar') {
            echo "
                <p>
                    Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?
                </p>
                <form>
                <table>
                <tr>
                            <th>
                                Id
                            </th>
                            <th>
                                name
                            </th>
                            <th>
                                item_id
                            </th>
                            <th>
                                value_type
                            </th>
                            <th>
                                form_field_name
                            </th>
                            <th>
                                form_field_type
                            </th>
                            <th>
                                unit_type_id
                            </th>
                            <th>
                                form_field_order
                            </th>
                            <th>
                                mandatory
                            </th>
                            <th>
                                state
                            </th>
                        </tr>
                        <tr>
                            <td class='id'>
                                " . $subitem_row['id'] . "
                            </td>
                            <td>
                                " . $subitem_row['name'] . "
                            </td>
                            <td>
                                " . $subitem_row['item_id'] . "
                            </td>
                            <td>
                                " . $subitem_row['value_type'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_name'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_type'] . "
                            </td>
                            <td>
                                " . $subitem_row['unit_type_id'] . "
                            </td>
                            <td>
                                " . $subitem_row['form_field_order'] . "
                            </td>
                            <td>
                                " . $subitem_row['mandatory'] . "
                            </td>
                            <td>
                                " . $subitem_row['state'] . "
                            </td>
                        </tr>

                </table>
                <input type='hidden' name='estado' value='apagado'>
                <input type='hidden' name='comp' value='gestao-de-subitens'>
                <input type='hidden' name='id' value='" . $subitem_row['id'] . "'>
                <input type='submit' value='Submeter'>
                </form>
            ";
        }
        if ($_GET['estado'] == 'editado') {
            $id = $_GET['id'];
            $name = $_GET['nome'];
            $item = $_GET['item'];
            $unit_type = $_GET['unit_type'];
            $form_field_order = $_GET['form_field_order'];
            $mandatory = $_GET['mandatory'];

            //query do id do item
            $query_item_id = "SELECT id FROM item WHERE item.name = '" . $item . "'";
            $item_id = mysqli_query($link, $query_item_id);
            $item_id_rows = mysqli_fetch_assoc($item_id);

            //query do id do tipo de unidade
            $query_unit_type_id = "SELECT id FROM subitem_unit_type WHERE subitem_unit_type.name = '" . $unit_type . "'";
            $unit_type_id = mysqli_query($link, $query_unit_type_id);
            $unit_type_id_rows = mysqli_fetch_assoc($unit_type_id);

            $form_field_name = concatenate($item, $id, $name);

            $query_editar_dados = "UPDATE subitem
                                    SET name = '$name', item_id = " . $item_id_rows['id'] . ", form_field_name = '$form_field_name', unit_type_id = " . $unit_type_id_rows['id'] . ", form_field_order = $form_field_order, mandatory = " . $mandatory . " 
                                    WHERE id = $id";
            if (!$name || !$item_id_rows || !$form_field_name || !$unit_type_id_rows || !$form_field_order || !$mandatory) {
                echo "<p>Não pode introduzir valores nulos</p>";
                goBack();
                echo "</br>";
            } else {
                $editar_dados = mysqli_query($link, $query_editar_dados);

                echo "<p>Edições realizadas com sucesso</p>";
            }
            echo "
                <a href='gestao-de-subitens'>
                    Continuar
                </a>
            ";
        }
        if ($_GET['estado'] == 'ativado') {
            $query_ativar_dados = "UPDATE subitem SET state = 'active' WHERE id = $id";
            $ativar_dados = mysqli_query($link, $query_ativar_dados);
            echo "<p>Ativação realizada com sucesso</p>";
            echo "
                <a href='gestao-de-subitens'>
                    Continuar
                </a>
            ";
            echo "</br>";
        }
        if ($_GET['estado'] == 'desativado') {
            $query_desativar_dados = "UPDATE subitem SET state = 'inactive' WHERE id = $id";
            $desativar_dados = mysqli_query($link, $query_desativar_dados);
            echo "<p>Desativação realizada com sucesso</p>";
            echo "
                <a href='gestao-de-subitens'>
                    Continuar
                </a>
            ";
            echo "</br>";
        }
        if ($_GET['estado'] == 'apagado') {
            $query_apagar_dados = "DELETE FROM subitem WHERE id = $id";
            $apagar_dados = mysqli_query($link, $query_apagar_dados);
            echo "<p>Eliminações realizadas com sucesso</p>";
            echo "
                <a href='gestao-de-subitens'>
                    Continuar
                </a>
            ";
            echo "</br>";
        }
    }
}
