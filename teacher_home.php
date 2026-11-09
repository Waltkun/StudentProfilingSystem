<?php
include 'connect2.php';
session_start();

if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit(); }

$role = $_SESSION['admin_role'];
if (!in_array($role, ['teacher', 'staff'], true)) { header("Location: admin_home.php"); exit(); }

$q = trim($_GET['q'] ?? '');
$year = trim($_GET['year'] ?? '');
$course = trim($_GET['course'] ?? '');

$courseQuery = $conn->query("SELECT DISTINCT program FROM student_profiles WHERE program IS NOT NULL AND program != '' ORDER BY program ASC");

$sql = "SELECT u.Id, u.firstName, u.lastName, u.email, u.profile_pic, u.gender, u.birthday,
            sp.student_id, sp.middle_name, sp.phone_no, sp.program, sp.year_level, sp.address, sp.nationality, sp.religion, sp.marital_status 
        FROM users u LEFT JOIN student_profiles sp ON u.Id = sp.user_id WHERE 1=1";
$params = []; $types = "";

if ($q !== "") {
    $sql .= " AND (u.firstName LIKE ? OR u.lastName LIKE ? OR u.email LIKE ? OR sp.student_id LIKE ? OR sp.program LIKE ?)";
    $like = "%$q%"; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $types .= "sssss";
}
if ($year !== "") { $sql .= " AND sp.year_level = ?"; $params[] = $year; $types .= "s"; }
if ($course !== "") { $sql .= " AND sp.program = ?"; $params[] = $course; $types .= "s"; }
$sql .= " ORDER BY sp.student_id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result();

