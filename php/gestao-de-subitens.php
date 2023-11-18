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

            ////criação dos headers do que vai ser representado
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

                    echo "<tr>
                            <td>" . $item_rows['name'] . "</td>
                            <td colspan = 10> Não há subitens</td>";
                } else {

                    echo "<tr>
                        <td colspan = 1 rowspan='" . $rowspan_subitens . "'>" . $item_rows['name'] . "</td>
                         ";
                }

                while ($subitens_rows = mysqli_fetch_assoc($subitens)) {

                    //criação das rows que vão conter a informação com rowspan para os itens
                    //Se não houver subitens, é apresentada uma mensagem a dizer que não há subitens

                    echo "  
                        <td>" . $subitens_rows['id'] . "</td>
                        <td>" . $subitens_rows['name'] . "</td>
                        <td>" . $subitens_rows['value_type'] . "</td>
                        <td>" . $subitens_rows['form_field_name'] . "</td>
                        <td>" . $subitens_rows['form_field_type'] . "</td>
                        <td>" . $subitens_rows['unit_type_id'] . "</td>
                        <td>" . $subitens_rows['form_field_order'] . "</td>
                        <td>" . $subitens_rows['mandatory'] . "</td>
                        <td>" . $subitens_rows['state'] . "</td>
                        <td>[editar][apagar]</td>
                    </tr>";
                }
            }
            //conclusão da table
            echo "</table>";


            echo "<h3>Gestão de subitems - introdução</h3>";

            echo "
            <form>
                Nome do subitem: <input type='text' name=subitem></br>
                Tipo de Valor: <input type='radio' name=value_type></br>
                Item: <select name='item'>
                <input type='hidden' name='estado' value='inserir'>
                <input type='submit'>
            </form>
            ";
        }
    } elseif ($_REQUEST['estado'] == 'inserir') {
    }
}
