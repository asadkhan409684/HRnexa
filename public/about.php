<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "About Us";
$currentPage = "about";
include 'layouts/header.php';

// Fetch dynamic team members
$team_query = "SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, name ASC";
$team_result = $conn->query($team_query);

?>

<!-- Corporate Hero Section -->
<section class="about-hero d-flex align-items-center">
    <div class="container position-relative z-1 text-center">
        <span class="d-inline-block py-1 px-3 rounded-pill bg-white bg-opacity-10 text-white border border-light border-opacity-25 small mb-4" data-aos="fade-down">
            Since 2024
        </span>
        <h1 class="display-3 fw-bold mb-4 font-serif" data-aos="fade-up" data-aos-delay="100">
            Empowering the <span style="color: var(--hero-accent);">Future of Work</span>
        </h1>
        <p class="lead mb-0 opacity-75 mx-auto" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="200">
            HRnexa is redefining how organizations manage their most valuable asset – their people. We combine technology with empathy to create seamless HR experiences.
        </p>
    </div>

    <!-- Wave Divider -->
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Stats Bar (Similar to Home) -->
<section class="py-5 bg-white position-relative" style="margin-top: -50px; z-index: 10;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0 text-center">
                            <div class="col-md-4 p-4 border-end">
                                <h2 class="display-4 fw-bold text-primary mb-0 count-up" data-target="500">0</h2>
                                <span class="text-muted fw-semibold">Global Clients</span>
                            </div>
                            <div class="col-md-4 p-4 border-end">
                                <h2 class="display-4 fw-bold text-primary mb-0 count-up" data-target="15">0</h2>
                                <span class="text-muted fw-semibold">Awards Won</span>
                            </div>
                            <div class="col-md-4 p-4">
                                <h2 class="display-4 fw-bold text-primary mb-0 count-up" data-target="99">0</h2>
                                <span class="text-muted fw-semibold">% Customer Satisfaction</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-5 bg-light">
    <div class="container py-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <h5 class="text-primary fw-bold text-uppercase mb-3">Our Purpose</h5>
                <h2 class="fw-bold mb-4 display-6 text-dark">We're on a mission to simplify HR complexities.</h2>
                <p class="text-muted mb-4">
                    In today's fast-paced corporate world, administrative burdens shouldn't hold back innovation. HRnexa provides the tools you need to focus on what truly matters—your culture and your people.
                </p>
                <ul class="list-unstyled text-muted">
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Streamlined Workflow Automation</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Data-Driven Decision Making</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Employee-Centric Design</li>
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="mission-vision-card">
                            <div class="mb-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 60px; height: 60px;">
                                    <i class="fas fa-bullseye fs-3"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-3">Our Mission</h4>
                            <p class="text-muted mb-0">To provide a comprehensive and user-friendly HR management ecosystem that drives efficiency, transparency, and employee satisfaction across all business sectors.</p>
                        </div>
                    </div>
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="mission-vision-card">
                            <div class="mb-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle" style="width: 60px; height: 60px;">
                                    <i class="fas fa-eye fs-3"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-3">Our Vision</h4>
                            <p class="text-muted mb-0">To be the world's most trusted partner in workforce management, constantly innovating to meet the evolving needs of the global professional landscape.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="py-5 bg-white">
    <div class="container py-lg-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h5 class="text-primary fw-bold text-uppercase">Our Core Values</h5>
            <h2 class="fw-bold display-6">What Drives Us Forward</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Innovation</h4>
                    <p class="text-muted small">We constantly challenge the status quo, seeking new and better ways to solve complex HR problems through technology.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Integrity</h4>
                    <p class="text-muted small">Trust is the foundation of our business. We handle your data and relationships with the highest security and ethical standards.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">People First</h4>
                    <p class="text-muted small">Technology is our tool, but people are our focus. Every feature we build is designed to improve the human experience at work.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light overflow-hidden">
    <div class="container py-lg-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase small ls-wide mb-2 d-block">The Experts</span>
            <h2 class="fw-bold display-5 font-serif">Meet the Minds Behind <span class="text-primary">HRnexa</span></h2>
            <div class="mx-auto bg-primary rounded-pill mt-3" style="width: 60px; height: 4px;"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <?php 
            $count = 0;
            if ($team_result && $team_result->num_rows > 0): 
                while($member = $team_result->fetch_assoc()): 
                    $delay = ($count % 3) * 100;
            ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="team-card-premium">
                        <div class="team-img-wrapper">
                            <?php if (!empty($member['image_path'])): ?>
                                <img src="../<?php echo $member['image_path']; ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="img-fluid">
                            <?php else: ?>
                                <div class="team-img-placeholder d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary" style="height: 380px;">
                                    <i class="fas fa-user-circle display-1 opacity-25"></i>
                                </div>
                            <?php endif; ?>
                            <div class="team-overlay-glass">
                                <div class="social-links-glass">
                                    <?php if ($member['linkedin']): ?>
                                        <a href="<?php echo htmlspecialchars($member['linkedin']); ?>"><i class="fab fa-linkedin-in"></i></a>
                                    <?php endif; ?>
                                    <?php if ($member['twitter']): ?>
                                        <a href="<?php echo htmlspecialchars($member['twitter']); ?>"><i class="fab fa-twitter"></i></a>
                                    <?php endif; ?>
                                    <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info-premium text-center">
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="team-role mb-0 text-uppercase"><?php echo htmlspecialchars($member['designation']); ?></p>
                        </div>
                    </div>
                </div>
            <?php 
                $count++;
                endwhile; 
            else:
            ?>
                <p class="text-center text-muted">Management team information will be updated soon.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.ls-wide { letter-spacing: 2px; }
.text-primary-gradient {
    background: linear-gradient(135deg, var(--bs-primary), #6a11cb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Premium Team Card Styles */
.team-card-premium {
    position: relative;
    background: #fff;
    border-radius: 24px;
    padding: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    transition: 0.3s ease;
    height: 100%;
    border: 1px solid rgba(0,0,0,0.05);
}

.team-card-premium:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.team-img-wrapper {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    aspect-ratio: 4/5;
}

.team-img-wrapper img, .team-img-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.7s ease;
}

.team-card-premium:hover .team-img-wrapper img {
    transform: scale(1.05);
}

.team-overlay-glass {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 30px;
    opacity: 0;
    transition: all 0.4s ease;
}

.team-card-premium:hover .team-overlay-glass {
    opacity: 1;
}

.social-links-glass {
    display: flex;
    gap: 15px;
    transform: translateY(20px);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.team-card-premium:hover .social-links-glass {
    transform: translateY(0);
}

.social-links-glass a {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links-glass a:hover {
    background: #fff;
    color: var(--bs-primary);
    transform: scale(1.1);
}

.team-info-premium {
    padding: 24px;
}

.team-info-premium h4 {
    color: #1a1a1a;
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 4px;
}

.team-role {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}
</style>

<!-- Final CTA Section (Shared) -->
<section class="container pb-5">
    <div class="final-cta text-center" data-aos="zoom-in">
        <div class="position-relative z-1">
            <h2 class="display-5 fw-bold mb-4">Ready to Join Our Team?</h2>
            <p class="lead mb-5 opacity-75">We are always looking for talented individuals to help us shape the future of HR technology.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary">Contact Us</a>
                <a href="#" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">View Careers</a>
            </div>
        </div>
    </div>
</section>

<?php include 'layouts/footer.php'; ?>
