<?php
include 'connect2.php';

// 1. Create Super Admin
$admin_email = 'admin@school.edu';
$admin_pass = 'Admin@123';
$admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);

$conn->query("DELETE FROM admins WHERE email='$admin_email'");
$stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role) VALUES ('System Admin', ?, ?, 'super_admin')");
$stmt->bind_param("ss", $admin_email, $admin_hash);
$stmt->execute();

// 2. Create Teacher
$teacher_email = 'teacher@school.edu';
$teacher_pass = 'Teacher@123';
$teacher_hash = password_hash($teacher_pass, PASSWORD_DEFAULT);

$conn->query("DELETE FROM admins WHERE email='$teacher_email'");
$stmt2 = $conn->prepare("INSERT INTO admins (full_name, email, password, role) VALUES ('Teacher Account', ?, ?, 'teacher')");
$stmt2->bind_param("ss", $teacher_email, $teacher_hash);
$stmt2->execute();

echo "<h3>Accounts Successfully Created!</h3>";
echo "<b>Super Admin:</b> admin@school.edu / Admin@123<br>";
echo "<b>Teacher:</b> teacher@school.edu / Teacher@123<br><br>";
echo "<a href='admin_login.php'>Go to Staff Login</a>";
?>