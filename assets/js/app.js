const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('open');
    });

    navMenu.querySelectorAll('.has-children > a').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (window.innerWidth <= 900) {
                event.preventDefault();
                link.parentElement.classList.toggle('open');
            }
        });
    });
}

// Hero slider
const slides = document.querySelectorAll('#heroSlider .slide');
let currentSlide = 0;

function showSlide(index) {
    slides.forEach((slide, idx) => {
        slide.classList.toggle('active', idx === index);
    });
}

if (slides.length > 1) {
    setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 5000);
}

// Lightbox for publication images
function openLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img src="${imageSrc}" alt="Publication image">
        </div>
    `;
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    
    lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
        closeLightbox(lightbox);
    });
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox(lightbox);
        }
    });
    
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            closeLightbox(lightbox);
            document.removeEventListener('keydown', escHandler);
        }
    });
}

function closeLightbox(lightbox) {
    lightbox.remove();
    document.body.style.overflow = '';
}

