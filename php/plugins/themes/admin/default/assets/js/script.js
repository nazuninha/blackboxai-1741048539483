document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const mainHeader = document.querySelector('.main-header');

    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        mainHeader.classList.toggle('expanded');

        // Store sidebar state in localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        mainHeader.classList.add('expanded');
    }

    // Handle mobile sidebar
    const mobileBreakpoint = 768;
    
    function handleResize() {
        if (window.innerWidth <= mobileBreakpoint) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            mainHeader.classList.remove('expanded');
            
            // Add mobile-specific classes
            sidebar.classList.add('mobile');
            document.body.classList.add('mobile-view');
        } else {
            // Remove mobile-specific classes
            sidebar.classList.remove('mobile');
            document.body.classList.remove('mobile-view');
            
            // Restore desktop sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                mainHeader.classList.add('expanded');
            }
        }
    }

    // Initial check and event listener for window resize
    handleResize();
    window.addEventListener('resize', handleResize);

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= mobileBreakpoint) {
            const isClickInside = sidebar.contains(event.target) || 
                                sidebarToggle.contains(event.target);
            
            if (!isClickInside && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Add smooth transitions for theme changes
    document.documentElement.classList.add('transitions-enabled');
});

// Add smooth scrolling to all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add ripple effect to buttons
function createRipple(event) {
    const button = event.currentTarget;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    
    const diameter = Math.max(rect.width, rect.height);
    const radius = diameter / 2;
    
    ripple.style.width = ripple.style.height = `${diameter}px`;
    ripple.style.left = `${event.clientX - rect.left - radius}px`;
    ripple.style.top = `${event.clientY - rect.top - radius}px`;
    ripple.classList.add('ripple');
    
    const rippleContainer = button.getElementsByClassName('ripple-container')[0] || button;
    rippleContainer.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

document.querySelectorAll('.btn-primary, .btn-secondary').forEach(button => {
    button.addEventListener('click', createRipple);
});

// Add fade-in animation to elements when they become visible
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.stat-card, .card').forEach(element => {
    observer.observe(element);
});