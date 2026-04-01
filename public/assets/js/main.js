/* Main JS for HRnexa Company Website */

$(document).ready(function () {
    // Career Apply Button Modal Handling
    $('.apply-btn').on('click', function () {
        const job = $(this).data('job');
        $('#jobTitle').text(job);
        $('#applyModal').modal('show');
    });

    // Contact Form AJAX Submission
    $('#contactForm').on('submit', function (e) {
        e.preventDefault();
        const submitBtn = $('#contactSubmitBtn');
        const responseDiv = $('#contactResponse');

        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span> Sending...');
        submitBtn.prop('disabled', true);

        // Simulate AJAX Request
        setTimeout(() => {
            submitBtn.html('Send Message <i class="fas fa-paper-plane ms-2"></i>');
            submitBtn.prop('disabled', false);

            responseDiv.html('<div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i> Thank you! Your message has been sent successfully.</div>');
            $('#contactForm')[0].reset();
        }, 1500);
    });

    // Career Form AJAX Submission
    $('#applyForm').on('submit', function (e) {
        e.preventDefault();
        const modalBody = $(this).find('.modal-body');
        const originalHtml = modalBody.html();

        modalBody.html('<div class="text-center py-5"><div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div><p>Submitting your application...</p></div>');

        // Simulate AJAX Request
        setTimeout(() => {
            modalBody.html('<div class="text-center py-5 fade-in"><i class="fas fa-check-circle text-primary mb-3" style="font-size: 4rem;"></i><h4 class="fw-bold">Application Sent!</h4><p class="text-muted">Our HR team will review your CV and get back to you soon.</p></div>');

            setTimeout(() => {
                $('#applyModal').modal('hide');
                // Reset form for next time if modal is opened again
                setTimeout(() => {
                    modalBody.html(originalHtml);
                    $('#applyForm')[0].reset();
                }, 500);
            }, 3000);
        }, 1500);
    });

    // Count Up Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const counter = $(entry.target);
                const target = parseInt(counter.attr('data-target'));
                const speed = 50;

                const updateCount = () => {
                    const current = parseInt(counter.text());
                    const increment = Math.ceil(target / speed);

                    if (current < target) {
                        counter.text(current + increment);
                        setTimeout(updateCount, 20);
                    } else {
                        counter.text(target + '+'); // Add plus sign for effect
                    }
                };

                updateCount();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    $('.count-up').each(function () {
        observer.observe(this);
    });

    // Navbar Scroll Effect
    const navbar = $('#mainNav');
    if (navbar.hasClass('navbar-transparent')) {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 50) {
                navbar.addClass('navbar-scrolled navbar-dark').removeClass('navbar-transparent');
            } else {
                navbar.addClass('navbar-transparent navbar-dark').removeClass('navbar-scrolled bg-dark');
            }
        });
    }
});
