(() => {
    const form       = document.getElementById('form_cad_adm');
    const inputNome  = document.getElementById('nome');
    const inputEmail = document.getElementById('email');
    const inputSenha = document.getElementById('senha');
    const inputConf  = document.getElementById('confirma_senha');
    const msg        = document.getElementById('cad_adm_msg');
    const btn        = document.getElementById('button');

    const API_CADASTRAR = '../../../public/api/cadastrar_admin.php';
    const LOGIN_URL     = '../auth/login.html';

    function mostrarMsg(texto, sucesso = false) {
        msg.textContent = texto;
        msg.classList.toggle('sucesso', sucesso);
        msg.hidden = false;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msg.hidden = true;

        const nome        = inputNome.value.trim();
        const email       = inputEmail.value.trim();
        const senha       = inputSenha.value;
        const confirmacao = inputConf.value;

        if (!nome || !email || !senha || !confirmacao) {
            return mostrarMsg('Preencha todos os campos.');
        }
        if (senha !== confirmacao) {
            return mostrarMsg('As senhas não coincidem.');
        }

        btn.disabled = true;
        btn.textContent = 'Cadastrando...';

        try {
            const r = await fetch(API_CADASTRAR, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nome, email, senha, confirmacao })
            });

            if (r.status === 401) {          // sessão expirou ou usuário desativado
                window.location.href = LOGIN_URL;
                return;
            }

            const dados = await r.json();

            if (dados.sucesso) {
                form.reset();
                mostrarMsg(dados.mensagem, true);
            } else {
                mostrarMsg(dados.mensagem || 'Não foi possível cadastrar.');
            }
        } catch {
            mostrarMsg('Erro de conexão com o servidor. Tente novamente.');
        }

        btn.disabled = false;
        btn.textContent = 'Cadastrar';
    });
})();