$fullName = htmlspecialchars($_SESSION['admin_name']);
$printedByRole = htmlspecialchars($_SESSION['admin_role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher / Staff Portal</title>
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
    </style>
</head>
<body class="home-page">
    <div class="bg-shapes"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></div>

    <div class="profile-card" style="max-width: 95%; margin-top: 20px;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">Teacher / Staff Portal<small>Logged in as: <?= $fullName ?> (<?= $printedByRole ?>)</small></div>
            </div>
            <a href="logout2.php" class="logout" style="text-decoration:none;">Logout</a>
        </header>

        <form method="get" action="teacher_home.php" class="no-print" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end; margin: 20px 0;">
            <div class="field" style="flex: 2; min-width: 200px;">
                <label>Search</label>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name, ID, Email...">
            </div>
            <div class="field" style="flex: 1; min-width: 150px;">
                <label>Course</label>
                <select name="course">
                    <option value="">All Courses</option>
                    <?php while($c = $courseQuery->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['program']) ?>" <?= $course === $c['program'] ? 'selected' : '' ?>><?= htmlspecialchars($c['program']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="field" style="flex: 1; min-width: 150px;">
                <label>Year Level</label>
                <select name="year">
                    <option value="">All Years</option>
                    <?php foreach (["1st Year","2nd Year","3rd Year","4th Year"] as $y): ?>
                        <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="save-btn">Filter</button>
            <a href="teacher_home.php" class="save-btn" style="background:transparent;border:1px solid var(--muted);color:var(--muted);text-decoration:none;">Reset</a>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color:var(--gold); font-size: 1.8rem; text-transform:uppercase; letter-spacing:1px; margin:0;">Student Directory</h2>
            <!-- PRINT ALL BUTTON FOR TEACHER -->
            <button type="button" onclick='printAllStudents(<?= htmlspecialchars(json_encode($students->fetch_all(MYSQLI_ASSOC)), ENT_QUOTES, "UTF-8") ?>, "<?= $fullName ?>")' style="background: var(--gold); color: #000; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                <i class="fas fa-print"></i> Print Filtered List
            </button>
        </div>

        <?php $students->data_seek(0); // Reset pointer ?>

        <div style="overflow-x: auto; background: #0b0b10; padding: 20px; border-radius: 10px; border: 1px solid #33334a;" class="table-container">
            <table style="width:100%; border-collapse: collapse; text-align: left; color: var(--text);" id="studentTable">
                <thead>
                    <tr style="border-bottom: 2px solid #33334a;">
                        <th style="padding:12px 10px; color:var(--gold);">Student ID</th>
                        <th style="padding:12px 10px; color:var(--gold);">Name</th>
                        <th style="padding:12px 10px; color:var(--gold);">Email</th>
                        <th style="padding:12px 10px; color:var(--gold);">Course/Year</th>
                        <th style="padding:12px 10px; color:var(--gold);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students->num_rows === 0): ?>
                        <tr><td colspan="5" style="padding:20px; color:var(--muted);">No students found.</td></tr>
                    <?php else: while($row = $students->fetch_assoc()): ?>
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
                            <td style="padding:12px 10px;">
                                <!-- SINGLE PRINT BUTTON -->
                                <button type="button" onclick='printSingleStudent(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>, "<?= $fullName ?>", "<?= $printedByRole ?>")' style="background: var(--gold); color: #000; padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
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

    <script>
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
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        document.getElementById('studentModal').addEventListener('click', function(e) { if (e.target === this) closeModal('studentModal'); });

        // --- SINGLE PRINT (Kept as the detailed profile page) ---
        function generatePrintHTML(student, printedBy, role, isBatch = false) {
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/';
            let picHtml = (student.profile_pic && student.profile_pic !== '') 
                ? `<img src="${baseUrl}${student.profile_pic}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #000;" alt="Profile">` 
                : `<div style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; background: #eee;">${(student.firstName.charAt(0) + student.lastName.charAt(0)).toUpperCase()}</div>`;
            
            return `
                <div>
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
                    <div style="margin-top: 50px; text-align: right; font-size: 12px; color: #333; border-top: 1px solid #ccc; padding-top: 10px;">
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

        // --- PRINT ALL (Now uses the Landscape Table Format) ---
        function printAllStudents(studentsArray, printedBy) {
            if(studentsArray.length === 0) { alert("No students to print."); return; }
            const iframe = document.createElement('iframe'); iframe.style.display = 'none'; document.body.appendChild(iframe);
            const doc = iframe.contentWindow.document;
            
            let rowsHtml = '';
            studentsArray.forEach(s => {
                rowsHtml += `
                    <tr>
                        <td>${s.student_id || 'N/A'}</td>
                        <td>${s.firstName} ${s.lastName}</td>
                        <td>${s.email || 'N/A'}</td>
                        <td>${s.phone_no || 'N/A'}</td>
                        <td>${s.program || 'N/A'}</td>
                        <td>${s.year_level || 'N/A'}</td>
                        <td>${s.address || 'N/A'}</td>
                    </tr>
                `;
            });

            doc.write(`
                <html>
                <head>
                    <title>Student Directory</title>
                    <style>
                        @page { size: landscape; margin: 15mm; }
                        body { font-family: Arial, sans-serif; color: #000; }
                        
                        .header { display: flex; align-items: center; gap: 15px; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
                        .header img { width: 60px; height: 60px; border-radius: 50%; }
                        .header-text h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
                        .header-text p { margin: 5px 0 0 0; font-size: 12px; color: #555; }

                        table { width: 100%; border-collapse: collapse; font-size: 11px; }
                        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
                        th { background-color: #f0f0f0; font-weight: bold; }
                        
                        .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 12px; color: #333; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <img src="${window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/'}gsc_logo.png" alt="Logo">
                        <div class="header-text">
                            <h1>Student Directory</h1>
                            <p>Generated by <?= $fullName ?> on ${new Date().toLocaleString()}</p>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>

                    <div class="footer">
                        <div><strong>Printed By:</strong> ${printedBy}</div>
                        <div><strong>Total Students:</strong> ${studentsArray.length}</div>
                    </div>
                </body>
                </html>
            `);
            
            doc.close(); iframe.contentWindow.focus(); iframe.contentWindow.print();
            setTimeout(() => { document.body.removeChild(iframe); }, 1000);
        }
    </script>
</body>
</html>
