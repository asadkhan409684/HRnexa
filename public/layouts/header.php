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
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/style.css?v=1.2">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <?php 
    // All main public pages now use the transparent/hero style
    $transparentPages = ['home', 'about', 'services', 'notice', 'career', 'contact'];
    $navClass = (in_array($currentPage, $transparentPages)) 
        ? 'navbar-transparent fixed-top navbar-dark' 
        : 'bg-white sticky-top shadow-sm navbar-light'; 
    ?>
    <nav class="navbar navbar-expand-lg <?php echo $navClass; ?> py-3 transition-all" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="<?php echo BASE_URL; ?>public/index.php">HRnexa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'home') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'about') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'services') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'notice') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/notice.php">Notice</a></li>
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'career') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/career.php">Career</a></li>
                    <li class="nav-item"><a class="nav-link px-3 <?php echo ($currentPage == 'contact') ? 'active text-primary fw-bold' : ''; ?>" href="<?php echo BASE_URL; ?>public/contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
