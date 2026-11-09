<?php
include 'connect2.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$is_super = ($_SESSION['admin_role'] === 'super_admin');
$msg = "";

// --- ADD STUDENT ---
if (isset($_POST['addStudent'])) {
    $s_id = trim($_POST['student_id']);
    $fname = trim($_POST['firstName']);
    $lname = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $ph = trim($_POST['phone_no']);
    $prog = trim($_POST['program']);
    $yr = trim($_POST['year_level']);
    
    if (!ctype_digit($s_id) || strlen($s_id) > 9) {
        $msg = "Error: Student ID must be numbers only and maximum 9 digits.";
    } elseif (!empty($ph) && (!ctype_digit($ph) || strlen($ph) > 11)) {
        $msg = "Error: Phone number must be numbers only and maximum 11 digits.";
    } else {
        $default_pass = password_hash('Student@123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fname, $lname, $email, $default_pass);
        
        if($stmt->execute()){
            $new_user_id = $conn->insert_id;
            $profile_stmt = $conn->prepare("INSERT INTO student_profiles (user_id, student_id, phone_no, program, year_level) VALUES (?, ?, ?, ?, ?)");
            $profile_stmt->bind_param("issss", $new_user_id, $s_id, $ph, $prog, $yr);
            $profile_stmt->execute();
            $msg = "Student added successfully! Default password: Student@123";
        } else {
            $msg = "Error adding student. Email might already exist.";
        }
    }
}

// --- UPDATE STUDENT (EXPANDED) ---
if (isset($_POST['updateStudent'])) {
    $uid = (int)$_POST['edit_id'];
    $fname = trim($_POST['edit_firstName']);
    $lname = trim($_POST['edit_lastName']);
    $email = trim($_POST['edit_email']);
    $gndr = $_POST['edit_gender'];
    $bday = trim($_POST['edit_birthday']);
    $mid = trim($_POST['edit_middle_name']);
    $ph = trim($_POST['edit_phone_no']);
    $prog = trim($_POST['edit_program']);
    $yr = trim($_POST['edit_year_level']);
    $addr = trim($_POST['edit_address']);
    $nat = trim($_POST['edit_nationality']);
    $rel = trim($_POST['edit_religion']);
    $mar = $_POST['edit_marital_status'];
    $s_id = trim($_POST['edit_student_id']);
    
    $u = $conn->prepare("UPDATE users SET firstName=?, lastName=?, email=?, gender=?, birthday=? WHERE Id=?");
    $u->bind_param("sssssi", $fname, $lname, $email, $gndr, $bday, $uid);
    $u->execute();
    
    $p = $conn->prepare("UPDATE student_profiles SET student_id=?, middle_name=?, phone_no=?, program=?, year_level=?, address=?, nationality=?, religion=?, marital_status=? WHERE user_id=?");
    $p->bind_param("sssssssssi", $s_id, $mid, $ph, $prog, $yr, $addr, $nat, $rel, $mar, $uid);
    $p->execute();
    
    $msg = "Student updated successfully!";
}

// --- DELETE STUDENT ---
if (isset($_POST['deleteStudent'])) {
    $uid = (int)$_POST['user_id'];
    $conn->query("DELETE FROM users WHERE Id = $uid");
    $msg = "Student deleted.";
}

// --- MANAGE STAFF ---
if ($is_super && isset($_POST['addStaff'])) {
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);
    $staff_role = $_POST['staff_role'];
    $staff_pass = password_hash($_POST['staff_pass'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check->bind_param("s", $staff_email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $msg = "Error: Admin email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $staff_name, $staff_email, $staff_pass, $staff_role);
        if ($stmt->execute()) {
            $msg = "New staff account created successfully.";
        }
    }
}

if ($is_super && isset($_POST['deleteStaff'])) {
    $staff_id = (int)$_POST['staff_id'];
    if ($staff_id !== $_SESSION['admin_id']) {
        $conn->query("DELETE FROM admins WHERE id = $staff_id");
        $msg = "Staff account removed.";
    } else {
        $msg = "Error: You cannot delete your own account.";
    }
}

// Fetch Data
$students = $conn->query("SELECT u.Id, u.firstName, u.lastName, u.email, u.gender, u.birthday, u.profile_pic, 
                                sp.student_id, sp.middle_name, sp.phone_no, sp.program, sp.year_level, sp.address, sp.nationality, sp.religion, sp.marital_status 
                        FROM users u LEFT JOIN student_profiles sp ON u.Id = sp.user_id");
$staff_list = $is_super ? $conn->query("SELECT id, full_name, email, role FROM admins ORDER BY role ASC") : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
    <style>
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: var(--panel); width: 100%; max-width: 600px; border-radius: 12px; border: 1px solid var(--gold); box-shadow: 0 20px 50px rgba(0,0,0,0.8); max-height: 90vh; overflow-y: auto; position: relative; animation: panelIn 0.3s ease; }
        .modal-header { padding: 20px; border-bottom: 1px solid #33334a; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { color: var(--gold); margin: 0; font-size: 1.5rem; text-transform: uppercase; }
        .close-btn { background: none; border: none; color: #ff6b6b; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
        .close-btn:hover { transform: scale(1.1); }
        
        .modal-body { padding: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .modal-field { margin-bottom: 5px; }
        .modal-field label { display: block; font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .modal-field .val { background: #0b0b10; padding: 10px; border-radius: 6px; border: 1px solid #33334a; font-size: 14px; color: var(--text); word-wrap: break-word; }
        .modal-field.full { grid-column: span 2; }
        
        .modal-pic { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--gold); margin: 0 auto 15px auto; display: block; }
        .modal-avatar-text { width: 120px; height: 120px; border-radius: 50%; background: #1e2d6e; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: var(--gold); border: 3px solid var(--gold); margin: 0 auto 15px auto; }
        .clickable-name { cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .clickable-name:hover { color: var(--gold); text-decoration: underline; }

        /* EXPANDED EDIT MODAL STYLES */
        .edit-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .edit-modal.active { display: flex; }
        .edit-content { background: var(--panel); width: 100%; max-width: 650px; border-radius: 10px; border: 1px solid var(--gold); padding: 25px; max-height: 95vh; overflow-y: auto; }
        .edit-header { display: flex; justify-content: space-between; border-bottom: 1px solid #33334a; padding-bottom: 15px; margin-bottom: 20px; }
        .edit-header h3 { color: var(--gold); margin: 0; }
    </style>
</head>
<body class="home-page">
    <div class="bg-shapes"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></div>

    <div class="profile-card" style="margin-top: 20px; max-width: 95%;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">
                    <?= htmlspecialchars($_SESSION['admin_role'] === 'super_admin' ? 'Super Admin' : ucfirst($_SESSION['admin_role'])) ?> Dashboard
                    <small>Logged in as: <?= htmlspecialchars($_SESSION['admin_name']) ?></small>
                </div>
            </div>
        </header>

        <?php if(!empty($msg)): ?>
            <div class="message <?= strpos($msg, 'Error') !== false ? 'error' : 'success' ?>" style="margin: 20px 0 0 0;"><?= $msg ?></div>
        <?php endif; ?>

        <div class="dashboard-layout">
            <nav class="persona-nav">
                <button type="button" class="nav-item active" data-panel="add">Add Student</button>
                <button type="button" class="nav-item" data-panel="registered">Registered Students</button>
                <?php if ($is_super && $staff_list): ?>
                    <button type="button" class="nav-item" data-panel="staff">Manage Staff</button>
                <?php endif; ?>
                <a href="logout2.php" class="nav-item nav-logout" style="margin-top: 30px;">Logout</a>
            </nav>

            <div class="dashboard-content">
                <section class="panel active" id="panel-add">
                    <h2 style="color:var(--gold); margin-bottom:20px; font-size: 1.8rem; text-transform:uppercase; letter-spacing:1px;">Add New Student</h2>
                    <form method="post" class="grid" style="grid-template-columns: 1fr 1fr; margin:0; background: #0b0b10; padding: 25px; border-radius: 10px; border: 1px solid #33334a;">
                        <div class="field" style="grid-column: span 2;"><label>Student ID (Max 9 digits)</label><input type="text" name="student_id" placeholder="e.g. 202400123" required maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                        <div class="field"><label>First Name</label><input type="text" name="firstName" placeholder="First Name" required></div>
                        <div class="field"><label>Last Name</label><input type="text" name="lastName" placeholder="Last Name" required></div>
                        <div class="field" style="grid-column: span 2;"><label>Email Address</label><input type="email" name="email" placeholder="Email Address" required></div>
                        <div class="field"><label>Phone (Max 11 digits)</label><input type="tel" name="phone_no" placeholder="Phone (Max 11 digits)" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                        <div class="field"><label>Year Level</label><select name="year_level"><option value="">Select Year Level</option><option value="1st Year">1st Year</option><option value="2nd Year">2nd Year</option><option value="3rd Year">3rd Year</option><option value="4th Year">4th Year</option></select></div>
                        <div class="field" style="grid-column: span 2;"><label>Course / Program</label><input type="text" name="program" placeholder="Course / Program"></div>
                        <div style="grid-column: span 2; text-align: right; margin-top: 15px;"><button type="submit" name="addStudent" class="save-btn">Add Student</button></div>
                    </form>
                </section>

                <section class="panel" id="panel-registered">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="color:var(--gold); font-size: 1.8rem; text-transform:uppercase; letter-spacing:1px; margin:0;">Registered Students</h2>
                        <!-- PRINT ALL BUTTON -->
                        <button type="button" onclick='printAllStudents(<?= htmlspecialchars(json_encode($students->fetch_all(MYSQLI_ASSOC)), ENT_QUOTES, "UTF-8") ?>, "<?= htmlspecialchars($_SESSION['admin_name']) ?>", "<?= htmlspecialchars($_SESSION['admin_role']) ?>")' style="background: var(--gold); color: #000; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                            <i class="fas fa-print"></i> Print All Students
                        </button>
                    </div>
                    
                    <?php $students->data_seek(0); // Reset pointer for the table loop ?>
                    
                    <div style="overflow-x: auto; background: #0b0b10; padding: 20px; border-radius: 10px; border: 1px solid #33334a;">
                        <table style="width:100%; border-collapse: collapse; text-align: left; color: var(--text); white-space: nowrap;">
                            <tr style="border-bottom: 2px solid #33334a;">
                                <th style="padding:12px 10px; color:var(--gold);">Student ID</th>
                                <th style="padding:12px 10px; color:var(--gold);">Name</th>
                                <th style="padding:12px 10px; color:var(--gold);">Email</th>
                                <th style="padding:12px 10px; color:var(--gold);">Course/Year</th>
                                <th style="padding:12px 10px; color:var(--gold);">Action</th>
                            </tr>
                            <?php while($row = $students->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #26263a;">
                                <td style="padding:12px 10px;"><?= htmlspecialchars($row['student_id'] ?? 'N/A') ?></td>
                                <td style="padding:12px 10px;">
                                    <div class="clickable-name" onclick='openStudentModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)'>
                                        <?php if(!empty($row['profile_pic']) && file_exists($row['profile_pic'])): ?>
                                            <img src="<?= htmlspecialchars($row['profile_pic']) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--gold);">
                                        <?php else: ?>
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--gold); border: 1px solid #555;"><?= strtoupper(substr($row['firstName'], 0, 1) . substr($row['lastName'], 0, 1)) ?></div>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?>
                                        <i class="fas fa-eye" style="color: var(--blue); font-size: 0.8rem; margin-left: 5px;"></i>
                                    </div>
                                </td>
                                <td style="padding:12px 10px;"><?= htmlspecialchars($row['email']) ?></td>
                                <td style="padding:12px 10px;"><?= htmlspecialchars($row['program'] ?? 'N/A') ?> - <?= htmlspecialchars($row['year_level'] ?? 'N/A') ?></td>
                                <td style="padding:12px 10px; display: flex; gap: 15px;">
                                    <!-- SINGLE PRINT BUTTON -->
                                    <button type="button" onclick='printSingleStudent(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>, "<?= htmlspecialchars($_SESSION['admin_name']) ?>", "<?= htmlspecialchars($_SESSION['admin_role']) ?>")' style="background:none; border:none; color:var(--gold); cursor:pointer; font-weight:bold;">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <!-- EDIT BUTTON -->
                                    <button type="button" onclick='openEditModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>)' style="background:none; border:none; color:var(--blue); cursor:pointer; font-weight:bold;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <!-- DELETE BUTTON -->
                                    <form method="post" onsubmit="return confirm('Delete this student?');" style="margin:0;">
                                        <input type="hidden" name="user_id" value="<?= $row['Id'] ?>">
                                        <button type="submit" name="deleteStudent" style="color:#ff6b6b; background:none; border:none; cursor:pointer; font-weight:bold;"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </section>

                <?php if ($is_super && $staff_list): ?>
                <section class="panel" id="panel-staff">
                    <!-- Staff Panel Remains Unchanged -->
                    <h2 style="color:var(--gold); margin-bottom:20px; font-size: 1.8rem; text-transform:uppercase; letter-spacing:1px;"><i class="fas fa-user-shield"></i> Manage Staff & Admins</h2>
                    <form method="post" class="grid" style="grid-template-columns: 1fr 1fr 1fr 1fr; margin:0; background: #0b0b10; padding: 25px; border-radius: 10px; border: 1px solid #33334a;">
                        <div class="field"><label>Full Name</label><input type="text" name="staff_name" placeholder="Full Name" required></div>
                        <div class="field"><label>Email</label><input type="email" name="staff_email" placeholder="Email Address" required></div>
                        <div class="field"><label>Role</label><select name="staff_role" required><option value="teacher">Teacher</option><option value="staff">School Staff</option><option value="super_admin">Super Admin</option></select></div>
                        <div class="field"><label>Temporary Password</label><input type="password" name="staff_pass" placeholder="Temporary Password" required minlength="6"></div>
                        <div style="grid-column: span 4; text-align: right; margin-top: 15px;"><button type="submit" name="addStaff" class="save-btn" style="background: #2563eb;">Create Admin Account</button></div>
                    </form>
                    <div style="margin-top: 30px; background: #0b0b10; padding: 20px; border-radius: 10px; border: 1px solid #33334a;">
                        <h3 style="color:var(--text); margin-bottom:15px; font-size: 1.2rem;">Active Admin Accounts</h3>
                        <table style="width:100%; border-collapse: collapse; text-align: left; color: var(--text);">
                            <tr style="border-bottom: 2px solid #33334a;"><th style="padding:10px; color:var(--gold);">Name</th><th style="padding:10px; color:var(--gold);">Email</th><th style="padding:10px; color:var(--gold);">Role</th><th style="padding:10px; color:var(--gold);">Action</th></tr>
                            <?php while($staff = $staff_list->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #26263a;">
                                <td style="padding:10px;"><?= htmlspecialchars($staff['full_name']) ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($staff['email']) ?></td>
                                <td style="padding:10px;"><span style="background: <?= $staff['role'] === 'super_admin' ? 'var(--gold)' : '#333' ?>; color: <?= $staff['role'] === 'super_admin' ? '#000' : '#fff' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;"><?= strtoupper(str_replace('_', ' ', $staff['role'])) ?></span></td>
                                <td style="padding:10px;"><?php if($staff['id'] !== $_SESSION['admin_id']): ?><form method="post" onsubmit="return confirm('Remove this admin account?');" style="margin:0;"><input type="hidden" name="staff_id" value="<?= $staff['id'] ?>"><button type="submit" name="deleteStaff" style="color:#ff6b6b; background:none; border:none; cursor:pointer;"><i class="fas fa-trash"></i> Remove</button></form><?php else: ?><span style="color:var(--muted); font-size:0.85rem;">(You)</span><?php endif; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal-overlay" id="studentModal">
        <div class="modal-content">
            <div class="modal-header"><h3>Student Information</h3><button class="close-btn" onclick="closeModal('studentModal')"><i class="fas fa-times"></i></button></div>
            <div class="modal-body">
                <div class="full" style="text-align: center;"><img id="modalPic" class="modal-pic" src="" style="display: none;"><div id="modalAvatarText" class="modal-avatar-text"></div></div>
                <div class="modal-field full"><label>Full Name</label><div class="val" id="mName"></div></div>
                <div class="modal-field"><label>Student ID</label><div class="val" id="mStudentId"></div></div>
                <div class="modal-field"><label>Email</label><div class="val" id="mEmail"></div></div>
                <div class="modal-field"><label>Phone Number</label><div class="val" id="mPhone"></div></div>
                <div class="modal-field"><label>Course / Program</label><div class="val" id="mCourse"></div></div>
                <div class="modal-field"><label>Year Level</label><div class="val" id="mYear"></div></div>
                <div class="modal-field"><label>Gender</label><div class="val" id="mGender"></div></div>
                <div class="modal-field"><label>Birthday</label><div class="val" id="mBirthday"></div></div>
                <div class="modal-field"><label>Nationality</label><div class="val" id="mNationality"></div></div>
                <div class="modal-field"><label>Religion</label><div class="val" id="mReligion"></div></div>
                <div class="modal-field full"><label>Civil Status</label><div class="val" id="mMaritalStatus"></div></div>
                <div class="modal-field full"><label>Address</label><div class="val" id="mAddress"></div></div>
            </div>
        </div>
    </div>

    <!-- EXPANDED EDIT MODAL -->
    <div class="edit-modal" id="editModal">
        <div class="edit-content">
            <div class="edit-header"><h3>Edit Student Info</h3><button class="close-btn" onclick="closeModal('editModal')">&times;</button></div>
            <form method="post">
                <input type="hidden" name="edit_id" id="edit_id">
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                    <!-- 1st Row -->
                    <div class="field"><label>First Name</label><input type="text" name="edit_firstName" id="edit_firstName" required></div>
                    <div class="field"><label>Middle Name</label><input type="text" name="edit_middle_name" id="edit_middle_name" placeholder="Optional"></div>
                    <div class="field"><label>Last Name</label><input type="text" name="edit_lastName" id="edit_lastName" required></div>
                    
                    <!-- 2nd Row -->
                    <div class="field"><label>Student ID</label><input type="text" name="edit_student_id" id="edit_student_id" maxlength="9" required oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                    <div class="field" style="grid-column:span 2;"><label>Email</label><input type="email" name="edit_email" id="edit_email" required></div>
                    
                    <!-- 3rd Row -->
                    <div class="field"><label>Phone</label><input type="tel" name="edit_phone_no" id="edit_phone_no" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                    <div class="field"><label>Gender</label><select name="edit_gender" id="edit_gender"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                    <div class="field"><label>Birthday</label><input type="text" name="edit_birthday" id="edit_birthday" placeholder="mm/dd/yyyy"></div>

                    <!-- 4th Row -->
                    <div class="field"><label>Program</label><input type="text" name="edit_program" id="edit_program"></div>
                    <div class="field"><label>Year Level</label><select name="edit_year_level" id="edit_year_level"><option value="1st Year">1st Year</option><option value="2nd Year">2nd Year</option><option value="3rd Year">3rd Year</option><option value="4th Year">4th Year</option></select></div>
                    <div class="field"><label>Civil Status</label><select name="edit_marital_status" id="edit_marital_status"><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Separated">Separated</option><option value="Divorced">Divorced</option></select></div>

                    <!-- 5th Row -->
                    <div class="field"><label>Nationality</label><input type="text" name="edit_nationality" id="edit_nationality"></div>
                    <div class="field"><label>Religion</label><input type="text" name="edit_religion" id="edit_religion"></div>
                    <div class="field" style="grid-column:span 3;"><label>Address</label><input type="text" name="edit_address" id="edit_address"></div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" name="updateStudent" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- UI / NAV LOGIC ---
        const panels = document.querySelectorAll('.panel'); const navItems = document.querySelectorAll('.nav-item[data-panel]');
        function showPanel(name) { panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + name)); navItems.forEach(b => b.classList.toggle('active', b.dataset.panel === name)); }
        navItems.forEach(btn => btn.addEventListener('click', () => showPanel(btn.dataset.panel)));
        
        const navItemsForPush = document.querySelectorAll('.persona-nav .nav-item');
        navItemsForPush.forEach((item, index) => {
            item.addEventListener('mouseenter', () => {
                for(let i = 0; i < index; i++) navItemsForPush[i].style.transform = 'skewX(-15deg) translateY(-15px)';
                for(let i = index + 1; i < navItemsForPush.length; i++) navItemsForPush[i].style.transform = 'skewX(-15deg) translateY(15px)';
                item.style.transform = 'skewX(-15deg) translateX(15px) scale(1.05)';
            });
            item.addEventListener('mouseleave', () => { navItemsForPush.forEach(nav => { nav.style.transform = ''; }); });
        });

        // --- VIEW MODAL LOGIC ---
        function openStudentModal(data) {
            document.getElementById('mName').innerText = (data.firstName || '') + ' ' + (data.middle_name ? data.middle_name + ' ' : '') + (data.lastName || '');
            document.getElementById('mStudentId').innerText = data.student_id || 'N/A'; document.getElementById('mEmail').innerText = data.email || 'N/A';
            document.getElementById('mPhone').innerText = data.phone_no || 'N/A'; document.getElementById('mCourse').innerText = data.program || 'N/A';
            document.getElementById('mYear').innerText = data.year_level || 'N/A'; document.getElementById('mGender').innerText = data.gender || 'N/A';
            document.getElementById('mBirthday').innerText = data.birthday || 'N/A'; document.getElementById('mNationality').innerText = data.nationality || 'N/A';
            document.getElementById('mReligion').innerText = data.religion || 'N/A'; document.getElementById('mMaritalStatus').innerText = data.marital_status || 'N/A';
            document.getElementById('mAddress').innerText = data.address || 'N/A';
            const picImg = document.getElementById('modalPic'); const picText = document.getElementById('modalAvatarText');
            if (data.profile_pic && data.profile_pic !== '') { picImg.src = data.profile_pic; picImg.style.display = 'block'; picText.style.display = 'none'; } else { picImg.style.display = 'none'; picText.style.display = 'flex'; picText.innerText = ((data.firstName || '').charAt(0) + (data.lastName || '').charAt(0)).toUpperCase(); }
            document.getElementById('studentModal').classList.add('active');
        }

        // --- EDIT MODAL LOGIC ---
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.Id; document.getElementById('edit_firstName').value = data.firstName || '';
            document.getElementById('edit_middle_name').value = data.middle_name || ''; document.getElementById('edit_lastName').value = data.lastName || '';
            document.getElementById('edit_student_id').value = data.student_id || ''; document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_phone_no').value = data.phone_no || ''; document.getElementById('edit_gender').value = data.gender || 'Male';
            document.getElementById('edit_birthday').value = data.birthday || ''; document.getElementById('edit_program').value = data.program || '';
            document.getElementById('edit_year_level').value = data.year_level || '1st Year'; document.getElementById('edit_marital_status').value = data.marital_status || 'Single';
            document.getElementById('edit_nationality').value = data.nationality || ''; document.getElementById('edit_religion').value = data.religion || '';
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        document.querySelectorAll('.modal-overlay, .edit-modal').forEach(modal => { modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); }); });

        // --- PRINTING LOGIC ---
        function generatePrintHTML(student, printedBy, role, isBatch = false) {
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/';
            let picHtml = (student.profile_pic && student.profile_pic !== '') 
                ? `<img src="${baseUrl}${student.profile_pic}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #000;" alt="Profile">` 
                : `<div style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; background: #eee;">${(student.firstName.charAt(0) + student.lastName.charAt(0)).toUpperCase()}</div>`;
            
            return `
                <div style="margin-bottom: ${isBatch ? '50px' : '0'}; page-break-after: ${isBatch ? 'always' : 'auto'};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px;">
                        <div style="display: flex; flex-direction: column;">
                            <img src="${baseUrl}gsc_logo.png" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #000;" alt="GSC Logo">
                            <div style="margin-top: 10px; font-size: 20px; font-weight: bold; text-transform: uppercase;">Student Personal Information</div>
                        </div>
                        <div>${picHtml}</div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="grid-column: span 2; margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Full Name</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.firstName} ${student.lastName}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Student ID</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.student_id || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Email</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.email || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Phone Number</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.phone_no || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Gender</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.gender || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Birthday</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.birthday || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Nationality</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.nationality || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Religion</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.religion || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Civil Status</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.marital_status || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Course / Program</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.program || 'N/A'}</div></div>
                        <div style="margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Year Level</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.year_level || 'N/A'}</div></div>
                        <div style="grid-column: span 2; margin-bottom: 15px;"><label style="font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase;">Address</label><div style="font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">${student.address || 'N/A'}</div></div>
                    </div>
                    <div style="margin-top: ${isBatch ? '20px' : '50px'}; text-align: right; font-size: 12px; color: #333; border-top: 1px solid #ccc; padding-top: 10px;">
                        <strong>Printed By:</strong> ${printedBy} (${role.toUpperCase()})<br>
                        <strong>Date Printed:</strong> ${new Date().toLocaleString()}
                    </div>
                </div>
            `;
        }

        function printSingleStudent(data, printedBy, role) {
            const iframe = document.createElement('iframe'); iframe.style.display = 'none'; document.body.appendChild(iframe);
            const doc = iframe.contentWindow.document;
            doc.write(`<html><head><title>Student Info - ${data.student_id}</title><style>body { font-family: Arial, sans-serif; padding: 40px; }</style></head><body>${generatePrintHTML(data, printedBy, role, false)}</body></html>`);
            doc.close(); iframe.contentWindow.focus(); iframe.contentWindow.print();
            setTimeout(() => { document.body.removeChild(iframe); }, 1000);
        }

        function printAllStudents(studentsArray, printedBy, role) {
            if(studentsArray.length === 0) { alert("No students to print."); return; }
            const iframe = document.createElement('iframe'); iframe.style.display = 'none'; document.body.appendChild(iframe);
            const doc = iframe.contentWindow.document;
            
            let allHtml = '';
            studentsArray.forEach(student => { allHtml += generatePrintHTML(student, printedBy, role, true); });
            
            doc.write(`<html><head><title>All Students Batch</title><style>body { font-family: Arial, sans-serif; padding: 40px; } @media print { .page-break { page-break-after: always; } }</style></head><body>${allHtml}</body></html>`);
            doc.close(); iframe.contentWindow.focus(); iframe.contentWindow.print();
            setTimeout(() => { document.body.removeChild(iframe); }, 1000);
        }
    </script>
</body>
</html>
