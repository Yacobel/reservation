// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerMenu = document.querySelector('.hamburger-icon');
    const nav = document.querySelector('.nav');
    
    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', function() {
            this.classList.toggle('open');
            nav.classList.toggle('open');
            
            // Prevent scrolling when menu is open
            document.body.classList.toggle('no-scroll');
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInsideNav = nav.contains(event.target);
        const isClickInsideHamburger = hamburgerMenu.contains(event.target);
        
        if (!isClickInsideNav && !isClickInsideHamburger && nav.classList.contains('open')) {
            hamburgerMenu.classList.remove('open');
            nav.classList.remove('open');
            document.body.classList.remove('no-scroll');
        }
    });
    
    // Close menu when clicking on a nav link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburgerMenu.classList.remove('open');
            nav.classList.remove('open');
            document.body.classList.remove('no-scroll');
        });
    });
});
