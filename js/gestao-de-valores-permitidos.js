function validateForm(event) {
    let output = true;
    let field = document.getElementById("value").value.trim();

    if (field == "") {
        document.getElementById("valueError").innerHTML = "Campo é obrigatório!"
        output = false;
    }

    return output;
}