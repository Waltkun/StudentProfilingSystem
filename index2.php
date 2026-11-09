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
    <style>
        /* Specific styles for the new date dropdowns */
        .date-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            position: relative;
        }
        .date-group .input-group {
            flex: 1;
            margin: 0;
        }
        /* Target the select boxes specifically */
        .date-group select {
            width: 100%;
            background-color: transparent;
            color: var(--text);
            border: none;
            border-bottom: 1px solid #4a4a60;
            padding-left: 1.5rem;
            padding-bottom: 5px;
            font-size: 15px;
            font-family: inherit;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23d4af37' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 5px center;
            background-size: 1em;
        }
        .date-group select:focus {
            outline: none;
            border-bottom: 2px solid var(--gold);
        }
        .date-group select option {
            background-color: var(--panel);
            color: var(--text);
            padding: 10px;
        }
        /* Fix icon positioning */
        .date-group .input-group > i {
            top: 45%;
        }

        /* 🚨 PERSONA 3 TEXT STYLES 🚨 */
        .p3-text {
            font-family: "Poppins", sans-serif;
            font-weight: 700;
            font-style: italic;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--gold);
            transform: skewX(-15deg);
            text-shadow: 3px 3px 0px rgba(0, 0, 0, 0.8);
            display: inline-block;
        }

        .p3-form-title {
            font-size: 2rem; /* Larger for main titles */
            text-align: center;
            margin-bottom: 25px;
            padding: 0;
            padding-top: 10px;
        }

        .p3-overlay-title {
            font-size: 2.2rem; /* Larger for the side panels */
            color: var(--gold); /* Keeps the gold color from the original overlay */
            margin-bottom: 15px;
            line-height: 1.1;
        }
    </style>
</head>
<body>

<div class="wrapper" id="wrapper">

    <!-- LOG IN -->
    <div class="form-box login" id="signIn">
        <!-- APPLIED P3 STYLE HERE -->
        <h1 class="form-title p3-text p3-form-title">Student Log In</h1>
        
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
        <!-- APPLIED P3 STYLE HERE -->
        <h1 class="form-title p3-text p3-form-title">Student Register</h1>
        
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

            <!-- GENDER -->
            <div class="input-group" style="margin-top: 10px;">
                <i class="fas fa-venus-mars"></i>
                <select name="gender" id="reg-gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- NEW BIRTHDAY DROPDOWNS -->
            <div class="date-group">
                <!-- Month -->
                <div class="input-group">
                    <i class="fas fa-calendar-alt"></i>
                    <select id="b_month" required>
                        <option value="" disabled selected>Month</option>
                        <option value="01">Jan</option>
                        <option value="02">Feb</option>
                        <option value="03">Mar</option>
                        <option value="04">Apr</option>
                        <option value="05">May</option>
                        <option value="06">Jun</option>
                        <option value="07">Jul</option>
                        <option value="08">Aug</option>
                        <option value="09">Sep</option>
                        <option value="10">Oct</option>
                        <option value="11">Nov</option>
                        <option value="12">Dec</option>
                    </select>
                </div>

                <!-- Day -->
                <div class="input-group">
                    <i class="fas fa-calendar-day"></i>
                    <select id="b_day" required>
                        <option value="" disabled selected>Day</option>
                        <!-- Populated by JS -->
                    </select>
                </div>

                <!-- Year (Editable) -->
                <div class="input-group">
                    <i class="fas fa-calendar-week"></i>
                    <input type="text" id="b_year" class="year-input" placeholder="Year" maxlength="4" required 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>
            <!-- HIDDEN INPUT TO SEND TO BACKEND -->
            <input type="hidden" name="birthday" id="reg-birthday">

            <div class="input-group" style="margin-top: 15px;">
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

            <input type="submit" class="button" value="Sign Up" name="SignUp" onclick="combineBirthday()">
        </form>
    </div> 

    <!-- OVERLAY -->
    <div class="overlay">
        <div class="overlay-panel for-login">
            <!-- APPLIED P3 STYLE HERE -->
            <h2 class="p3-text p3-overlay-title">New here?</h2>
            <p>Create an account and get started.</p>
            <button type="button" class="ghost" id="signUpButton">Sign Up</button>
        </div>
        <div class="overlay-panel for-register">
            <!-- APPLIED P3 STYLE HERE -->
            <h2 class="p3-text p3-overlay-title">Welcome back!</h2>
            <p>Already have an account? Log in instead.</p>
            <button type="button" class="ghost" id="signInButton">Sign In</button>
        </div>
    </div>

</div> 

<script src="script2.js"></script>

<script>
    // --- LOGIC FOR BIRTHDAY DROPDOWNS ---

    const monthSelect = document.getElementById('b_month');
    const daySelect = document.getElementById('b_day');
    const yearInput = document.getElementById('b_year');

    // Function to populate days based on selected month/year
    function populateDays() {
        const month = parseInt(monthSelect.value);
        const year = parseInt(yearInput.value) || 2024; // Default to leap year for Feb calc if year is empty

        // Clear existing days (except the placeholder)
        daySelect.innerHTML = '<option value="" disabled selected>Day</option>';

        if (!month) return; // If no month selected, stop here

        // Calculate days in month
        const daysInMonth = new Date(year, month, 0).getDate();

        for (let i = 1; i <= daysInMonth; i++) {
            const dayVal = i < 10 ? '0' + i : i;
            const opt = document.createElement('option');
            opt.value = dayVal;
            opt.textContent = i;
            daySelect.appendChild(opt);
        }
    }

    // Listen for changes in Month or Year to update Day
    monthSelect.addEventListener('change', populateDays);
    yearInput.addEventListener('input', populateDays);

    // Combine values into YYYY-MM-DD before submitting
    function combineBirthday() {
        const m = monthSelect.value;
        const d = daySelect.value;
        const y = yearInput.value;

        // Validate if fields are empty
        if (m && d && y) {
            // Format to match what the database expects: 2006-01-30
            const formattedDate = `${y}-${m}-${d}`;
            document.getElementById('reg-birthday').value = formattedDate;
        }
    }
</script>

</body>
</html>
