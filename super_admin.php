<?php
include 'connect2.php';

$name = 'System Admin';
$email = 'admin@school.edu';
$password = 'Admin@123'; // You will use this to log in
$hash = password_hash($password, PASSWORD_DEFAULT);

// Delete old one if it exists to start fresh
$conn->query("DELETE FROM admins WHERE email='$email'");

$stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role) VALUES (?, ?, ?, 'super_admin')");
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    echo "<h3>Super Admin Created!</h3>";
    echo "<b>Email:</b> $email<br>";
    echo "<b>Password:</b> $password<br><br>";
    echo "<a href='admin_login.php'>Go to Staff Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>