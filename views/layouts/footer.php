        </div> <!-- End Dashboard Content Wrapper -->
    </div> <!-- End Main Content -->

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Close sidebar on mobile after clicking link
        if (window.innerWidth <= 768 && sidebar) {
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.addEventListener('click', () => {
                   sidebar.classList.remove('active'); 
                });
            });
        }

        // Auto-dismiss alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>
</html>