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

//verificação de sessão e capability
if (!verifyCapability('manage_subitems')) {
    echo "<p>Não tem autorização para aceder a esta página</p>";
} else {

    //Código a executar quando não existe estado definido
    if (!array_key_exists("estado", $_REQUEST)) {

        $query_verifica_subitens = "SELECT * FROM subitem";
        $verifica_subitens = mysqli_query($link, $query_verifica_subitens);

        if (mysqli_num_rows($verifica_subitens) == 0) {

            echo "<p>Não há subitens</p>";
        } else {

            //criação da table
            echo "<table>";

            //criação dos headers do que vai ser representado
            echo "
                <tr>
                    <th>item</th>
                    <th>id</th>
                    <th>subitem</th>
                    <th>tipo de valor</th>
                    <th>nome do campo no formulário</th>
                    <th>tipo do campo no formulário</th>
                    <th>tipo de unidade</th>
                    <th>ordem do campo no formulário</th>
                    <th>obrigatório</th>
                    <th>estado</th>
                    <th>ação</th>
                </tr>
                ";

            //query dos itens
            $query_dos_itens = "SELECT id, name FROM item";
            $itens = mysqli_query($link, $query_dos_itens);

            while ($item_rows = mysqli_fetch_assoc($itens)) {

                //query dos subitens que correspondem aos itens
                $query_dos_subitens = "SELECT * FROM subitem WHERE subitem.item_id = " . $item_rows["id"] . "";
                $subitens = mysqli_query($link, $query_dos_subitens);
                $rowspan_subitens = mysqli_num_rows($subitens);

                if ($rowspan_subitens == 0) {
                    //Se não houver subitens, é apresentada uma mensagem a dizer que não há subitens
                    echo "<tr>
                            <td>" . $item_rows['name'] . "</td>
                            <td colspan = 10>este item não tem subitens</td>";
                } else {

                    echo "<tr>
                        <td colspan = 1 rowspan='" . $rowspan_subitens . "'>" . $item_rows['name'] . "</td>
                         ";
                }

                while ($subitens_rows = mysqli_fetch_assoc($subitens)) {

                    //query das unidades
                    $query_das_unidades = "SELECT name FROM subitem_unit_type WHERE subitem_unit_type.id = " . $subitens_rows['unit_type_id'] . "";
                    $unidades = mysqli_query($link, $query_das_unidades);

                    //criação das rows que vão conter a informação com rowspan para os itens
                    echo "  
                        <td>" . $subitens_rows['id'] . "</td>
                        <td>" . $subitens_rows['name'] . "</td>
                        <td>" . $subitens_rows['value_type'] . "</td>
                        <td>" . $subitens_rows['form_field_name'] . "</td>
                        <td>" . $subitens_rows['form_field_type'] . "</td>";

                    if (!empty($subitens_rows['unit_type_id'])) {
                        while ($unidades_rows = mysqli_fetch_assoc($unidades)) {
                            echo "<td>" . $unidades_rows['name'] . "</td>";
                        }
                    } elseif (empty($subitens_rows['unit_type_id'])) {
                        echo "<td> - </td>";
                    }
                    echo "
                            <td>
                                " . $subitens_rows['form_field_order'] . "
                            </td>
                        ";
                    if ($subitens_rows['mandatory'] == 1) {
                        echo "
                                <td>
                                    Sim
                                </td>
                            ";
                    } elseif ($subitens_rows['mandatory'] == 0) {
                        echo "
                                <td>
                                    Não
                                </td>
                            ";
                    }
                    if ($subitens_rows['state'] == 'active') {
                        echo "
                                <td>
                                    Ativo
                                </td>
                            ";
                    } else {
                        echo "<td>Inativo</td>";
                    }
                    echo "<td>[editar][apagar]</td>
                    </tr>";
                }
            }
            //conclusão da table
            echo "</table>";

            echo "<h3>Gestão de subitems - introdução</h3>";

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
                Nome do subitem: <input type='text' name=subitem_name></br>
                Tipo de Valor:</br>";
            foreach ($value_type as $value) {
                echo "<input type='radio' name=value_type value=" . $value . ">" . $value . "</br>";
            }
            echo "</br>";
            //Item
            echo "Item:</br>";
            echo "<select name='item'>";
            while ($item_name_rows = mysqli_fetch_assoc($item_name)) {
                echo "<option value=" . $item_name_rows['name'] . ">" . $item_name_rows['name'] . "</option>";
            }
            echo "</select></br>";
            echo "</br>";
            //Tipo de campo no formulário
            echo "Tipo de campo no formulário:</br>";
            foreach ($form_field_type as $form_field) {
                echo "<input type='radio' name=form_field_type value=" . $form_field . ">" . $form_field . "</br>";
            }
            echo "</br>";
            //Tipo de unidade
            echo "Tipo de unidade:</br>";
            echo "<select name='unit_type'>";
            while ($tipo_de_unidade_rows = mysqli_fetch_assoc($tipo_de_unidade)) {
                echo "
                    <option value=" . $tipo_de_unidade_rows['name'] . ">
                        " . $tipo_de_unidade_rows['name'] . "
                    </option>
                ";
            }
            echo "
                </select>
                </br>
            ";
            echo "
                Ordem do campo no formulário: 
                <input type='text' name=form_field_order>
                </br>
                Obrigatório:
                </br>
            ";
            echo "
                <input type='radio' name=mandatory value=1>
                    Sim
                </br>
            ";
            echo "
                <input type='radio' name=mandatory value=0>
                    Não
                </br>
            ";

            echo "
                <input type='hidden' name='estado' value='inserir'>
                    <input type='submit'>
                </form>
            ";
        }
    } elseif ($_REQUEST['estado'] == 'inserir') {

        //validação dos campos
        $subitens_name = $_REQUEST['subitem_name'];
        $value_type = $_REQUEST['value_type'];
        $item = $_REQUEST['item'];
        $form_field_type = $_REQUEST['form_field_type'];
        $unit_type = $_REQUEST['unit_type'];
        $form_field_order = $_REQUEST['form_field_order'];
        $mandatory = $_REQUEST['mandatory'];

        //verificação dos campos
        if (!$subitens_name || !$value_type || !$item || !$form_field_type || !$form_field_order || !$mandatory) {
            echo "<p>Não pode inserir valores nulos</p>";
            goBack();
        } elseif (!is_numeric($form_field_order) || $form_field_order <= 0) {
            echo "<p>A ordem do campo no formulário tem de ser um número e superior a 0</p>";
            goBack();
        } elseif (!ctype_alpha($subitens_name)) {
            echo "<p>O nome do subitem não pode conter caractéres não alfabéticos</p>";
            goBack();
        } else {

            //query do id do item
            $query_item_id = "SELECT id FROM item WHERE item.name = '" . $item . "'";
            $item_id = mysqli_query($link, $query_item_id);
            $item_id_rows = mysqli_fetch_assoc($item_id);

            //query do id do tipo de unidade
            $query_unit_type_id = "SELECT id FROM subitem_unit_type WHERE subitem_unit_type.name = '" . $unit_type . "'";
            $unit_type_id = mysqli_query($link, $query_unit_type_id);
            $unit_type_id_rows = mysqli_fetch_assoc($unit_type_id);

            $form_field_name = concatenate($item, $item_id_rows['id'], $subitens_name);

            //query de inserção de um novo subitem
            $query_inserir_subitem = "INSERT INTO subitem (name, value_type, item_id, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state) 
                                        VALUES ('" . $subitens_name . "', '" . $value_type . "', '" . $item_id_rows['id'] . "','" . $form_field_name . "', '" . $form_field_type . "', '" . $unit_type_id_rows['id'] . "', '" . $form_field_order . "', '" . $mandatory . "', 'active')";

            $inserir_subitem = mysqli_query($link, $query_inserir_subitem);

            echo "<p>Inseriu os dados de novo subitem com sucesso</p>";
            goBack();
        }
    }
}
