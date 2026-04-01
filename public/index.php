<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "Home";
$currentPage = "home";
include 'layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-corporate">
    <div class="container position-relative z-2">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center fade-in">
                
                <span class="hero-subtitle">Welcome to HRnexa</span>
                <h1 class="hero-title">Empowering Your<br>Workforce</h1>
                <p class="hero-desc mx-auto">Streamline your HR processes and unlock the true potential of your team with our comprehensive management methodology.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>public/services.php" class="btn btn-hero-accent rounded-pill px-5">Get Started</a>
                </div>

            </div>
        </div>
    </div>
    
    <!-- Wave Divider -->
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Stats Bar -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stats-card">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Happy Clients</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stats-card">
                    <div class="stat-number">10k+</div>
                    <div class="stat-label">Employees Managed</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stats-card">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">System Uptime</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="stats-card">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Premium Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By -->
<section class="trusted-section text-center overflow-hidden">
    <div class="container">
        <p class="text-muted small text-uppercase mb-5 fw-bold ls-1">Trusted by Industry Leaders</p>
        
        <div class="logo-marquee">
            <div class="marquee-content">
                <!-- Original Logos -->
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" alt="Google" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg" alt="IBM" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg" alt="Microsoft" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" alt="Netflix" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Samsung_Galaxy_logo_2024.svg" alt="Samsung" class="client-logo">
                
                <!-- Duplicate Logos for Loop -->
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" alt="Google" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg" alt="IBM" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg" alt="Microsoft" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" alt="Netflix" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple" class="client-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Samsung_Galaxy_logo_2024.svg" alt="Samsung" class="client-logo">
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5 bg-light overflow-hidden">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-6 fw-bold">Our Premium Solutions</h2>
            <div class="mx-auto bg-primary" style="height: 3px; width: 60px;"></div>
            <p class="text-muted mt-3">Tailored HR management tools for your modern enterprise.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-right" data-aos-delay="100">
                <div class="service-glass-card">
                    <div class="service-glass-icon"><i class="fas fa-users-cog"></i></div>
                    <h4>Workforce Planning</h4>
                    <p class="text-muted small">Efficiently manage your human capital with advanced organizational tools and reporting.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="service-glass-card">
                    <div class="service-glass-icon"><i class="fas fa-money-check-alt"></i></div>
                    <h4>Payroll Automation</h4>
                    <p class="text-muted small">Simplify payroll processes with automated tax calculations and direct integrations.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-left" data-aos-delay="300">
                <div class="service-glass-card">
                    <div class="service-glass-icon"><i class="fas fa-chart-line"></i></div>
                    <h4>Performance Analytics</h4>
                    <p class="text-muted small">Gain actionable insights into employee productivity and engagement through data.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="fw-bold mb-4 display-5">Why Choose HRnexa?</h2>
                <p class="text-muted mb-5">We provide more than just software; we provide a foundation for your company's growth and employee satisfaction.</p>
                
                <div class="d-flex mb-4">
                    <div class="me-4 shadow-sm rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle" style="width: 50px; height: 50px;">
                        <i class="fas fa-shield-alt text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold">Security & Compliance</h5>
                        <p class="text-muted small">Enterprise-grade security ensuring all your employee data is encrypted and safe.</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="me-4 shadow-sm rounded-circle d-flex align-items-center justify-content-center bg-success-subtle" style="width: 50px; height: 50px;">
                        <i class="fas fa-bolt text-success"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold">Lightning Fast Setup</h5>
                        <p class="text-muted small">Get your entire team onboarded in minutes, not days, with our intuitive wizard.</p>
                    </div>
                </div>
                
                <div class="d-flex">
                    <div class="me-4 shadow-sm rounded-circle d-flex align-items-center justify-content-center bg-info-subtle" style="width: 50px; height: 50px;">
                        <i class="fas fa-headset text-info"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold">Human-Centric Support</h5>
                        <p class="text-muted small">Our dedicated support team is available 24/7 to help you solve any HR challenges.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="zoom-in">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80" alt="Team Discussion" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 start-0 translate-middle-y bg-white p-4 rounded-4 shadow-lg m-4 d-none d-md-block" data-aos="fade-up" data-aos-delay="500">
                        <div class="d-flex align-items-center">
                            <div class="display-4 fw-bold text-primary me-3">15k</div>
                            <div class="small fw-bold text-dark">Active Users<br><span class="text-muted">Growing Daily</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-6 fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted mt-3">Everything you need to know about HRnexa.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Is HRnexa suitable for small businesses?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! HRnexa is built to scale. We have special pricing tiers and features designed specifically for startups and growing teams.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How secure is my employee data?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We take security very seriously. All data is encrypted using AES-256 at rest and TLS 1.3 in transit. We also undergo regular security audits.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I integrate HRnexa with my existing tools?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely. HRnexa offers a robust API and pre-built integrations with popular tools like Slack, Microsoft Teams, and Google Workspace.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<div class="container">
    <section class="final-cta text-center" data-aos="zoom-in">
        <div class="row justify-content-center px-4">
            <div class="col-lg-8">
                <h2 class="display-4 fw-bold mb-4">Ready to transform your workforce?</h2>
                <p class="lead mb-5 opacity-75">Join thousands of companies using HRnexa to build better workplaces.</p>
                <div class="d-flex justify-content-center gap-3 flex-column flex-md-row">
                    <a href="<?php echo BASE_URL; ?>public/career.php" class="btn btn-hero-accent btn-lg px-5">Get Started Today</a>
                    <a href="<?php echo BASE_URL; ?>public/contact.php" class="btn btn-outline-light btn-lg px-5 rounded-0 border-2">Contact Sales</a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'layouts/footer.php'; ?>
