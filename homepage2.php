<?php
session_start();
include("connect2.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index2.php");
    exit();
}

$uid = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE Id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$firstName   = $user['firstName'];
$lastName    = $user['lastName'];
$gender      = $user['gender'] ?? '';
$birthday    = $user['birthday'] ?? '';
$profile_pic = $user['profile_pic'] ?? '';

$stmt2 = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt2->bind_param("i", $uid);
$stmt2->execute();
$profile = $stmt2->get_result()->fetch_assoc() ?: [];

$s_id           = $profile['student_id'] ?? '';
$middle         = $profile['middle_name'] ?? '';
$phone          = $profile['phone_no'] ?? '';
$program        = $profile['program'] ?? '';
$year           = $profile['year_level'] ?? '';
$address        = $profile['address'] ?? '';
$nationality    = $profile['nationality'] ?? '';
$religion       = $profile['religion'] ?? '';
$marital_status = $profile['marital_status'] ?? '';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_s_id = trim($_POST['student_id']);
    $fname    = trim($_POST['first_name']);
    $lname    = trim($_POST['last_name']);
    $gndr     = $_POST['gender'];
    $bday     = $_POST['birthday'];
    $mid      = trim($_POST['middle_name']);
    $ph       = trim($_POST['phone_no']);
    $prog     = trim($_POST['program']);
    $yr       = trim($_POST['year_level']);
    $addr     = trim($_POST['address']);
    $nat      = trim($_POST['nationality']);
    $rel      = trim($_POST['religion']);
    $mar      = trim($_POST['marital_status']);

    if (!empty($new_s_id) && (!ctype_digit($new_s_id) || strlen($new_s_id) > 9)) {
        $error = "Student ID must be numbers only and maximum 9 digits.";
    } elseif (!empty($ph) && (!ctype_digit($ph) || strlen($ph) > 11)) {
        $error = "Phone number must be numbers only and maximum 11 digits.";
    } else {
        if (!empty($_FILES['profile_pic']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Profile picture must be JPG, PNG, GIF, or WEBP.";
            } elseif ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) {
                $error = "Profile picture must be under 2 MB.";
            } else {
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                $filename = 'uploads/profile_' . $uid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filename)) {
                    if (!empty($profile_pic) && file_exists($profile_pic)) @unlink($profile_pic);
                    $profile_pic = $filename;
                }
            }
        }

        if (empty($error)) {
            $u = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, gender = ?, birthday = ?, profile_pic = ? WHERE Id = ?");
            $u->bind_param("sssssi", $fname, $lname, $gndr, $bday, $profile_pic, $uid);
            $u->execute();
            $_SESSION['firstName'] = $fname;
            $_SESSION['lastName']  = $lname;

            $conn->query("INSERT IGNORE INTO student_profiles (user_id) VALUES ($uid)");
            
            $p = $conn->prepare("UPDATE student_profiles SET student_id = ?, middle_name = ?, phone_no = ?, program = ?, year_level = ?, address = ?, nationality = ?, religion = ?, marital_status = ? WHERE user_id = ?");
            $p->bind_param("sssssssssi", $new_s_id, $mid, $ph, $prog, $yr, $addr, $nat, $rel, $mar, $uid);
            $p->execute();

            header("Location: homepage2.php?saved=1");
            exit();
        }
    }
}

$justSaved    = isset($_GET['saved']);
$initialPanel = $justSaved ? 'edit' : 'profile';
$fullName = htmlspecialchars($firstName . ' ' . $lastName);
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        /* New layout styles specifically for the profile panel */
        .new-profile-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            text-align: center;
        }
        .new-avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 4px solid var(--gold);
            background: radial-gradient(circle at 30% 30%, #1e2d6e, #0b0b10);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: var(--gold);
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .new-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .new-welcome {
            font-size: 1.2rem;
            color: var(--gold);
            margin-bottom: 30px;
            font-weight: 600;
        }
        .new-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 20px;
            width: 100%;
            max-width: 700px;
            text-align: left;
            margin-bottom: 40px;
        }
    </style>
