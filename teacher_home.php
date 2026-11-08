<?php
include 'connect2.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$role = $_SESSION['admin_role'];
if (!in_array($role, ['teacher', 'staff'], true)) {
    header("Location: admin_home.php");
    exit();
}

$q = trim($_GET['q'] ?? '');
$year = trim($_GET['year'] ?? '');

$sql = "SELECT u.Id, u.firstName, u.lastName, u.email, u.profile_pic, u.gender, u.birthday,
               sp.student_id, sp.phone_no, sp.program, sp.year_level, sp.address, sp.nationality, sp.religion, sp.marital_status 
        FROM users u LEFT JOIN student_profiles sp ON u.Id = sp.user_id WHERE 1=1";
$params = [];
$types = "";

if ($q !== "") {
    $sql .= " AND (u.firstName LIKE ? OR u.lastName LIKE ? OR u.email LIKE ? OR sp.student_id LIKE ? OR sp.program LIKE ?)";
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sssss";
}
if ($year !== "") {
    $sql .= " AND sp.year_level = ?";
    $params[] = $year; $types .= "s";
}
$sql .= " ORDER BY sp.student_id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result();

// Get logged-in teacher/staff info
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
</head>
<body class="home-page">
    <div class="bg-shapes">
        <span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span>
    </div>

    <div class="profile-card" style="max-width: 95%; margin-top: 20px;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">
                    Teacher / Staff Portal
                    <small>Logged in as: <?= $fullName ?> (<?= $printedByRole ?>)</small>
                </div>
            </div>
            <a href="logout2.php" class="logout" style="text-decoration:none;">Logout</a>
        </header>

        <form method="get" action="teacher_home.php" class="no-print" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end; margin: 20px 0;">
            <div class="field" style="flex: 2; min-width: 220px;">
                <label>Search</label>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name, Student ID, Email, Program">
            </div>
            <div class="field" style="flex: 1; min-width: 160px;">
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
                            <td style="padding:12px 10px; display: flex; align-items: center; gap: 10px;">
                                <?php if(!empty($row['profile_pic']) && file_exists($row['profile_pic'])): ?>
                                    <img src="<?= htmlspecialchars($row['profile_pic']) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--gold);">
                                <?php else: ?>
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #333; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--gold); border: 1px solid #555;">
                                        <?= strtoupper(substr($row['firstName'], 0, 1) . substr($row['lastName'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?>
                            </td>
                            <td style="padding:12px 10px;"><?= htmlspecialchars($row['email']) ?></td>
                            <td style="padding:12px 10px;"><?= htmlspecialchars($row['program'] ?? 'N/A') ?> - <?= htmlspecialchars($row['year_level'] ?? 'N/A') ?></td>
                            <td style="padding:12px 10px;">
                                <!-- ADDED LOGGED IN NAME & ROLE TO THE ONCLICK -->
                                <button type="button" onclick='printStudent(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>, "<?= $fullName ?>", "<?= $printedByRole ?>")' style="background: var(--gold); color: #000; padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Updated to accept printedBy and printedByRole
        function printStudent(data, printedBy, printedByRole) {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;
            
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/';
            const logoUrl = baseUrl + 'gsc_logo.png';

            let picHtml = '';
            if (data.profile_pic && data.profile_pic !== '') {
                const picUrl = baseUrl + data.profile_pic;
                picHtml = `<img src="${picUrl}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #000;" alt="Profile">`;
            } else {
                const initials = (data.firstName.charAt(0) + data.lastName.charAt(0)).toUpperCase();
                picHtml = `<div style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; background: #eee;">${initials}</div>`;
            }

            doc.write(`
                <html>
                <head>
                    <title>Student Info - ${data.student_id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; color: #000; padding: 40px; }
                        
                        .print-header { 
                            display: flex; 
                            justify-content: space-between; 
                            align-items: flex-start; 
                            border-bottom: 2px solid #000; 
                            padding-bottom: 20px; 
                            margin-bottom: 30px;
                        }
                        .header-left { display: flex; flex-direction: column; }
                        .header-title { margin-top: 10px; font-size: 20px; font-weight: bold; text-transform: uppercase; }
                        
                        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                        .field { margin-bottom: 15px; }
                        .field label { font-weight: bold; font-size: 12px; text-transform: uppercase; color: #555; display: block; margin-bottom: 4px; }
                        .field div { font-size: 16px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
                        .full { grid-column: span 2; }
                        
                        .logo-circle { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #000; }
                        
                        /* NEW: Print Footer Style */
                        .print-footer {
                            margin-top: 50px;
                            text-align: right;
                            font-size: 12px;
                            color: #333;
                            border-top: 1px solid #ccc;
                            padding-top: 10px;
                        }
                    </style>
                </head>
                <body>
                    
                    <div class="print-header">
                        <div class="header-left">
                            <img src="${logoUrl}" class="logo-circle" alt="GSC Logo">
                            <div class="header-title">Student Personal Information</div>
                        </div>
                        <div class="header-right">
                            ${picHtml}
                        </div>
                    </div>

                    <div class="grid">
                        <div class="field full"><label>Full Name</label><div>${data.firstName} ${data.lastName}</div></div>
                        <div class="field"><label>Student ID</label><div>${data.student_id || 'N/A'}</div></div>
                        <div class="field"><label>Email</label><div>${data.email || 'N/A'}</div></div>
                        <div class="field"><label>Phone Number</label><div>${data.phone_no || 'N/A'}</div></div>
                        <div class="field"><label>Gender</label><div>${data.gender || 'N/A'}</div></div>
                        <div class="field"><label>Birthday</label><div>${data.birthday || 'N/A'}</div></div>
                        <div class="field"><label>Nationality</label><div>${data.nationality || 'N/A'}</div></div>
                        <div class="field"><label>Religion</label><div>${data.religion || 'N/A'}</div></div>
                        <div class="field"><label>Marital Status</label><div>${data.marital_status || 'N/A'}</div></div>
                        <div class="field"><label>Course / Program</label><div>${data.program || 'N/A'}</div></div>
                        <div class="field"><label>Year Level</label><div>${data.year_level || 'N/A'}</div></div>
                        <div class="field full"><label>Address</label><div>${data.address || 'N/A'}</div></div>
                    </div>

                    
                    <div class="print-footer">
                        <strong>Printed By:</strong> ${printedBy} (${printedByRole.toUpperCase()})<br>
                        <strong>Date Printed:</strong> ${new Date().toLocaleString()}
                    </div>

                </body>
                </html>
            `);

            doc.close();

            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }
    </script>
</body>
</html>