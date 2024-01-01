function validateFormGR(event) {
    let output = true;
    let params = {
        childName: document.getElementById("fullname_child").value.trim(),
        birthDate: document.getElementById("birthdate").value.trim(),
        tutorName: document.getElementById("fullname_tutor").value.trim(),
        cellphone: document.getElementById("cellphone").value.trim(),
        email: document.getElementById("email").value.trim()
    }

    document.getElementById("fullname_childError").innerHTML= "";
    document.getElementById("birthdateError").innerHTML= "";
    document.getElementById("fullname_tutorError").innerHTML = "";
    document.getElementById("cellphoneError").innerHTML = "";
    document.getElementById("emailError").innerHTML = "";

    const nameRegex = /^[\p{L}]+$/u;
    const mandatoryFieldMessage = "Campo é obrigatório!";

    if (params.childName == "") {
        document.getElementById("fullname_childError").innerHTML = mandatoryFieldMessage
        output = false;
    } else if (!nameRegex.test(params.childName)) {
        document.getElementById("fullname_childError").innerHTML= "Apenas letras e espaços são permitidos!"
        output = false;
    }

    if (params.tutorName == "") {
        document.getElementById("fullname_tutorError").innerHTML = mandatoryFieldMessage
        output = false;
    } else if (!nameRegex.test(params.tutorName)) {
        document.getElementById("fullname_tutorError").innerHTML= "Apenas letras e espaços são permitidos!"
        output = false;
    }

    const birthDateRegex = /^([2-9][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
    const timeSinceBirthdate = new Date(params.birthDate).getTime()
    if (params.birthDate == "") {
        document.getElementById("birthdateError").innerHTML= mandatoryFieldMessage
        output = false;
    } else if (birthDateRegex.test(params.birthDate) || isNaN(timeSinceBirthdate) || timeSinceBirthdate<946684800000) {
        document.getElementById("birthdateError").innerHTML= "Data inválida!";
        output = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (params.email != "" && !emailRegex.test(params.email)) {
        document.getElementById("emailError").innerHTML= "Email inválido!"
        output = false;
    }

    const phoneRegex = /^[0-9]{9}$/;
    if (params.cellphone == "") {
        document.getElementById("cellphoneError").innerHTML= mandatoryFieldMessage
        output = false;
    } else if (!phoneRegex.test(params.cellphone)) {
        document.getElementById("cellphoneError").innerHTML= "Apenas são permitidos 9 números!"
        output = false;
    }
    return output;
}

function validateFormGVP(event) {
    let output = true;
    let field = document.getElementById("value").value.trim();

    if (field == "") {
        document.getElementById("valueError").innerHTML = "Campo é obrigatório!"
        output = false;
    }

    return output;
}

function validateFormGVP(event) {
    let output = true;
    let field = document.getElementById("value").value.trim();

    if (field == "") {
        document.getElementById("valueError").innerHTML = "Campo é obrigatório!"
        output = false;
    }

    return output;
}