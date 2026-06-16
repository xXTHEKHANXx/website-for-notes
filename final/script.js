// Login Button
document.getElementById("login-btn").addEventListener("click", function () {
    let email = document.querySelectorAll("input[type='email']")[0].value.trim();
    let password = document.querySelectorAll("input[type='password']")[0].value.trim();

    if (email === "" || password === "") {
        alert("Please fill in all login fields.");
    } else {
        alert("Login Successful!");
    }
});

// Register Button
document.getElementById("register-btn").addEventListener("click", function () {
    let name = document.querySelectorAll("input[type='text']")[0].value.trim();
    let email = document.querySelectorAll("input[type='email']")[1].value.trim();
    let password = document.querySelectorAll("input[type='password']")[1].value.trim();
    let human = document.getElementById("human").checked;

    if (name === "" || email === "" || password === "") {
        alert("Please fill in all registration fields.");
        return;
    }

    if (!human) {
        alert("Please confirm that you are human.");
        return;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        return;
    }

    alert("Account Created Successfully!");
});