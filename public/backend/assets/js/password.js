function togglePasswordVisibility() {
    var passwordField = document.getElementById("password");
    var passwordConfirmField = document.getElementById("password_confirmation");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        passwordConfirmField.type = "text";
    } else {
        passwordField.type = "password";
        passwordConfirmField.type = "password";
    }
}
