var loginModal = document.getElementById('login-modal');
        var closeBtn = document.getElementById('close-btn');

        document.getElementById('login-btn').addEventListener('click', function () {
            loginModal.style.display = 'block';
        });

        closeBtn.addEventListener('click', function () {
            loginModal.style.display = 'none';
        });
        
        document.getElementById('login-btn').addEventListener('click', function () {
            window.location.href = 'login.html';
        });