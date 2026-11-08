<?php
include 'connect2.php';

// This will add the missing profile_pic column to the users table
$col = 'profile_pic';
$check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");

if ($check && $check->num_rows > 0) {
    echo "<h3>The '$col' column already exists! You don't need to run this again.</h3>";
} else {
    if ($conn->query("ALTER TABLE users ADD COLUMN $col VARCHAR(255) NULL")) {
        echo "<h3>Success! The '$col' column was added to the database. Go back and try uploading your picture again.</h3>";
    } else {
        echo "Error adding column: " . $conn->error;
    }
}
?>