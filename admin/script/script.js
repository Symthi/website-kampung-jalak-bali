// Preview gambar sebelum upload
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Hapus preview lama jika ada
                    const oldPreview = document.querySelector('.gambar-preview');
                    if (oldPreview) {
                        oldPreview.remove();
                    }        
                    // Buat preview baru
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'gambar-preview';        
                    // Sisipkan setelah input file
                    fileInput.parentNode.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    }    
});

// Toggle mobile menu
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('show');
        });
    }
});

$(document).ready(function() {
    // Toggle mobile menu
    $('.menu-toggle').click(function() {
        $('.nav-container').toggleClass('active');
    });
    // Close menu on window resize
    $(window).resize(function() {
        if ($(window).width() > 768) {
            $('.nav-container').removeClass('active');
        }
    });
});