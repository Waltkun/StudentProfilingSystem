<?php
include 'connect2.php';
session_start();

// SIGN UP
if (isset($_POST['SignUp'])) {
    $firstName = trim($_POST['fName']);
    $lastName  = trim($_POST['lName']);
    $email     = trim($_POST['email']);
    $gender    = $_POST['gender'];
    $birthday  = $_POST['birthday'];
    $student_id = trim($_POST['student_id']);   // NEW
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if ($password !== $confirm) {
        header("Location: index2.php?error=match&type=register");
        exit();
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        header("Location: index2.php?error=complex&type=register");
        exit();
    }

    // NEW: validate student ID
    if (!ctype_digit($student_id) || strlen($student_id) > 9 || $student_id === '') {
        header("Location: index2.php?error=sid&type=register");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: index2.php?error=exists&type=register");
        exit();
    }

    // NEW: also check student_id uniqueness
    $chk = $conn->prepare("SELECT user_id FROM student_profiles WHERE student_id = ?");
    $chk->bind_param("s", $student_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        header("Location: index2.php?error=sid_exists&type=register");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, gender, birthday, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $gender, $birthday, $hashed_password);

    if ($stmt->execute()) {
        $uid = $conn->insert_id;
        // NEW: create profile with student_id
        $p = $conn->prepare("INSERT INTO student_profiles (user_id, student_id) VALUES (?, ?)");
        $p->bind_param("is", $uid, $student_id);
        $p->execute();

        header("Location: index2.php?registered=1");
        exit();
    } else {
        header("Location: index2.php?error=db&type=register");
        exit();
    }
}

// SIGN IN (unchanged)
if (isset($_POST['SignIn'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, email, firstName, lastName, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['lastName'] = $row['lastName'];
        header("Location: homepage2.php?welcome=1");
        exit();
    } else {
        header("Location: index2.php?error=invalid");
        exit();
    }
}
?>