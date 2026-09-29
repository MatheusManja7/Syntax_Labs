const form   = document.getElementById('form_login');
const inputEmail = document.getElementById('username');
const inputSenha = document.getElementById('password');
const msg    = document.getElementById('login_msg');
const btn    = document.getElementById('button');

const API_LOGIN   = '../../../public/api/login.php';
const PAGINA_ADMIN = '../admin/home_adm.php';

function mostrarErro(texto) {
    msg.textContent = texto;
    msg.hidden = false;
}

function limparErro() {
    msg.textContent = '';
    msg.hidden = true;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault(); // não recarrega a página
    limparErro();

    const email = inputEmail.value.trim();
    const senha = inputSenha.value;

    if (!email || !senha) {
        mostrarErro('Preencha e-mail e senha.');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Entrando...';

    try {
        const resposta = await fetch(API_LOGIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha })
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            window.location.href = PAGINA_ADMIN;
            return;
        }

        mostrarErro(dados.mensagem || 'Não foi possível entrar.');
        inputSenha.value = '';
        inputSenha.focus();
    } catch (erro) {
        mostrarErro('Erro de conexão com o servidor. Tente novamente.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Entrar';
    }
});