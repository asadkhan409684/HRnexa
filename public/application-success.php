<?php
session_start();
$pageTitle = "Application Successful";
$currentPage = "career";

// Redirect if not coming from a successful submission
if (!isset($_SESSION['application_success'])) {
    header('Location: career.php');
    exit;
}

// Clear the success flag after viewing
unset($_SESSION['application_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success | HRnexa</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
    :root {
        --primary-dark: #0f172a;
        --accent-color: #d97706;
    }
    
    body { 
        font-family: 'Inter', sans-serif; 
        background: #f8fafc;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .success-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        padding: 50px;
        text-align: center;
        max-width: 600px;
        margin: 50px auto;
        transform: translateY(20px);
    }

    .success-icon {
        width: 100px;
        height: 100px;
        background: #10b981;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        margin: 0 auto 30px;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
    }

    .success-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: var(--primary-dark);
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    .success-text {
        color: #64748b;
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 40px;
    }

    .btn-home {
        background: var(--primary-dark);
        color: white;
        padding: 15px 40px;
        border-radius: 40px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-block;
    }

    .btn-home:hover {
        background: #1e293b;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.1);
        color: white;
    }

    .navbar { background: var(--primary-dark); }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="index.php">HRnexa</a>
        </div>
    </nav>

    <main class="flex-grow-1 d-flex align-items-center">
        <div class="container">
            <div class="success-card" data-aos="zoom-in" data-aos-duration="800">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="success-title">Thank You!</h1>
                <p class="success-text">
                    Your application has been received successfully. Our HR team will carefully review your profile. 
                    If your qualifications match our requirements, we'll reach out to you via email or phone for the next steps.
                </p>
                <a href="career.php" class="btn-home">
                    <i class="fas fa-arrow-left me-2"></i> Back to Careers
                </a>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-muted small">
        &copy; <?php echo date('Y'); ?> HRnexa. All rights reserved.
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>
</html>
