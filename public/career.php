<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "Careers";
$currentPage = "career";
include 'layouts/header.php';

// Fetch Active Job Postings (requisitions with 'open' status)
$postings_query = "SELECT jr.*, d.name as department_name 
                  FROM job_requisitions jr
                  JOIN departments d ON jr.department_id = d.id
                  WHERE jr.status = 'open'
                  ORDER BY jr.created_at DESC";
$postings_result = $conn->query($postings_query);
?>

<!-- Career Hero Section -->
<section class="page-hero career-hero-bg d-flex align-items-center">
    <div class="container position-relative z-1 text-center">
        <span class="d-inline-block py-1 px-3 rounded-pill bg-white bg-opacity-10 text-white border border-light border-opacity-25 small mb-4" data-aos="fade-down">
            Join Our Team
        </span>
        <h1 class="display-3 fw-bold mb-4 font-serif" data-aos="fade-up" data-aos-delay="100">
            Build the <span style="color: var(--hero-accent);">Future</span> With Us
        </h1>
        <p class="lead mb-0 opacity-75 mx-auto" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="200">
            We are looking for passionate individuals who want to redefine HR technology. Your journey starts here.
        </p>
    </div>

    <!-- Wave Divider -->
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Job Listings -->
<section id="open-positions" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-1">Current Openings</h2>
            <p class="text-muted">Find your place in our growing team.</p>
        </div>

        <div class="row g-4">
            <?php if ($postings_result && $postings_result->num_rows > 0): ?>
                <?php 
                $delay = 0;
                while($job = $postings_result->fetch_assoc()): 
                ?>
                <div class="col-12" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card job-card p-4 shadow-sm border-0">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($job['title']); ?></h4>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-building me-2"></i> <?php echo htmlspecialchars($job['department_name']); ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-map-marker-alt me-2"></i> Office / Dhaka, BD
                                </p>
                            </div>
                            <div class="col-md-3 py-3 py-md-0">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Full-time</span>
                                <?php if (!empty($job['experience'])): ?>
                                    <span class="text-muted small ms-2"><i class="fas fa-briefcase me-1"></i> <?php echo htmlspecialchars($job['experience']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="apply-job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary rounded-pill px-4">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $delay += 100;
                endwhile; 
                ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/searching.svg" alt="No Jobs" style="width: 200px; opacity: 0.5;" class="mb-4">
                    <h4 class="text-muted">No Open Positions Currently</h4>
                    <p class="text-muted">Please check back later or subscribe to our newsletter for updates.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>



<?php include 'layouts/footer.php'; ?>

<script>
    // Handle Apply Now button clicks
    document.querySelectorAll('.apply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const jobTitle = this.getAttribute('data-job');
            window.location.href = 'apply-job.php?job=' + encodeURIComponent(jobTitle);
        });
    });
</script>
