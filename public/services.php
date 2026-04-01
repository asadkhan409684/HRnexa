<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "Our Services";
$currentPage = "services";
include 'layouts/header.php';
?>

<!-- Services Hero Section -->
<section class="page-hero services-hero-bg d-flex align-items-center">
    <div class="container position-relative z-1 text-center">
        <span class="d-inline-block py-1 px-3 rounded-pill bg-white bg-opacity-10 text-white border border-light border-opacity-25 small mb-4" data-aos="fade-down">
            Our Expertise
        </span>
        <h1 class="display-3 fw-bold mb-4 font-serif" data-aos="fade-up" data-aos-delay="100">
            Solutions that <span style="color: var(--hero-accent);">Drive Growth</span>
        </h1>
        <p class="lead mb-0 opacity-75 mx-auto" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="200">
            Providing high-end solutions for your digital and human resource needs. We build the tools that build your business.
        </p>
    </div>

    <!-- Wave Divider -->
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#f8f9fa" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Services Grid -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Web Dev -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card service-card h-100 p-4 text-center">
                    <div class="service-icon mx-auto"><i class="fas fa-laptop-code"></i></div>
                    <h5 class="fw-bold mb-3">Web Development</h5>
                    <p class="text-muted small">Modern, responsive websites built with the latest technologies like PHP, Bootstrap, and JS.</p>
                </div>
            </div>
            <!-- Mobile App -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card service-card h-100 p-4 text-center">
                    <div class="service-icon mx-auto"><i class="fas fa-mobile-alt"></i></div>
                    <h5 class="fw-bold mb-3">Mobile Apps</h5>
                    <p class="text-muted small">Cross-platform mobile applications that provide seamless user experiences across devices.</p>
                </div>
            </div>
            <!-- HR Software -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card service-card h-100 p-4 text-center">
                    <div class="service-icon mx-auto"><i class="fas fa-users-cog"></i></div>
                    <h5 class="fw-bold mb-3">HR Software</h5>
                    <p class="text-muted small">Customized human resource management systems to automate payroll and attendance.</p>
                </div>
            </div>
            <!-- UI/UX -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card service-card h-100 p-4 text-center">
                    <div class="service-icon mx-auto"><i class="fas fa-paint-brush"></i></div>
                    <h5 class="fw-bold mb-3">UI/UX Design</h5>
                    <p class="text-muted small">Beautiful and intuitive design systems that focus on user engagement and conversion.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Info Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="fw-bold mb-4">How We Deliver Results</h2>
                <div class="mb-4">
                    <h6 class="fw-bold"><i class="fas fa-check text-primary me-2"></i> Thorough Analysis</h6>
                    <p class="text-muted small">We start by understanding your specific needs and business goals.</p>
                </div>
                <div class="mb-4">
                    <h6 class="fw-bold"><i class="fas fa-check text-primary me-2"></i> Agile Development</h6>
                    <p class="text-muted small">We build in cycles, allowing for feedback and constant improvement.</p>
                </div>
                <div>
                    <h6 class="fw-bold"><i class="fas fa-check text-primary me-2"></i> Quality Assurance</h6>
                    <p class="text-muted small">Every product undergoes rigorous testing before it reaches you.</p>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="200">
                <img src="https://img.freepik.com/free-vector/modern-development-concept-with-flat-design_23-2147888741.jpg" alt="Service Process" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="container pb-5">
    <div class="final-cta text-center" data-aos="zoom-in">
        <div class="position-relative z-1">
            <h2 class="display-5 fw-bold mb-4">Ready to start your project?</h2>
            <p class="lead mb-4 opacity-75">Let's build something amazing together.</p>
            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary">Contact Us Today</a>
        </div>
    </div>
</section>

<?php include 'layouts/footer.php'; ?>
