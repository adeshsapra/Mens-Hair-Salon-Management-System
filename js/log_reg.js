document.getElementById('sign-up-link').addEventListener('click', function () {
    document.getElementById('register-form').classList.add('active');
    document.getElementById('login-form').classList.remove('active');
});

document.getElementById('sign-in-link').addEventListener('click', function () {
    document.getElementById('login-form').classList.add('active');
    document.getElementById('register-form').classList.remove('active');
});


