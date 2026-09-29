const btnSair = document.getElementById('logout_btn');

const API_LOGOUT   = '../../../public/api/logout.php';
const PAGINA_LOGIN = '../auth/login.html';

if (btnSair) {
    btnSair.addEventListener('click', async (e) => {
        e.preventDefault();

        try {
            await fetch(API_LOGOUT, { method: 'POST' });
        } catch {
            // Mesmo sem resposta, leva para o login; o servidor expira a sessão sozinho
        }

        window.location.href = PAGINA_LOGIN;
    });
}