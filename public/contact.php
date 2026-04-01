<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/Config/database.php';
}
$pageTitle = "Contact Us";
$currentPage = "contact";
include 'layouts/header.php';
?>

<!-- Contact Header -->
<section class="py-5 bg-dark text-white text-center" style="padding-top: 120px !important;">
    <div class="container" data-aos="fade-down">
        <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
        <p class="lead text-secondary opacity-75">We'd love to hear from you. Get in touch with our team.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <!-- Contact Wrapper -->
        <div class="contact-wrapper mb-5" data-aos="fade-up">
            <div class="row g-0">
                <!-- Left Info Panel -->
                <div class="col-lg-5 contact-left" data-aos="fade-right" data-aos-delay="200">
                    <h3 class="fw-bold mb-4 display-6">Get in Touch</h3>
                    <p class="mb-5 opacity-75">Have a question or want to work with us? We'd love to hear from you.</p>
                    
                    <div class="d-flex mb-4 align-items-start">
                        <div class="contact-icon-box"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Our Location</h6>
                            <p class="mb-0 opacity-75">123 Tech Avenue, Silicon Valley, CA 94043</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4 align-items-start">
                        <div class="contact-icon-box"><i class="fas fa-phone-alt"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Phone Number</h6>
                            <p class="mb-0 opacity-75">+1 (555) 000-0000 / +1 (555) 111-2222</p>
                        </div>
                    </div>

                    <div class="d-flex mb-5 align-items-start">
                        <div class="contact-icon-box"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Address</h6>
                            <p class="mb-0 opacity-75">support@hrnexa.com</p>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="d-flex">
                            <a href="#" class="social-circle text-decoration-none"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-circle text-decoration-none"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-circle text-decoration-none"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Right Form Panel -->
                <div class="col-lg-7 contact-right" data-aos="fade-left" data-aos-delay="400">
                    <h3 class="fw-bold mb-4 text-dark">Send us a Message</h3>
                    
                    <form id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="contact-form-label">First Name</label>
                                <input type="text" class="contact-form-input" name="firstname" required>
                            </div>
                            <div class="col-md-6">
                                <label class="contact-form-label">Last Name</label>
                                <input type="text" class="contact-form-input" name="lastname" required>
                            </div>
                            <div class="col-12">
                                <label class="contact-form-label">Email Address</label>
                                <input type="email" class="contact-form-input" name="email" required>
                            </div>
                            <div class="col-12">
                                <label class="contact-form-label">Subject</label>
                                <input type="text" class="contact-form-input" name="subject" required>
                            </div>
                            <div class="col-12">
                                <label class="contact-form-label">Your Message</label>
                                <textarea class="contact-form-input" name="message" rows="5" required></textarea>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn-contact-submit" id="contactSubmitBtn">
                                    Send Message <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="contactResponse" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Google Map Section -->
        <h3 class="fw-bold mb-4 ps-2 border-start border-4 border-primary">Our Location</h3>
        <div class="rounded-4 overflow-hidden border shadow-sm" style="height: 400px;" data-aos="fade-up">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3168.628291414436!2d-122.0838510846922!3d37.4220655979808!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808580b3699c5555%3A0x7c4e53b444f4dcc7!2sGoogleplex!5e0!3m2!1sen!2sbd!4v1654321098765!5m2!1sen!2sbd" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php include 'layouts/footer.php'; ?>