</head>
<body class="home-page">

    <div class="bg-shapes">
        <span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span>
    </div>

    <div class="profile-card" style="max-width: 95%; margin: 20px auto;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">Goldenstate College<small>Student Portal</small></div>
            </div>
        </header>

        <div class="dashboard-layout">
            <nav class="persona-nav">
                <button type="button" class="nav-item" data-panel="profile">Student Profile</button>
                <button type="button" class="nav-item" data-panel="info">Student Info</button>
                <button type="button" class="nav-item" data-panel="edit">Edit Profile</button>
                <a href="logout2.php" class="nav-item nav-logout">Logout</a>
            </nav>

            <div class="dashboard-content">
                <?php if ($justSaved): ?>
                    <div class="message success">Profile saved successfully.</div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- NEW LAYOUT FOR PROFILE PANEL -->
                <section class="panel" id="panel-profile">
                    <div class="new-profile-layout">
                        <div class="new-avatar">
                            <?php if (!empty($profile_pic) && file_exists($profile_pic)): ?>
                                <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile">
                            <?php else: ?>
                                <?= $initials ?>
                            <?php endif; ?>
                        </div>
                        
                        <p class="new-welcome">Hello, Welcome <?= $fullName ?> :3</p>
                        
                        <div class="new-info-grid">
                            <div class="view-field"><label>Student ID</label><div class="v"><?= htmlspecialchars($s_id ?: '—') ?></div></div>
                            <div class="view-field"><label>Full Name</label><div class="v"><?= $fullName ?></div></div>
                            <div class="view-field"><label>Course / Program</label><div class="v"><?= htmlspecialchars($program ?: '—') ?></div></div>
                            <div class="view-field"><label>Year Level</label><div class="v"><?= htmlspecialchars($year ?: '—') ?></div></div>
                        </div>

                        <div class="view-field text-center">
                            <label>QR ID</label>
                            <div class="qr-box"><div id="qrcode"></div></div>
                        </div>
                    </div>
                </section>

                <section class="panel" id="panel-info">
                    <div class="view-grid">
                        <div class="view-field"><label>Address</label><div class="v"><?= htmlspecialchars($address ?: '—') ?></div></div>
                        <div class="view-field"><label>Birthday</label><div class="v"><?= htmlspecialchars($birthday ?: '—') ?></div></div>
                        <div class="view-field"><label>Phone Number</label><div class="v"><?= htmlspecialchars($phone ?: '—') ?></div></div>
                        <div class="view-field"><label>Gender</label><div class="v"><?= htmlspecialchars($gender ?: '—') ?></div></div>
                        <div class="view-field"><label>Nationality</label><div class="v"><?= htmlspecialchars($nationality ?: '—') ?></div></div>
                        <div class="view-field"><label>Religion</label><div class="v"><?= htmlspecialchars($religion ?: '—') ?></div></div>
                        <div class="view-field"><label>Marital Status</label><div class="v"><?= htmlspecialchars($marital_status ?: '—') ?></div></div>
                    </div>
                </section>

                <section class="panel" id="panel-edit">
                    <form method="post" action="homepage2.php" enctype="multipart/form-data">
                        <div class="grid">
                            <div class="field" style="grid-column: span 2;">
                                <label>Student ID (Max 9 digits)</label>
                                <input type="text" name="student_id" value="<?= htmlspecialchars($s_id) ?>"
                                    placeholder="e.g. 202400123" maxlength="9"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="field">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                            </div>
                            <div class="field">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                            </div>
                            <div class="field">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" value="<?= htmlspecialchars($middle) ?>" placeholder="Optional">
                            </div>
                            <div class="field">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="" disabled <?= empty($gender) ? 'selected' : '' ?>>Select Gender</option>
                                    <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Birthday (mm/dd/yy)</label>
                                <input type="text" name="birthday" value="<?= htmlspecialchars($birthday) ?>"
                                    placeholder="mm/dd/yy" maxlength="10" required
                                    oninput="let v=this.value.replace(/\D/g,''); if(v.length>2)v=v.slice(0,2)+'/'+v.slice(2); if(v.length>5)v=v.slice(0,5)+'/'+v.slice(5); this.value=v;">
                            </div>
                            <div class="field">
                                <label>Phone Number (Max 11 digits)</label>
                                <input type="tel" name="phone_no" value="<?= htmlspecialchars($phone) ?>"
                                    placeholder="e.g. 09171234567" maxlength="11"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="field">
                                <label>Course / Program</label>
                                <input type="text" name="program" value="<?= htmlspecialchars($program) ?>" placeholder="e.g. BSIT">
                            </div>
                            <div class="field">
                                <label>Year Level</label>
                                <select name="year_level">
                                    <option value="">Select year</option>
                                    <?php foreach (["1st Year", "2nd Year", "3rd Year", "4th Year"] as $y): ?>
                                        <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="field">
                                <label>Nationality</label>
                                <input type="text" name="nationality" value="<?= htmlspecialchars($nationality) ?>" placeholder="e.g. Filipino">
                            </div>
                            <div class="field">
                                <label>Religion</label>
                                <input type="text" name="religion" value="<?= htmlspecialchars($religion) ?>" placeholder="e.g. Catholic">
                            </div>
                            <div class="field">
                                <label>Marital Status</label>
                                <select name="marital_status">
                                    <option value="">Select Status</option>
                                    <?php foreach (["Single", "Married", "Widowed", "Separated", "Divorced"] as $status): ?>
                                        <option value="<?= $status ?>" <?= $marital_status === $status ? 'selected' : '' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field" style="grid-column: span 2;">
                                <label>Address</label>
                                <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" placeholder="House No., Street, City">
                            </div>
                            <div class="field" style="grid-column: span 2;">
                                <label>Profile Picture (max 2 MB)</label>
                                <input type="file" name="profile_pic" accept="image/*">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="save-btn">Save Profile</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <script src="script2.js"></script>
    <script>
        const panels = document.querySelectorAll('.panel');
        const navItems = document.querySelectorAll('.nav-item[data-panel]');
        function showPanel(name) {
            panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + name));
            navItems.forEach(b => b.classList.toggle('active', b.dataset.panel === name));
        }
        navItems.forEach(btn => btn.addEventListener('click', () => showPanel(btn.dataset.panel)));
        showPanel('<?= $initialPanel ?>');

        const qrTarget = document.getElementById('qrcode');
        if (qrTarget) {
            new QRCode(qrTarget, {
                text: <?= json_encode($s_id ?: ('user-' . $uid)) ?>,
                width: 120, height: 120,
                colorDark: "#05050a",
                colorLight: "#f5eecb"
            });
        }

        const navItemsForPush = document.querySelectorAll('.persona-nav .nav-item');
        navItemsForPush.forEach((item, index) => {
            item.addEventListener('mouseenter', () => {
                for(let i = 0; i < index; i++) {
                    navItemsForPush[i].style.transform = 'skewX(-15deg) translateY(-15px)';
                }
                for(let i = index + 1; i < navItemsForPush.length; i++) {
                    navItemsForPush[i].style.transform = 'skewX(-15deg) translateY(15px)';
                }
                item.style.transform = 'skewX(-15deg) translateX(15px) scale(1.05)';
            });
            item.addEventListener('mouseleave', () => {
                navItemsForPush.forEach(nav => { nav.style.transform = ''; });
            });
        });
    </script>
</body>
</html>