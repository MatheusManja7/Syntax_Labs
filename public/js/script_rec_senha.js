const form           = document.getElementById('recSenhaForm');
const formStep       = document.getElementById('formStep');
const successMessage = document.getElementById('successMessage');
const inputEmail     = document.getElementById('email');
const msg            = document.getElementById('rec_msg');
const btn            = document.getElementById('button');

const API_RECUPERAR = '../../../public/api/recuperar_senha.php';

function mostrarErro(texto) {
    msg.textContent = texto;
    msg.hidden = false;
}

function limparErro() {
    msg.textContent = '';
    msg.hidden = true;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    limparErro();

    const email = inputEmail.value.trim();

    if (!email) {
        mostrarErro('Informe o seu e-mail.');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const resposta = await fetch(API_RECUPERAR, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            formStep.style.display = 'none';
            successMessage.classList.add('active');
            return;
        }

        mostrarErro(dados.mensagem || 'Não foi possível enviar o link.');
    } catch (erro) {
        mostrarErro('Erro de conexão com o servidor. Tente novamente.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar link';
    }
});