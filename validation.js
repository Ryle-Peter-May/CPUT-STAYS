document.addEventListener("DOMContentLoaded", ()=>{
    const validation = new JustValidate('#register-form');

    validation
    .addField("#id_num", [
        {rule: "required", errorMessage: "ID numnber is required"},
    ])

    .addField("#first_name", [
        {rule: "required", errorMessage: "First name is required"},
    ])

    .addField("#last_name", [
        {rule: "required", errorMessage: "Last name is required"},
    ])

    .addField("#stud_number", [
        {rule: "required", errorMessage: "Student number is required"},
    ])

    .addField("#email", [
        {rule: "required", errorMessage: "Email is required"},
        {rule: "email", errorMessage: "Email is invalid"},
        {
            validator: (value)=>{
                return fetch("validate-email.php?email="+ endCodeURIComponent(value))
                .then((response) => response.json())
                .then((json)=> json.available);
            },
            errorMessage: "Email already exists",

        },
    ])

    .addField("#password",[
        { rule: "required", errorMessage: "Password is required"},
        {
            validator: (value) => value.length >=8,
            errorMessage: "Password must be at least 8 characters long",
        },
    ])
});