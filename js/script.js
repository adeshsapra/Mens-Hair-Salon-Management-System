const navbar = document.querySelector('.menu');
const menuBtn = document.querySelector('#menu-btn');
const searchform = document.querySelector('.search-form');

if (menuBtn && navbar) {
    menuBtn.onclick = () => {
        navbar.classList.toggle('active');
        if (searchform) searchform.classList.remove('active');
    };
}

const searchbtn = document.getElementById('search-btn');

if (searchbtn && searchform && navbar) {
    searchbtn.addEventListener('click', () => {
        searchform.classList.toggle('active');
        navbar.classList.remove('active');
    });
}

window.onscroll = () => {
    if (navbar) navbar.classList.remove('active');
    if (searchform) searchform.classList.remove('active');
};


const year = document.getElementById('yearly-btn');
const month = document.getElementById('monthly-btn');
const yearlyCards = document.getElementById('yearly-cards');
const monthlyCards = document.getElementById('monthly-cards');
const pricingHiddenClass = 'membership-pricing-cards--hidden';

if (year && month && yearlyCards && monthlyCards) {
    year.addEventListener('click', function () {
        yearlyCards.classList.remove(pricingHiddenClass);
        monthlyCards.classList.add(pricingHiddenClass);
        year.classList.add('active');
        month.classList.remove('active');
    });

    month.addEventListener('click', function () {
        monthlyCards.classList.remove(pricingHiddenClass);
        yearlyCards.classList.add(pricingHiddenClass);
        month.classList.add('active');
        year.classList.remove('active');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const heroSection = document.querySelector('.home');
    if (!heroSection) return;

    const heroAnimatedElements = heroSection.querySelectorAll('.hero-animate');
    heroAnimatedElements.forEach((element) => {
        const delay = element.getAttribute('data-hero-delay');
        if (delay) {
            element.style.setProperty('--hero-delay', delay);
        }
    });

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const heroTitle = heroSection.querySelector('[data-hero-split]');

    if (!prefersReducedMotion && heroTitle) {
        const originalText = (heroTitle.textContent || '').replace(/\s+/g, ' ').trim();
        if (originalText) {
            heroTitle.setAttribute('aria-label', originalText);
            heroTitle.textContent = '';

            let letterIndex = 0;
            originalText.split(' ').forEach((word) => {
                const wordSpan = document.createElement('span');
                wordSpan.className = 'hero-word';
                wordSpan.setAttribute('aria-hidden', 'true');

                Array.from(word).forEach((letter) => {
                    const letterSpan = document.createElement('span');
                    letterSpan.className = 'hero-letter';
                    letterSpan.textContent = letter;
                    letterSpan.style.setProperty('--hero-index', String(letterIndex));
                    wordSpan.appendChild(letterSpan);
                    letterIndex += 1;
                });

                heroTitle.appendChild(wordSpan);
            });
        }
    }

    heroSection.classList.add('hero-ready');

    const triggerHeroReveal = () => {
        heroSection.classList.remove('hero-inview');
        // Force reflow so the animation can replay when section re-enters viewport.
        void heroSection.offsetWidth;
        heroSection.classList.add('hero-inview');
    };

    if (prefersReducedMotion) {
        heroSection.classList.add('hero-inview');
        return;
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    triggerHeroReveal();
                } else {
                    heroSection.classList.remove('hero-inview');
                }
            });
        }, {
            threshold: 0.45
        });

        observer.observe(heroSection);
    } else {
        triggerHeroReveal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const addRevealSet = (selector, options = {}) => {
        const {
            stagger = 0,
            baseDelay = 0,
            reveal = 'up'
        } = options;

        const elements = document.querySelectorAll(selector);
        elements.forEach((element, index) => {
            element.classList.add('scroll-reveal');
            element.setAttribute('data-reveal', reveal);
            const delay = baseDelay + (index * stagger);
            element.style.setProperty('--reveal-delay', `${delay}ms`);
        });
    };

    // Home page sections (after hero)
    addRevealSet('.home-color h2', { reveal: 'up', baseDelay: 20 });
    addRevealSet('.home-color h6', { reveal: 'up', baseDelay: 80 });
    addRevealSet('.home-color .service-card', { reveal: 'pop', stagger: 90, baseDelay: 40 });
    addRevealSet('.home-color .viewall-btn', { reveal: 'up', baseDelay: 110 });
    addRevealSet('.product-home-main-container h1, .product-home-main-container p', { reveal: 'up', stagger: 70, baseDelay: 30 });
    addRevealSet('.product-home-card', { reveal: 'pop', stagger: 90, baseDelay: 50 });
    addRevealSet('.product-home-main-container .view-product-btn', { reveal: 'up', baseDelay: 110 });
    addRevealSet('.home-about-heading h2, .home-about-heading h6', { reveal: 'up', stagger: 70, baseDelay: 30 });
    addRevealSet('.photo-nav .content > *', { reveal: 'left', stagger: 80, baseDelay: 20 });
    addRevealSet('.photo-nav .video', { reveal: 'right', baseDelay: 80 });
    addRevealSet('.about-skill .about-skill-img', { reveal: 'left', baseDelay: 20 });
    addRevealSet('.about-skill .content > *', { reveal: 'right', stagger: 70, baseDelay: 20 });
    addRevealSet('.contact-main-container .contact-success-banner', { reveal: 'up', baseDelay: 20 });
    addRevealSet('.contact-main-container .contact-form', { reveal: 'left', baseDelay: 10 });
    addRevealSet('.contact-main-container .contact-details', { reveal: 'right', baseDelay: 50 });

    // Shared page hero strip (service / e-shop / membership / appointment)
    addRevealSet('.defualt-section .img-content > *', { reveal: 'up', stagger: 80, baseDelay: 20 });

    // Service page
    addRevealSet('.service-container1 .service-hair .service-hair-img', { reveal: 'left', baseDelay: 20 });
    addRevealSet('.service-container1 .service-hair .content > *', { reveal: 'right', stagger: 70, baseDelay: 20 });
    addRevealSet('.service-container1 .price-list h2', { reveal: 'up', stagger: 60, baseDelay: 40 });
    addRevealSet('.service-container1 .price-list li', { reveal: 'up', stagger: 16, baseDelay: 60 });
    addRevealSet('.service-container2 .service-beard .content > *', { reveal: 'left', stagger: 70, baseDelay: 20 });
    addRevealSet('.service-container2 .service-beard .service-beard-img', { reveal: 'right', baseDelay: 40 });
    addRevealSet('.service-container2 .price-list h2', { reveal: 'up', baseDelay: 40 });
    addRevealSet('.service-container2 .price-list li', { reveal: 'up', stagger: 16, baseDelay: 60 });
    addRevealSet('.service-container3 .service-skin .service-skin-img', { reveal: 'left', baseDelay: 20 });
    addRevealSet('.service-container3 .service-skin .content > *', { reveal: 'right', stagger: 70, baseDelay: 20 });
    addRevealSet('.service-container3 .price-list h2', { reveal: 'up', baseDelay: 40 });
    addRevealSet('.service-container3 .price-list li', { reveal: 'up', stagger: 16, baseDelay: 60 });
    addRevealSet('.service-container4 .service-body .content > *', { reveal: 'left', stagger: 70, baseDelay: 20 });
    addRevealSet('.service-container4 .service-body .service-body-img', { reveal: 'right', baseDelay: 40 });
    addRevealSet('.service-container4 .price-list h2', { reveal: 'up', stagger: 60, baseDelay: 40 });
    addRevealSet('.service-container4 .price-list li', { reveal: 'up', stagger: 16, baseDelay: 60 });

    // E-shop page
    addRevealSet('.product-main-container h1, .product-main-container > p', { reveal: 'up', stagger: 70, baseDelay: 30 });
    addRevealSet('.product-main-container .product-card', { reveal: 'pop', stagger: 45, baseDelay: 40 });

    // Membership page
    addRevealSet('.membership-pricing-title, .membership-pricing-lead', { reveal: 'up', stagger: 70, baseDelay: 20 });
    addRevealSet('.membership-billing-toggle .membership-toggle-btn', { reveal: 'pop', stagger: 70, baseDelay: 40 });
    addRevealSet('.membership-pricing-cards .membership-card', { reveal: 'pop', stagger: 90, baseDelay: 60 });

    // Appointment page
    addRevealSet('.booking-aside > *', { reveal: 'left', stagger: 70, baseDelay: 20 });
    addRevealSet('.booking-card', { reveal: 'right', baseDelay: 50 });
    addRevealSet('.booking-card__head > *', { reveal: 'up', stagger: 70, baseDelay: 30 });
    addRevealSet('.booking-form .booking-field, .booking-form .booking-field-row, .booking-form .booking-form-actions, .booking-alert', { reveal: 'up', stagger: 60, baseDelay: 40 });

    // Shared schedule strip
    addRevealSet('.shedule-container .shedule-panel > *', { reveal: 'up', stagger: 70, baseDelay: 10 });

    const revealElements = document.querySelectorAll('.scroll-reveal');
    if (!revealElements.length) return;

    if (prefersReducedMotion) {
        revealElements.forEach((element) => element.classList.add('revealed'));
        return;
    }

    document.body.classList.add('scroll-ready');

    if (!('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('revealed');
            currentObserver.unobserve(entry.target);
        });
    }, {
        threshold: 0.16,
        rootMargin: '0px 0px -10% 0px'
    });

    revealElements.forEach((element) => observer.observe(element));
});

