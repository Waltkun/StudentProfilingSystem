<?php
session_start();
if (isset($_SESSION['email'])) {
    header("Location: homepage2.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div class="wrapper" id="wrapper">

    <!-- LOG IN -->
    <div class="form-box login" id="signIn">
        <h1 class="form-title">Student Log In</h1>
        
        <div id="loginError" class="message error" style="display: none;"></div>

        <form method="post" action="register.php" id="loginForm">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="log-email" placeholder="Email" required>
                <label for="log-email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="log-password" placeholder="Password" required>
                <label for="log-password">Password</label>
                <button type="button" class="toggle-pw" data-target="log-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign In" name="SignIn">
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="admin_login.php" style="color: var(--muted); font-size: 0.9rem;">Login as Administrator</a>
        </div>
    </div>

    <!-- REGISTER -->
    <div class="form-box register" id="signUp" inert>
        <h1 class="form-title">Student Register</h1>
        
        <div id="registerError" class="message error" style="display: none;"></div>
        
        <form method="post" action="register.php" id="registerForm">
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="student_id" id="reg-student-id" placeholder="Student ID (Max 9 digits)" maxlength="9" required
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <label for="reg-student-id">Student ID</label>
            </div>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="First Name" required>
                <label for="fName">First Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" placeholder="Last Name" required>
                <label for="lName">Last Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="reg-email" placeholder="Email" required>
                <label for="reg-email">Email</label>
            </div>

            <div class="input-group" style="display: flex; gap: 20px; margin-top: 10px;">
                <div style="flex: 1; position: relative;">
                    <i class="fas fa-venus-mars"></i>
                    <select name="gender" id="reg-gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div style="flex: 1; position: relative;">
                    <i class="fas fa-birthday-cake"></i>
                    <input type="text" name="birthday" id="reg-birthday" placeholder="Birthday (mm/dd/yy)" maxlength="10" required>
                </div>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="reg-password" placeholder="Password" required minlength="6">
                <label for="reg-password">Password</label>
                <button type="button" class="toggle-pw" data-target="reg-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" id="reg-confirm" placeholder="Confirm Password" required minlength="6">
                <label for="reg-confirm">Confirm Password</label>
                <button type="button" class="toggle-pw" data-target="reg-confirm">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign Up" name="SignUp">
        </form>
    </div> <!-- 🚨 THIS WAS MISSING! It closes the register form box -->

    <!-- OVERLAY -->
    <div class="overlay">
        <div class="overlay-panel for-login">
            <h2>New here?</h2>
            <p>Create an account and get started.</p>
            <button type="button" class="ghost" id="signUpButton">Sign Up</button>
        </div>
        <div class="overlay-panel for-register">
            <h2>Welcome back!</h2>
            <p>Already have an account? Log in instead.</p>
            <button type="button" class="ghost" id="signInButton">Sign In</button>
        </div>
    </div>

</div> <!-- 🚨 THIS WAS MISSING! It closes the whole wrapper -->

<script src="script2.js"></script>

<script>
    document.getElementById('reg-birthday').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
        if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5);
        e.target.value = value;
    });
</script>

</body>
</html>