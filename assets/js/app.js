const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');

function setMenuOpen(isOpen) {
    if (!navMenu || !navToggle) return;
    navMenu.classList.toggle('open', isOpen);
    navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    navToggle.textContent = isOpen ? '✕' : '☰';
}

if (navToggle && navMenu) {
    navToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        setMenuOpen(!navMenu.classList.contains('open'));
    });

    navMenu.querySelectorAll('.has-children > a').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (window.innerWidth <= 960) {
                event.preventDefault();
                link.parentElement.classList.toggle('open');
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (window.innerWidth > 960 || !navMenu.classList.contains('open')) return;
        if (navMenu.contains(event.target) || navToggle.contains(event.target)) return;
        setMenuOpen(false);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) {
            setMenuOpen(false);
            navMenu.querySelectorAll('.has-children.open').forEach((item) => item.classList.remove('open'));
        }
    });
}

(function initHeroSlider() {
    const root = document.getElementById('heroSlider');
    if (!root) return;

    const slides = Array.from(root.querySelectorAll('.hero-slide'));
    if (!slides.length) return;

    const dotsWrap = document.getElementById('heroDots');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    let index = 0;
    let timer;

    if (dotsWrap && slides.length > 1) {
        slides.forEach((_, i) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goTo(i));
            dotsWrap.appendChild(dot);
        });
    }

    const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('button')) : [];

    function goTo(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach((slide, idx) => slide.classList.toggle('active', idx === index));
        dots.forEach((dot, idx) => dot.classList.toggle('active', idx === index));
        restart();
    }

    function restart() {
        clearInterval(timer);
        if (slides.length > 1) {
            timer = setInterval(() => goTo(index + 1), 5500);
        }
    }

    if (prevBtn) prevBtn.addEventListener('click', () => goTo(index - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goTo(index + 1));
    restart();
})();

const backToTop = document.getElementById('backToTop');
if (backToTop) {
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

(function initTestimonials() {
    const track = document.getElementById('testimonialsTrack');
    if (!track) return;

    const cards = Array.from(track.querySelectorAll('.testimonial-card'));
    if (cards.length <= 3) return;

    let start = 0;
    const visible = () => (window.innerWidth <= 960 ? 1 : 3);

    function render() {
        const count = visible();
        cards.forEach((card, i) => {
            const show = i >= start && i < start + count;
            card.style.display = show ? '' : 'none';
        });
    }

    function shift(dir) {
        const count = visible();
        start = (start + dir + cards.length) % cards.length;
        if (start > cards.length - count) start = 0;
        render();
    }

    const prev = document.getElementById('testimonialPrev');
    const next = document.getElementById('testimonialNext');
    if (prev) prev.addEventListener('click', () => shift(-1));
    if (next) next.addEventListener('click', () => shift(1));
    window.addEventListener('resize', render);
    render();
})();

function openLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img src="${imageSrc}" alt="Gallery image">
        </div>
    `;
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';

    lightbox.querySelector('.lightbox-close').addEventListener('click', () => closeLightbox(lightbox));
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox(lightbox);
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
