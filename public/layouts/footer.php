    </main>
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-md-left">
            <div class="row text-md-left">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-primary">HRnexa</h5>
                    <p class="text-secondary small">Empowering businesses with modern HR solutions for a more productive workplace. Built with cutting-edge technology for the next generation of workforce management.</p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Quick Links</h5>
                    <p><a href="<?php echo BASE_URL; ?>public/index.php" class="text-secondary text-decoration-none">Home</a></p>
                    <p><a href="<?php echo BASE_URL; ?>public/about.php" class="text-secondary text-decoration-none">About Us</a></p>
                    <p><a href="<?php echo BASE_URL; ?>public/services.php" class="text-secondary text-decoration-none">Our Services</a></p>
                    <p><a href="<?php echo BASE_URL; ?>public/notice.php" class="text-secondary text-decoration-none">Notice Board</a></p>
                </div>

                <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Support</h5>
                    <p><a href="#" class="text-secondary text-decoration-none">Help Center</a></p>
                    <p><a href="#" class="text-secondary text-decoration-none">Privacy Policy</a></p>
                    <p><a href="#" class="text-secondary text-decoration-none">Terms of Use</a></p>
                    <p><a href="<?php echo BASE_URL; ?>public/contact.php" class="text-secondary text-decoration-none">Contact Support</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold">Contact</h5>
                    <p class="text-secondary small"><i class="fas fa-home mr-3"></i> 123 Tech Avenue, Silicon Valley</p>
                    <p class="text-secondary small"><i class="fas fa-envelope mr-3"></i> support@hrnexa.com</p>
                    <p class="text-secondary small"><i class="fas fa-phone mr-3"></i> +1 (555) 000-0000</p>
                </div>
            </div>

            <hr class="mb-4">

            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p class="small text-secondary text-md-left">© <?php echo date("Y"); ?> All rights reserved by: <strong class="text-primary">HRnexa</strong></p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-right">
                        <ul class="list-unstyled list-inline">
                            <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-secondary" style="font-size: 23px;"><i class="fab fa-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-secondary" style="font-size: 23px;"><i class="fab fa-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-secondary" style="font-size: 23px;"><i class="fab fa-linkedin-in"></i></a></li>
                            <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-secondary" style="font-size: 23px;"><i class="fab fa-github"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JQuery for AJAX (optional, can use fetch) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
    <!-- Custom Scripts -->
    <script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
</body>
</html>
