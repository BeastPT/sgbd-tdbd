<?php
require_once("custom/php/common.php");
$sql = connectDB();
echo "
    <table>
        <tr>
            <th>Nome</th><th>Data de nascimento</th><th>Enc. de educação</th><th>Telefone do Enc.</th><th>E-mail</th><th>Registos</th>
        </tr>
";

$query = "SELECT * from child";
$childs = mysqli_query($sql, $query);
while ($child = mysqli_fetch_assoc($childs)) {
    echo "
        <tr>
            <td>".$child["name"]."</td><td>".$child["birth_date"]."</td><td>".$child["tutor_name"]."</td><td>".$child["tutor_phone"]."</td><td>".$child["tutor_email"]."</td><td>Registos</td>
        </tr>
    ";
}

echo "
    </table>
";
?>