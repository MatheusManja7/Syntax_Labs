(() => {
    const botoes = document.querySelectorAll('[data-logout]');

    const API_LOGOUT = '../../../public/api/logout.php';
    const LOGIN_URL  = '../auth/login.html';

    botoes.forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            try {
                await fetch(API_LOGOUT, { method: 'POST' });
            } catch {
                // Mesmo sem resposta, leva para o login
            }

            window.location.href = LOGIN_URL;
        });
    });
})();