const form        = document.getElementById('novaSenhaForm');
const formStep    = document.getElementById('formStep');
const invalidMsg  = document.getElementById('invalidTokenMessage');
const inputSenha  = document.getElementById('password');
const inputConf   = document.getElementById('confirmPassword');
const erroEl      = document.getElementById('errorMessage');
const btn         = document.getElementById('button');
const hints       = document.querySelectorAll('#passwordHints [data-rule]');
const modal       = document.getElementById('modalSucesso');
const contadorEl  = document.getElementById('contador');

const API_VALIDAR   = '../../../public/api/validar_token.php';
const API_REDEFINIR = '../../../public/api/redefinir_senha.php';
const PAGINA_LOGIN  = 'login.html';

const token = new URLSearchParams(window.location.search).get('token') || '';

// Remove o token da barra de endereço e do histórico
history.replaceState(null, '', window.location.pathname);

function mostrarLinkInvalido() {
    formStep.style.display = 'none';
    invalidMsg.classList.add('active');
}

function mostrarErro(texto) { erroEl.textContent = texto; }
function limparErro()       { erroEl.textContent = ''; }

// 1) Ao abrir a página, confere se o token vale
async function checarToken() {
    if (!token) return mostrarLinkInvalido();

    try {
        const r = await fetch(API_VALIDAR, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token })
        });
        const dados = await r.json();
        if (!dados.sucesso) mostrarLinkInvalido();
    } catch {
        mostrarErro('Erro de conexão com o servidor.');
    }
}

// 2) Dicas de senha em tempo real (só visual; o servidor valida de verdade)
const regras = {
    length: (s) => s.length >= 8,
    upper:  (s) => /[A-Z]/.test(s),
    number: (s) => /[0-9]/.test(s)
};

inputSenha.addEventListener('input', () => {
    hints.forEach((el) => {
        el.classList.toggle('valid', regras[el.dataset.rule](inputSenha.value));
    });
});

// 3) Modal de sucesso + redirecionamento
function abrirModalSucesso() {
    modal.hidden = false;
    let segundos = 3;
    contadorEl.textContent = segundos;

    const timer = setInterval(() => {
        segundos--;
        contadorEl.textContent = segundos;
        if (segundos <= 0) {
            clearInterval(timer);
            window.location.href = PAGINA_LOGIN;
        }
    }, 1000);
}

// 4) Envio do formulário
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    limparErro();

    const senha = inputSenha.value;
    const confirmacao = inputConf.value;

    if (!senha || !confirmacao) return mostrarErro('Preencha os dois campos.');
    if (senha !== confirmacao)  return mostrarErro('As senhas não coincidem.');

    btn.disabled = true;
    btn.textContent = 'Salvando...';

    try {
        const r = await fetch(API_REDEFINIR, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token, senha, confirmacao })
        });
        const dados = await r.json();

        if (dados.sucesso) {
            abrirModalSucesso();
            return; // botão continua desabilitado
        }

        if (r.status === 400) return mostrarLinkInvalido(); // token expirou/foi usado

        mostrarErro(dados.mensagem || 'Não foi possível salvar a senha.');
    } catch {
        mostrarErro('Erro de conexão com o servidor. Tente novamente.');
    }

    btn.disabled = false;
    btn.textContent = 'Salvar nova senha';
});

checarToken();