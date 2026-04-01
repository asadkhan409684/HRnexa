<?php
$pageTitle = "Job Application";
$currentPage = "career";

// Include database connection
require_once __DIR__ . '/../app/Config/database.php';

// Get job ID from query parameter
$jobId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jobData = null;

if ($jobId > 0) {
    $stmt = $conn->prepare("SELECT jr.*, d.name as department_name 
                           FROM job_requisitions jr 
                           JOIN departments d ON jr.department_id = d.id 
                           WHERE jr.id = ? AND jr.status = 'open'");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $jobData = $stmt->get_result()->fetch_assoc();
}

// Redirect if job not found or not open
if (!$jobData) {
    header('Location: career.php');
    exit();
}

$jobTitle = htmlspecialchars($jobData['title']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | HRnexa" : "HRnexa - Modern HR Management"; ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
    :root {
        --primary-dark: #0f172a;
        --accent-color: #d97706;
    }
    
    body { font-family: 'Inter', sans-serif; }
    
    .application-form-container {
        width: 100%;
        max-width: 900px;
        margin: 20px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .form-header {
        background: var(--primary-dark);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .progress-container {
        padding: 20px 40px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .progress { height: 8px; border-radius: 4px; }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .step-item { font-size: 0.8rem; font-weight: 600; color: #94a3b8; transition: all 0.3s; }
    .step-item.active { color: var(--primary-dark); }

    .form-step { display: none; padding: 40px; }
    .form-step.active { display: block; animation: fadeIn 0.5s ease; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 25px;
        border-left: 4px solid var(--accent-color);
        padding-left: 15px;
    }

    .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
    
    .form-control, .form-select {
        padding: 10px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    .form-control:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1); }

    .preview-box {
        width: 150px;
        height: 150px;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        overflow: hidden;
        background: #f8fafc;
    }

    .preview-box img { width: 100%; height: 100%; object-fit: cover; }

    .signature-preview { width: 250px; height: 80px; }

    .btn-next, .btn-submit { background: var(--primary-dark); color: white; border: none; padding: 12px 30px; border-radius: 30px; font-weight: 700; transition: all 0.3s; }
    .btn-next:hover, .btn-submit:hover { background: #1e293b; transform: translateX(5px); }
    
    .btn-prev { background: #e2e8f0; color: #475569; border: none; padding: 12px 30px; border-radius: 30px; font-weight: 700; }

    .navbar { background: var(--primary-dark); }
    .nav-link:hover { color: var(--accent-color) !important; }

    .required-mark { color: #ef4444; margin-left: 2px; }

    #spouseSection, #experienceDetails { display: none; }
</style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark py-3 sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="index.php">HRnexa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link px-3" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link px-3 active fw-bold" href="career.php">Career</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="application-form-container">
                <div class="form-header">
                    <h2 class="fw-bold mb-1">Join Our Team</h2>
                    <p class="mb-0 opacity-75">Applying for: <span class="text-warning"><?php echo $jobTitle; ?></span> (<?php echo htmlspecialchars($jobData['department_name']); ?>)</p>
                </div>

                <div class="progress-container">
                    <div class="progress">
                        <div class="progress-bar bg-warning" id="formProgress" role="progressbar" style="width: 33%;"></div>
                    </div>
                    <div class="step-indicator">
                        <div class="step-item active" id="step1Label">Personal Info</div>
                        <div class="step-item" id="step2Label">Experience & Skills</div>
                        <div class="step-item" id="step3Label">Uploads & Finalize</div>
                    </div>
                </div>

                <form id="jobApplicationForm" enctype="multipart/form-data">
                    <input type="hidden" name="job_requisition_id" value="<?php echo $jobId; ?>">
                    <input type="hidden" name="job_position" value="<?php echo $jobTitle; ?>">

                    <!-- STEP 1: Personal Information -->
                    <div class="form-step active" id="step1">
                        <h4 class="section-title">Personal Information</h4>
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label">Full Name of Candidate <span class="required-mark">*</span></label>
                                <input type="text" class="form-control text-uppercase" name="full_name" required id="fullNameInput">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Father's Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="father_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mother's Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="mother_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth <span class="required-mark">*</span></label>
                                <input type="date" class="form-control" name="dob" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender <span class="required-mark">*</span></label>
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion <span class="required-mark">*</span></label>
                                <select class="form-select" name="religion" required>
                                    <option value="">Select Religion</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Hinduism">Hinduism</option>
                                    <option value="Christianity">Christianity</option>
                                    <option value="Buddhism">Buddhism</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Blood Group <span class="required-mark">*</span></label>
                                <select class="form-select" name="blood_group" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option><option value="A-">A-</option>
                                    <option value="B+">B+</option><option value="B-">B-</option>
                                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                    <option value="O+">O+</option><option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">National ID Card No <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="nid_no" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Marital Status <span class="required-mark">*</span></label>
                                <select class="form-select" name="marital_status" id="maritalStatus" required>
                                    <option value="">Select Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Divorced">Divorced</option>
                                </select>
                            </div>
                            <div class="col-md-12" id="spouseSection">
                                <label class="form-label">Spouse Name</label>
                                <input type="text" class="form-control" name="spouse_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number <span class="required-mark">*</span></label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="required-mark">*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Home District <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="home_district" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thana / Upazila <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="thana_upazila" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nationality <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="nationality" value="Bangladeshi" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Present Address (Mailing Address) <span class="required-mark">*</span></label>
                                <textarea class="form-control" name="present_address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Permanent Address <span class="required-mark">*</span></label>
                                <textarea class="form-control" name="permanent_address" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="mt-5 text-end">
                            <button type="button" class="btn btn-next px-5" onclick="nextStep(2)">Next: Education & Experience <i class="fas fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    <!-- STEP 2: Education, Experience & Skills -->
                    <div class="form-step" id="step2">
                        <h4 class="section-title">Educational Qualification</h4>
                        <div class="table-responsive mb-4">
                            <style>
                                .edu-table th { background: #f8fafc; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
                                .edu-table td { padding: 4px !important; }
                                .edu-table .form-select-sm, .edu-table .form-control-sm { font-size: 12px; border-radius: 2px; }
                                .div-cgpa-cell { display: flex; align-items: center; gap: 5px; }
                                .div-cgpa-cell label { margin: 0; font-size: 11px; white-space: nowrap; }
                            </style>
                            <table class="table table-bordered align-middle edu-table">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width: 20%;">Examination</th>
                                        <th style="width: 20%;">Board / University</th>
                                        <th style="width: 10%;">Passing Year</th>
                                        <th style="width: 30%;">Division / CGPA</th>
                                        <th style="width: 20%;">Subj / Group</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i=1; $i<=4; $i++): ?>
                                    <tr>
                                        <td>
                                            <select name="exam_<?php echo $i; ?>" class="form-select form-select-sm">
                                                <option value="">Select Examination</option>
                                                <option value="S.S.C">S.S.C</option>
                                                <option value="H.S.C">H.S.C</option>
                                                <option value="Diploma">Diploma</option>
                                                <option value="Graduation">Graduation</option>
                                                <option value="Post Graduation">Post Graduation</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="board_<?php echo $i; ?>" class="form-select form-select-sm">
                                                <option value="">Select Board</option>
                                                <option value="Dhaka">Dhaka</option>
                                                <option value="Chattogram">Chattogram</option>
                                                <option value="Rajshahi">Rajshahi</option>
                                                <option value="Sylhet">Sylhet</option>
                                                <option value="Barishal">Barishal</option>
                                                <option value="Khulna">Khulna</option>
                                                <option value="National University">National University</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="year_<?php echo $i; ?>" class="form-control form-control-sm text-center" maxlength="4">
                                        </td>
                                        <td>
                                            <div class="div-cgpa-cell">
                                                <label>Division</label>
                                                <select name="div_<?php echo $i; ?>" class="form-select form-select-sm" style="width: 80px;">
                                                    <option value="">--</option>
                                                    <option value="1st">1st</option>
                                                    <option value="2nd">2nd</option>
                                                    <option value="3rd">3rd</option>
                                                </select>
                                                <label>Or</label>
                                                <input type="text" name="grade_<?php echo $i; ?>" class="form-control form-control-sm" placeholder="CGPA">
                                            </div>
                                        </td>
                                        <td>
                                            <select name="subject_<?php echo $i; ?>" class="form-select form-select-sm">
                                                <option value="">Select Subject</option>
                                                <option value="Science">Science</option>
                                                <option value="Commerce">Commerce</option>
                                                <option value="Arts">Arts</option>
                                                <option value="Computer Science">Computer Science</option>
                                                <option value="BBA">BBA</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="section-title mt-4">Professional Certification</h4>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Certification Name</th>
                                        <th>Attending Year</th>
                                        <th>Achieved Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i=1; $i<=2; $i++): ?>
                                    <tr>
                                        <td><input type="text" name="cert_name_<?php echo $i; ?>" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="cert_year_<?php echo $i; ?>" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="cert_grade_<?php echo $i; ?>" class="form-control form-control-sm"></td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="section-title mt-4">Experience & Skills</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Experience Level <span class="required-mark">*</span></label>
                                <select class="form-select" name="experience_level" id="experienceLevel" required>
                                    <option value="Fresher">Fresher</option>
                                    <option value="Experienced">Experienced</option>
                                </select>
                            </div>
                            <div class="row g-4 mt-1" id="experienceDetails">
                                <div class="col-md-6">
                                    <label class="form-label">Last Company Name</label>
                                    <input type="text" class="form-control" name="last_company">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Job Title</label>
                                    <input type="text" class="form-control" name="last_job_title">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Duration (From - To)</label>
                                    <input type="text" class="form-control" name="experience_duration" placeholder="e.g., Jan 2021 - Dec 2023">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Responsibilities</label>
                                    <textarea class="form-control" name="responsibilities" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Professional Skills <span class="required-mark">*</span></label>
                                <textarea class="form-control" name="skills" rows="3" placeholder="e.g., PHP, JavaScript, SQL, Leadership" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Other Skills</label>
                                <input type="text" class="form-control" name="other_skills" placeholder="e.g., Video Editing, Graphics Design">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Portfolio Link (Website/GitHub/Behance)</label>
                                <input type="url" class="form-control" name="portfolio_link" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mt-5 d-flex justify-content-between">
                            <button type="button" class="btn btn-prev px-5" onclick="prevStep(1)"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                            <button type="button" class="btn btn-next px-5" onclick="nextStep(3)">Next: Uploads <i class="fas fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>

                    <!-- STEP 3: Uploads & Finalize -->
                    <div class="form-step" id="step3">
                        <h4 class="section-title">Upload Documents & Salary</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Photo upload (400×400 px, Max 200KB) <span class="required-mark">*</span></label>
                                <div class="preview-box" id="photoPreviewContainer">
                                    <span class="text-muted small">400×400 px</span>
                                </div>
                                <input type="file" class="form-control" name="photo" id="photoInput" accept="image/jpeg,image/jpg" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Signature (Max 100KB) <span class="required-mark">*</span></label>
                                <div class="preview-box signature-preview" id="sigPreviewContainer">
                                    <span class="text-muted small">300x100 Recommended</span>
                                </div>
                                <input type="file" class="form-control" name="signature" id="sigInput" accept="image/jpeg,image/jpg,image/png" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Resume/CV (PDF/DOC, Max 5MB) <span class="required-mark">*</span></label>
                                <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expected Monthly Salary (BDT) <span class="required-mark">*</span></label>
                                <input type="number" class="form-control" name="expected_salary" required>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="declaration" required>
                                    <label class="form-check-label fw-bold" for="declaration">
                                        I confirm that all information provided is correct and subject to verification.
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 d-flex justify-content-between">
                            <button type="button" class="btn btn-prev px-5" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                            <button type="submit" class="btn btn-submit px-5" id="submitBtn">
                                <span>Submit Application <i class="fas fa-paper-plane ms-2"></i></span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'layouts/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        // Uppercase Name implementation
        document.getElementById('fullNameInput').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Step Navigation
        function nextStep(step) {
            // Basic validation for current step
            const currentStepEl = document.querySelector('.form-step.active');
            const inputs = currentStepEl.querySelectorAll('[required]');
            let valid = true;
            
            inputs.forEach(input => {
                if(!input.value) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if(!valid) {
                 alert('Please fill all required fields before proceeding.');
                 return;
            }

            document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            
            // Progress Bar & Labels
            const progress = (step === 1) ? 33 : (step === 2) ? 66 : 100;
            document.getElementById('formProgress').style.width = progress + '%';
            
            document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step + 'Label').classList.add('active');

            window.scrollTo(0, 0);
        }

        function prevStep(step) {
            document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            
            const progress = (step === 1) ? 33 : (step === 2) ? 66 : 100;
            document.getElementById('formProgress').style.width = progress + '%';

            document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step + 'Label').classList.add('active');
        }

        // Conditional Sections
        document.getElementById('maritalStatus').addEventListener('change', function() {
            const spouseSection = document.getElementById('spouseSection');
            spouseSection.style.display = (this.value === 'Married') ? 'block' : 'none';
        });

        document.getElementById('experienceLevel').addEventListener('change', function() {
            const expDetails = document.getElementById('experienceDetails');
            expDetails.style.display = (this.value === 'Experienced') ? 'flex' : 'none';
        });

        // Image Previews & Ratio Check
        function setupPreview(inputId, containerId, isPhoto = false) {
            document.getElementById(inputId).addEventListener('change', function(e) {
                const file = e.target.files[0];
                const container = document.getElementById(containerId);
                
                if (file) {
                    // Size validation
                    const maxSize = isPhoto ? 200000 : 100000; // 200KB vs 100KB
                    if(file.size > maxSize) {
                        alert('File size too large! Max allowed: ' + (maxSize/1000) + 'KB');
                        this.value = '';
                        container.innerHTML = '<span class="text-danger small">File too large</span>';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            if (isPhoto) {
                                // Ratio Check (Allow 5% margin)
                                const ratio = img.width / img.height;
                                if (ratio < 0.95 || ratio > 1.05) {
                                    alert('Photo must be 1:1 square ratio.');
                                    document.getElementById(inputId).value = '';
                                    container.innerHTML = '<span class="text-danger small">Invalid Ratio</span>';
                                    return;
                                }
                            }
                            container.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        setupPreview('photoInput', 'photoPreviewContainer', true);
        setupPreview('sigInput', 'sigPreviewContainer', false);

        // AJAX Submission
        document.getElementById('jobApplicationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const spinner = submitBtn.querySelector('.spinner-border');
            const btnText = submitBtn.querySelector('span:not(.spinner-border)');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.classList.add('d-none');

            const formData = new FormData(this);

            fetch('submit-application.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'application-success.php';
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.classList.remove('d-none');
            });
        });
    </script>
</body>
</html>
<?php 
// Layout functions
function renderFieldGroup($label, $name, $type = 'text', $required = false) {
    // Utility for cleaner code if needed
}
?>


