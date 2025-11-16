//Mobile responsive script
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggleTop');
    const sidebar = document.getElementById('accordionSidebar');
    const wrapper = document.getElementById('wrapper');
    let backdrop = document.querySelector('.sidebar-backdrop');
    // Create backdrop if not exists
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.classList.add('sidebar-backdrop');
        document.body.appendChild(backdrop);
    }
    // Toggle sidebar on button click
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();       
            const isShown = sidebar.classList.contains('show');        
            if (!isShown) {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
            } else {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }
        });
    }
    // Close sidebar when clicking on backdrop
    backdrop.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });
    // Close sidebar when clicking outside (but not on toggle)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const isClickOnSidebar = sidebar.contains(e.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(e.target);        
            if (!isClickOnSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }
        }
    });
    // Close sidebar on window resize if moving from mobile to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });
    // Close sidebar when clicking on nav links (mobile)
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                }, 100);
            }
        });
    });
});