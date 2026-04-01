<?php
// submit-application.php
// Handles job application via AJAX with extended fields
header('Content-Type: application/json');
session_start();

// Include database connection
require_once __DIR__ . '/../app/Config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    // 1. Collect and sanitize form data
    $requisition_id = intval($_POST['job_requisition_id'] ?? 0);
    $jobPosition    = htmlspecialchars($_POST['job_position'] ?? '');
    
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Invalid email address.');
    }

    // New Fields from Specification
    $fullName        = strtoupper(htmlspecialchars($_POST['full_name'] ?? ''));
    $fatherName      = htmlspecialchars($_POST['father_name'] ?? '');
    $motherName      = htmlspecialchars($_POST['mother_name'] ?? '');
    $dob             = htmlspecialchars($_POST['dob'] ?? '');
    $religion        = htmlspecialchars($_POST['religion'] ?? '');
    $bloodGroup      = htmlspecialchars($_POST['blood_group'] ?? '');
    $homeDistrict    = htmlspecialchars($_POST['home_district'] ?? '');
    $thanaUpazila    = htmlspecialchars($_POST['thana_upazila'] ?? '');
    $gender          = htmlspecialchars($_POST['gender'] ?? '');
    $nidNo           = htmlspecialchars($_POST['nid_no'] ?? '');
    $maritalStatus   = htmlspecialchars($_POST['marital_status'] ?? '');
    $spouseName      = ($maritalStatus === 'Married') ? htmlspecialchars($_POST['spouse_name'] ?? '') : '';
    $phone           = htmlspecialchars($_POST['phone'] ?? '');
    $nationality     = htmlspecialchars($_POST['nationality'] ?? 'Bangladeshi');
    $presentAddr     = htmlspecialchars($_POST['present_address'] ?? '');
    $permanentAddr   = htmlspecialchars($_POST['permanent_address'] ?? '');
    
    $expLevel        = htmlspecialchars($_POST['experience_level'] ?? 'Fresher');
    $lastCompany     = ($expLevel === 'Experienced') ? htmlspecialchars($_POST['last_company'] ?? '') : '';
    $lastJobTitle    = ($expLevel === 'Experienced') ? htmlspecialchars($_POST['last_job_title'] ?? '') : '';
    $expDuration     = ($expLevel === 'Experienced') ? htmlspecialchars($_POST['experience_duration'] ?? '') : '';
    $responsibilities= ($expLevel === 'Experienced') ? htmlspecialchars($_POST['responsibilities'] ?? '') : '';
    
    $skills          = htmlspecialchars($_POST['skills'] ?? '');
    $otherSkills     = htmlspecialchars($_POST['other_skills'] ?? '');
    $portfolioLink   = htmlspecialchars($_POST['portfolio_link'] ?? '');
    $expectedSalary  = floatval($_POST['expected_salary'] ?? 0);

    // Process Education Data
    $educationItems = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["exam_$i"])) {
            $educationItems[] = [
                'exam' => htmlspecialchars($_POST["exam_$i"]),
                'board' => htmlspecialchars($_POST["board_$i"]),
                'year' => htmlspecialchars($_POST["year_$i"]),
                'div' => htmlspecialchars($_POST["div_$i"] ?? ''),
                'grade' => htmlspecialchars($_POST["grade_$i"]),
                'subject' => htmlspecialchars($_POST["subject_$i"])
            ];
        }
    }
    $educationJson = json_encode($educationItems);

    // Process Professional Certifications
    $certItems = [];
    for ($i = 1; $i <= 2; $i++) {
        if (!empty($_POST["cert_name_$i"])) {
            $certItems[] = [
                'name' => htmlspecialchars($_POST["cert_name_$i"]),
                'year' => htmlspecialchars($_POST["cert_year_$i"]),
                'grade' => htmlspecialchars($_POST["cert_grade_$i"])
            ];
        }
    }
    $certsJson = json_encode($certItems);

    if ($requisition_id <= 0) {
        throw new Exception('Invalid job requisition.');
    }

    // 2. Duplicate Prevention
    $stmt = $conn->prepare("SELECT a.id FROM candidate_applications a 
                           JOIN candidates c ON a.candidate_id = c.id 
                           WHERE c.email = ? AND a.job_requisition_id = ?");
    $stmt->bind_param("si", $email, $requisition_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('You have already applied for this position.');
    }

    // 3. Handle File Uploads
    $uploadDir = 'assets/uploads/applications/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    function processUpload($fileKey, $allowedExts, $maxSize, $prefix) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return '';
        
        $file = $_FILES[$fileKey];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowedExts)) throw new Exception("Invalid file type for $fileKey.");
        if ($file['size'] > $maxSize) throw new Exception("File size too large for $fileKey. Max: " . ($maxSize/1024) . "KB");
        
        $newName = $prefix . '_' . time() . '_' . uniqid() . '.' . $ext;
        $target = 'assets/uploads/applications/' . $newName;
        
        if (move_uploaded_file($file['tmp_name'], $target)) return $target;
        throw new Exception("Failed to upload $fileKey.");
    }

    $photoPath     = processUpload('photo', ['jpg', 'jpeg'], 204800, 'photo');    // 200KB
    $signaturePath = processUpload('signature', ['jpg', 'jpeg', 'png'], 102400, 'sig'); // 100KB
    $resumePath    = processUpload('resume', ['pdf', 'doc', 'docx'], 5242880, 'cv'); // 5MB

    if (!$resumePath) throw new Exception('Resume upload is required.');
    if (!$photoPath) throw new Exception('Photo upload is required.');
    if (!$signaturePath) throw new Exception('Signature upload is required.');

    // 4. Database Persistence
    
    // Manage Candidate Record
    $checkStmt = $conn->prepare("SELECT id FROM candidates WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    
    if ($res->num_rows > 0) {
        $candidate_id = $res->fetch_assoc()['id'];
        $updateSql = "UPDATE candidates SET 
            name = ?, father_name = ?, mother_name = ?, dob = ?, religion = ?, blood_group = ?, 
            home_district = ?, thana_upazila = ?, gender = ?, nid_no = ?, 
            marital_status = ?, spouse_name = ?, phone = ?, nationality = ?, 
            present_address = ?, permanent_address = ?, experience_level = ?, 
            last_company = ?, last_job_title = ?, experience_duration = ?, responsibilities = ?, 
            skills = ?, education = ?, professional_certs = ?, other_skills = ?, 
            portfolio_link = ?, photo_path = ?, signature_path = ?, resume_path = ?, 
            expected_salary = ?, status = 'screening' 
            WHERE id = ?";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssssssssssssssssssssssssssssdi", 
            $fullName, $fatherName, $motherName, $dob, $religion, $bloodGroup,
            $homeDistrict, $thanaUpazila, $gender, $nidNo,
            $maritalStatus, $spouseName, $phone, $nationality,
            $presentAddr, $permanentAddr, $expLevel,
            $lastCompany, $lastJobTitle, $expDuration, $responsibilities,
            $skills, $educationJson, $certsJson, $otherSkills,
            $portfolioLink, $photoPath, $signaturePath, $resumePath,
            $expectedSalary, $candidate_id
        );
        $stmt->execute();
    } else {
        $insertSql = "INSERT INTO candidates (
            name, email, father_name, mother_name, dob, religion, blood_group, 
            home_district, thana_upazila, gender, nid_no, marital_status, spouse_name, phone, 
            nationality, present_address, permanent_address, experience_level, 
            last_company, last_job_title, experience_duration, responsibilities, 
            skills, education, professional_certs, other_skills, 
            portfolio_link, photo_path, signature_path, resume_path, 
            expected_salary, source, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Career Page', 'new')";
        
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ssssssssssssssssssssssssssssssd", 
            $fullName, $email, $fatherName, $motherName, $dob, $religion, $bloodGroup,
            $homeDistrict, $thanaUpazila, $gender, $nidNo, $maritalStatus, $spouseName, $phone,
            $nationality, $presentAddr, $permanentAddr, $expLevel,
            $lastCompany, $lastJobTitle, $expDuration, $responsibilities,
            $skills, $educationJson, $certsJson, $otherSkills,
            $portfolioLink, $photoPath, $signaturePath, $resumePath,
            $expectedSalary
        );
        $stmt->execute();
        $candidate_id = $conn->insert_id;
    }

    // Create Application Entry
    $appStmt = $conn->prepare("INSERT INTO candidate_applications (candidate_id, job_requisition_id, status) VALUES (?, ?, 'applied')");
    $appStmt->bind_param("ii", $candidate_id, $requisition_id);
    $appStmt->execute();

    $_SESSION['application_success'] = true;
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

