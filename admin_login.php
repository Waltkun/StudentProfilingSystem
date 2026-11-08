<?php
include 'connect2.php';
session_start();

if (isset($_SESSION['admin_id'])) {
    if ($_SESSION['admin_role'] === 'teacher' || $_SESSION['admin_role'] === 'staff') {
        header("Location: teacher_home.php");
    } else {
        header("Location: admin_home.php");
    }
    exit();
}

$msg = "";
// Default to admin tab
$activeTab = $_POST['login_type'] ?? 'admin'; 

if (isset($_POST['LoginAdmin'])) {
    $email    = trim($_POST['log_email']);
    $password = $_POST['log_password'];
    $expected = $_POST['login_type'];

    $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $isTeacher = in_array($row['role'], ['teacher', 'staff'], true);
        $isAdmin   = in_array($row['role'], ['super_admin', 'admin', 'registrar'], true);

        if ($expected === 'teacher' && !$isTeacher) {
            $msg = "Error: This account is not a Teacher/Staff account.";
        } elseif ($expected === 'admin' && !$isAdmin) {
            $msg = "Error: This account is not an Admin account.";
        } else {
            $_SESSION['admin_id']    = $row['id'];
            $_SESSION['admin_name']  = $row['full_name'];
            $_SESSION['admin_email'] = $row['email'];
            $_SESSION['admin_role']  = $row['role'];

            if ($isTeacher) {
                header("Location: teacher_home.php");
            } else {
                header("Location: admin_home.php");
            }
            exit();
        }
    } else {
        $msg = "Invalid login credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
    <style>
        .login-wrapper {
            width: 450px;
            max-width: 95%;
            margin: 80px auto;
            background: var(--panel);
            padding: 40px 30px;
            border-radius: 12px;
            border: 1px solid rgba(212, 175, 55, 0.5);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75), 0 0 45px rgba(37, 99, 235, 0.25);
            overflow: hidden; /* Prevents the P3 tabs from spilling out */
        }
        
        /* P3 ANIMATED TABS */
        .role-tabs {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            border-bottom: 1px solid #26263a;
            padding-bottom: 20px;
        }
        
        .role-btn {
            background: none;
            border: none;
            color: var(--muted);
            font-weight: 700;
            font-size: 1.1rem;
            font-style: italic;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            transform: skewX(-15deg);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
        }

        /* Hover Effect */
        .role-btn:hover {
            color: #fff;
            transform: skewX(-15deg) translateY(-3px) scale(1.05);
        }

        /* Active Tab Effect */
        .role-btn.active {
            color: var(--gold);
            font-size: 1.4rem;
            transform: skewX(-15deg) translateX(5px) scale(1.1);
            text-shadow: 3px 3px 0px rgba(0, 0, 0, 0.8);
            z-index: 10;
        }

        /* Dim the inactive tab */
        .role-tabs:has(.role-btn.active) .role-btn:not(.active) {
            opacity: 0.4;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <h1 class="form-title" style="text-align: center; margin-bottom: 20px;">Staff Portal</h1>

        <!-- Role Selector Tabs -->
        <div class="role-tabs">
            <button type="button" class="role-btn <?= $activeTab === 'admin' ? 'active' : '' ?>" onclick="switchRole('admin', event)">Admin</button>
            <button type="button" class="role-btn <?= $activeTab === 'teacher' ? 'active' : '' ?>" onclick="switchRole('teacher', event)">Teacher / Staff</button>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="message error" style="margin: 0 0 15px 0;"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="login_type" id="login-type" value="<?= $activeTab ?>">

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="log_email" id="log-email" placeholder="Email" required>
                <label for="log-email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="log_password" id="log-password" placeholder="Password" required>
                <label for="log-password">Password</label>
                <button type="button" class="toggle-pw" data-target="log-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign In" name="LoginAdmin" style="margin-top: 10px;">

            <div style="text-align: center; margin-top: 20px;">
                <a href="index2.php" style="color: var(--muted); font-size: 0.9rem; text-decoration: none;">Back to Student Portal</a>
            </div>
        </form>
    </div>

    <script src="script2.js"></script>
    <script>
        function switchRole(role, event) {
            // 1. Update hidden input
            document.getElementById('login-type').value = role;
            
            // 2. Remove active class from both buttons
            document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
            
            // 3. Add active class to the clicked button
            event.target.classList.add('active');
            
            // 4. Optional: Clear error messages when switching roles
            const msgBox = document.querySelector('.message.error');
            if (msgBox) msgBox.style.display = 'none';
        }
    </script>
</body>
</html>