<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "Notice Board";
$currentPage = "notice";
include 'layouts/header.php';
?>

<!-- Notice Hero Section -->
<section class="page-hero notice-hero-bg d-flex align-items-center">
    <div class="container position-relative z-1 text-center">
        <span class="d-inline-block py-1 px-3 rounded-pill bg-white bg-opacity-10 text-white border border-light border-opacity-25 small mb-4" data-aos="fade-down">
            Latest Updates
        </span>
        <h1 class="display-3 fw-bold mb-4 font-serif" data-aos="fade-up" data-aos-delay="100">
            Notice <span style="color: var(--hero-accent);">Board</span>
        </h1>
        <p class="lead mb-0 opacity-75 mx-auto" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="200">
            Stay informed with the latest updates, improved policies, and upcoming events at HRnexa.
        </p>
    </div>

    <!-- Wave Divider -->
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>



<?php
// Fetch active notices
$notice_query = "SELECT * FROM notices WHERE status = 'active' ORDER BY created_at DESC";
$notice_result = $conn->query($notice_query);
?>

<!-- Notices List -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($notice_result && $notice_result->num_rows > 0): ?>
                    <?php 
                    $delay = 0;
                    while($notice = $notice_result->fetch_assoc()): 
                        // Map categories to styles
                        $badge_class = 'bg-info';
                        $card_style = '';
                        $category_name = 'General Update';

                        if ($notice['category'] == 'important') {
                            $badge_class = 'bg-danger';
                            $category_name = 'Important';
                        } elseif ($notice['category'] == 'holiday') {
                            $badge_class = 'bg-warning text-dark';
                            $card_style = 'border-left-color: #ffc107 !important;';
                            $category_name = 'Holiday Notice';
                        }
                    ?>
                        <!-- Dynamic Notice Card -->
                        <div class="card notice-card shadow-sm mb-4 border-0" style="<?php echo $card_style; ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2"><?php echo $category_name; ?></span>
                                    <span class="text-muted small"><i class="far fa-calendar-alt me-2"></i> <?php echo date('M d, Y', strtotime($notice['created_at'])); ?></span>
                                </div>
                                <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($notice['title']); ?></h4>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">
                                        <strong><?php echo htmlspecialchars($notice['footer_label'] ?? 'Posted'); ?>:</strong> 
                                        <?php echo htmlspecialchars($notice['footer_value'] ?? 'Management'); ?>
                                    </span>
                                    <?php if (!empty($notice['button_text'])): ?>
                                        <a href="<?php echo htmlspecialchars($notice['button_link'] ?? '#'); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <?php echo htmlspecialchars($notice['button_text']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $delay += 100;
                    endwhile; 
                else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bullhorn display-1 text-light mb-4"></i>
                        <h4 class="text-muted">No notices at the moment.</h4>
                        <p class="text-muted small">Please check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'layouts/footer.php'; ?>
