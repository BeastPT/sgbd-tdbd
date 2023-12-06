function validateForm(event) {
    let output = true;
    let params = {
        childName: document.getElementById("fullname_child").value,
        birthDate: document.getElementById("birthdate").value,
        tutorName: document.getElementById("fullname_tutor").value,
        cellphone: document.getElementById("cellphone").value,
        email: document.getElementById("email").value
    }

    let keys = Object.keys(params)


    e.forEach(key => {
        params[key] = params[key].trim();
    });

    let fullname_childError = document.getElementById("fullname_childError").innerHTML;
    fullname_childError = "";
    let birthdateError = document.getElementById("birthdateError").innerHTML;
    birthdateError = "";
    let fullname_tutorError = document.getElementById("fullname_tutorError").innerHTML;
    fullname_tutorError = "";
    let cellphoneError = document.getElementById("cellphoneError").innerHTML;
    cellphoneError = "";
    let emailError = document.getElementById("emailError").innerHTML;
    emailError = "";

    const nameRegex = /^[\p{L}]+$/u;
    const mandatoryFieldMessage = "Campo é obrigatório!";

    if (params.childName == "") {
        fullname_childError = mandatoryFieldMessage
        output = false;
    } else if (!nameRegex.test(params["childName"])) {
        fullname_childError = "Apenas letras e espaços são permitidos!"
        output = false;
    }

    const birthDateRegex = /^([2-9][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
    const timeSinceBirthdate = new Date(params.birthDate).getTime()
    if (params.birthDate == "") {
        fullname_childError = mandatoryFieldMessage
        output = false;
    } else if (birthDateRegex.test(params.birthDate) || isNaN(timeSinceBirthdate) || timeSinceBirthdate<946684800000) {
        birthdateError = "Data inválida!";
        output = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (params.email != "" && !emailRegex.test(params.email)) {
        emailError = "Email inválido!"
        output = false;
    }

    const phoneRegex = /^[0-9]{9}$/;
    if (params.cellphone == "") {
        cellphoneError = mandatoryFieldMessage
        output = false;
    } else if (!phoneRegex.test(params.cellphone)) {
        cellphoneError = "Apenas são permitidos 9 números!"
        output = false;
    }
    if (!output) {
        event.preventDefault();
    }
    
    return output;
}