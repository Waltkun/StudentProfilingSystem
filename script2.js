// PASSWORD SHOW/HIDE LOGIC (Safe for all pages)
document.querySelectorAll('.toggle-pw').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// SLIDER & ERROR LOGIC (Only runs if the elements exist on the page)
const wrapper = document.getElementById('wrapper');
if (wrapper) {
    const signInForm = document.getElementById('signIn');
    const signUpForm = document.getElementById('signUp');
    const loginFormElement = document.getElementById('loginForm');
    const registerFormElement = document.getElementById('registerForm');
    const loginErrorBox = document.getElementById('loginError');
    const registerErrorBox = document.getElementById('registerError');

    // Only attach slider events if the buttons exist (Student page)
    const btnSignUp = document.getElementById('signUpButton');
    const btnSignIn = document.getElementById('signInButton');

    if (btnSignUp && btnSignIn) {
        function showRegister(isRegister) {
            wrapper.classList.toggle('active', isRegister);
            signUpForm.inert = !isRegister;
            signInForm.inert = isRegister;
            
            if(loginErrorBox) loginErrorBox.style.display = 'none';
            if(registerErrorBox) registerErrorBox.style.display = 'none';
        }

        btnSignUp.addEventListener('click', () => showRegister(true));
        btnSignIn.addEventListener('click', () => showRegister(false));

        // Handle URL error parameters for student page
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const type = urlParams.get('type');

        if (urlParams.get('registered') === '1') {
            wrapper.classList.add('no-anim');
            showRegister(true);
            wrapper.offsetHeight; 
            wrapper.classList.remove('no-anim');
            setTimeout(() => showRegister(false), 900);
            history.replaceState(null, '', window.location.pathname);
        }

        if (error && type) {
            if (type === 'register') {
                showRegister(true);
                registerErrorBox.style.display = 'block';
                if (error === 'match') registerErrorBox.innerText = "Passwords do not match.";
                if (error === 'exists') registerErrorBox.innerText = "Email address already exists.";
                if (error === 'db') registerErrorBox.innerText = "Database error. Please try again.";
                if (error === 'complex') registerErrorBox.innerText = "Password must be 8+ chars, with 1 letter, 1 number, & 1 symbol.";
                registerFormElement.classList.add('shake');
                setTimeout(() => registerFormElement.classList.remove('shake'), 500);
            } else if (error === 'invalid') {
                showRegister(false);
                loginErrorBox.style.display = 'block';
                loginErrorBox.innerText = "Incorrect Email or Password.";
                loginFormElement.classList.add('shake');
                setTimeout(() => loginFormElement.classList.remove('shake'), 500);
            }
            setTimeout(() => {
                history.replaceState(null, '', window.location.pathname);
            }, 1000);

            if (error === 'sid')        registerErrorBox.innerText = "Student ID must be numbers only and max 9 digits.";
            if (error === 'sid_exists') registerErrorBox.innerText = "That Student ID is already registered.";
        }
    }
}

// Admin login page errors (works only when the elements exist)
const adminErrBox = document.getElementById('adminError');
if (adminErrBox) {
    const p = new URLSearchParams(window.location.search);
    const e = p.get('error');
    if (e === 'invalid') {
        adminErrBox.style.display = 'block';
        adminErrBox.innerText = "Invalid Admin Credentials.";
    } else if (e === 'role') {
        adminErrBox.style.display = 'block';
        adminErrBox.innerText = "Your account role does not have access to that portal.";
    }
    if (e) history.replaceState(null, '', window.location.pathname);
}