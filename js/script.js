let navbar=document.querySelector('.menu')

document.querySelector('#menu-btn').onclick = () =>{
    navbar.classList.toggle('active');
    searchform.classList.remove('active');
};

const searchbtn = document.getElementById('search-btn')
const searchform=document.querySelector('.search-form')

// document.querySelector('#search-box').onclick = () =>{
//     searchbar.classList.toggle('active');
// }
searchbtn.addEventListener('click', () => {
    searchform.classList.toggle('active');
    navbar.classList.remove('active');
});

window.onscroll = () => {
    navbar.classList.remove('active');
    searchform.classList.remove('active');
};


const year=document.getElementById('yearly-btn');

year.addEventListener('click', function() {
    document.getElementById('yearly-cards').classList.remove('hidden');
    document.getElementById('monthly-cards').classList.add('hidden');
    document.getElementById('yearly-btn').classList.add('active');
    document.getElementById('monthly-btn').classList.remove('active');
});

const month =document.getElementById('monthly-btn');

month.addEventListener('click', function() {
    document.getElementById('monthly-cards').classList.remove('hidden');
    document.getElementById('yearly-cards').classList.add('hidden');
    document.getElementById('monthly-btn').classList.add('active');
    document.getElementById('yearly-btn').classList.remove('active');
});

