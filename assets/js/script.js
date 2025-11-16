console.log("script.js loaded");
document.addEventListener("DOMContentLoaded", () => {
    // Background Slideshow Homepage
    const heroSection = document.querySelector(".hero-section");
    const bgImages = [
        'assets/slideshow/img1.jpg',
        'assets/slideshow/img2.jpg',
        'assets/slideshow/img3.jpg'
    ];
    let bgIndex = 0;
    const changeInterval = 4000;
    function createSlide(url, animationClass) {
        const slide = document.createElement("div");
        slide.classList.add("bg-slider");
        slide.style.backgroundImage = `url('${url}')`;
        if (animationClass) {
            slide.classList.add(animationClass);
        }
        heroSection.appendChild(slide);
        return slide;
    }
    let currentSlideBgHero = createSlide(bgImages[bgIndex]);
    function changeBackgroundSlide() {
        const nextIndex = (bgIndex + 1) % bgImages.length;
        const nextImage = bgImages[nextIndex];
        const newSlide = createSlide(nextImage, "slide-in");
        const oldSlide = currentSlideBgHero;
        oldSlide.classList.remove("slide-in");
        currentSlideBgHero.classList.add("slide-out");
        setTimeout(() => {
            oldSlide.remove();
        }, 1400);
        currentSlideBgHero = newSlide;
        bgIndex = nextIndex;
    }
    setInterval(changeBackgroundSlide, changeInterval);

    // Structure Slider
    const structureSlider = document.getElementById('structureSlider');
    const structureCards = structureSlider.children;
    const dotsContainer = document.getElementById('structureDots');
    let currentSlideStruktur = 0;
    // Create dots
    for (let i = 0; i < structureCards.length; i++) {
        const dot = document.createElement('div');
        dot.className = 'dot';
        if (i === 0) dot.classList.add('active');
        dot.onclick = () => showStructureSlide(i);
        dotsContainer.appendChild(dot);
    }
    function showStructureSlide(index) {
        currentSlideStruktur = index;
        structureSlider.style.transform = `translateX(-${index * 100}%)`;     
        // Update dots
        document.querySelectorAll('.dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }
    // Auto slide struktur
    setInterval(() => {
        currentSlideStruktur = (currentSlideStruktur + 1) % structureCards.length;
        showStructureSlide(currentSlideStruktur);
    }, 5000);

    // Scroll Animation with Intersection Observer
    // Observer for section animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    }; 
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                // Stagger animation for child elements
                const staggerItems = entry.target.querySelectorAll('.stagger-item');
                staggerItems.forEach((item, index) => {
                    item.style.animationDelay = `${index * 0.1}s`;
                });
            }
        });
    }, observerOptions);

    // Observe all sections
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        section.classList.add('section-observed');
        observer.observe(section);
    });

    // Add staggered animation to mission items
    const missionItems = document.querySelectorAll('.mission-item');
    missionItems.forEach((item, index) => {
        item.classList.add('stagger-item');
        item.classList.add(`stagger-delay-${(index % 5) + 1}`);
    });

    // Add staggered animation to wisata cards
    const wisataCards = document.querySelectorAll('.wisata-card');
    wisataCards.forEach((card, index) => {
        card.classList.add('stagger-item');
        card.classList.add(`stagger-delay-${(index % 5) + 1}`);
    });

    // Add staggered animation to gallery items
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach((item, index) => {
        item.classList.add('stagger-item');
        item.classList.add(`stagger-delay-${(index % 5) + 1}`);
    });

    // Add CSS for sliding transition
    const style = document.createElement('style');
    style.textContent = `
        .sliding {
        transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
    `;
    document.head.appendChild(style);

    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero-section');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Initialize animations on page load
    window.addEventListener('load', function() {
        document.body.classList.add('loaded');
        // Add loaded class to images for loading animation
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (img.complete) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', function() {
                    this.classList.add('loaded');
                });
            }
        });
    });
});

// Enhanced Vision Mission Slider with animation
function showVisionMission(index) {
    const slider = document.getElementById('visionMissionSlider');
    const buttons = document.querySelectorAll('.slider-btn');     
    // Add transition class
    slider.classList.add('sliding');   
    setTimeout(() => {
        slider.style.transform = `translateX(-${index * 100}%)`;    
        buttons.forEach((btn, i) => {
            if (i === index) {
                btn.style.background = 'var(--dark-green)';
                btn.style.color = 'var(--white)';
                btn.classList.add('active');
                } else {
                btn.style.background = 'var(--tan)';
                btn.style.color = 'var(--brown)';
                btn.classList.remove('active');
            }
        });
        // Remove transition class after animation
        setTimeout(() => {
            slider.classList.remove('sliding');
        }, 500);
    }, 50);
}

// Vision & Mission Slider
function showVisionMission(index) {
    const slider = document.getElementById('visionMissionSlider');
    slider.style.transform = `translateX(-${index * 100}%)`;               
    const buttons = document.querySelectorAll('.slider-btn');
    buttons.forEach((btn, i) => {
        if (i === index) {
            btn.style.background = 'var(--dark-green)';
            btn.style.color = 'var(--white)';
        } else {
            btn.style.background = 'var(--tan)';
            btn.style.color = 'var(--brown)';
        }
    });
}

// Gallery 
function openGalleryDetail(data){
    const m = document.getElementById('gallery-modal');
    document.getElementById('gm-title').textContent = data.title || '';
    document.getElementById('gm-image').src = data.src || '';
    document.getElementById('gm-image').alt = data.title || '';
    const descEl = document.getElementById('gm-desc');
    descEl.textContent = data.desc ? data.desc.trim() : NO_DESCRIPTION_TEXT;
    document.getElementById('gm-date').textContent = data.date || '';
    m.style.display = 'flex';
}
function closeGalleryDetail(){
    var m = document.getElementById('gallery-modal');
    m.style.display = 'none';
}
document.addEventListener('click', function(e){
    var m = document.getElementById('gallery-modal');
    if(!m || m.style.display === 'none') return;
    if(e.target === m) closeGalleryDetail();
});
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeGalleryDetail();
});

$(document).ready(function() {
    // Image zoom effect
    $('.wj-featured-image').click(function() {
        $(this).toggleClass('zoomed');
    });

    // Smooth scroll to comments
    $('a[href="#comments"]').click(function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top - 100
        }, 500);
    });

    // Auto-resize textarea
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Toggle mobile menu
    $('.menu-toggle').click(function() {
        $('.main-nav').toggleClass('active');
        $(this).find('i').toggleClass('fa-bars fa-times');
    });

    // Close menu on window resize
    $(window).resize(function() {
        if ($(window).width() > 768) {
            $('.main-nav').removeClass('active');
            $('.menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        }
    });
});