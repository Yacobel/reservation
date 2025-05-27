        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Navigation Toggle
        const hamburgerMenu = document.querySelector('.hamburger-icon');
        const dashboardNav = document.getElementById('dashboardNav');
        const menuOverlay = document.getElementById('menuOverlay');
        
        if (hamburgerMenu) {
            hamburgerMenu.addEventListener('click', function() {
                this.classList.toggle('open');
                dashboardNav.classList.toggle('open');
                menuOverlay.classList.toggle('open');
                document.body.classList.toggle('no-scroll');
            });
        }
        
        // Close menu when clicking on overlay
        if (menuOverlay) {
            menuOverlay.addEventListener('click', function() {
                hamburgerMenu.classList.remove('open');
                dashboardNav.classList.remove('open');
                menuOverlay.classList.remove('open');
                document.body.classList.remove('no-scroll');
            });
        }
        
        // Close menu when clicking on a nav link
        const navLinks = document.querySelectorAll('.dashboard-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (hamburgerMenu && dashboardNav) {
                    hamburgerMenu.classList.remove('open');
                    dashboardNav.classList.remove('open');
                    if (menuOverlay) {
                        menuOverlay.classList.remove('open');
                    }
                    document.body.classList.remove('no-scroll');
                }
            });
        });
        
        // Close menu when clicking on mobile user links
        const mobileUserLinks = document.querySelectorAll('.mobile-user-link');
        mobileUserLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (hamburgerMenu && dashboardNav) {
                    hamburgerMenu.classList.remove('open');
                    dashboardNav.classList.remove('open');
                    if (menuOverlay) {
                        menuOverlay.classList.remove('open');
                    }
                    document.body.classList.remove('no-scroll');
                }
            });
        });
        
        document.addEventListener('click', function(event) {
            const userDropdowns = document.querySelectorAll('.user-dropdown');
            userDropdowns.forEach(dropdown => {
                if (!event.target.closest('.dashboard-user') && dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            });
        });
        
        const alertCloseButtons = document.querySelectorAll('.alert .close-btn');
        alertCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.parentElement;
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        });
        
        const userAvatars = document.querySelectorAll('.user-avatar');
        userAvatars.forEach(avatar => {
            avatar.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.nextElementSibling.querySelector('.user-dropdown');
                dropdown.classList.toggle('active');
            });
        });
    });
    </script>
    
    <?php if (isset($extra_scripts)): ?>
        <?php echo $extra_scripts; ?>
    <?php endif; ?>
</body>
</html>
