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

