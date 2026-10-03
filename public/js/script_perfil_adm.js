(() => {
    const API_BASE  = '../../../public/api/';
    const LOGIN_URL = '../auth/login.html';

    function configurar({ formId, msgId, url, textoBotao, textoEnviando, campos, aoSucesso }) {
        const form = document.getElementById(formId);
        const msg  = document.getElementById(msgId);
        const btn  = form.querySelector('.form_button');

        function mostrar(texto, sucesso = false) {
            msg.textContent = texto;
            msg.classList.toggle('sucesso', sucesso);
            msg.hidden = false;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            msg.hidden = true;

            const corpo = {};
            for (const [chave, id] of Object.entries(campos)) {
                corpo[chave] = document.getElementById(id).value;
            }

            btn.disabled = true;
            btn.textContent = textoEnviando;

            try {
                const r = await fetch(API_BASE + url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(corpo)
                });

                if (r.status === 401) {          // sessão expirou
                    window.location.href = LOGIN_URL;
                    return;
                }

                const dados = await r.json();

                if (dados.sucesso) {
                    mostrar(dados.mensagem, true);
                    if (aoSucesso) aoSucesso(form, dados);
                } else {
                    mostrar(dados.mensagem || 'Não foi possível salvar.');
                }
            } catch {
                mostrar('Erro de conexão com o servidor. Tente novamente.');
            }

            btn.disabled = false;
            btn.textContent = textoBotao;
        });
    }

    configurar({
        formId: 'form_perfil_dados',
        msgId: 'msg_dados',
        url: 'atualizar_perfil.php',
        textoBotao: 'Salvar alterações',
        textoEnviando: 'Salvando...',
        campos: { nome: 'nome' }
    });

    configurar({
        formId: 'form_perfil_senha',
        msgId: 'msg_senha',
        url: 'alterar_senha.php',
        textoBotao: 'Alterar senha',
        textoEnviando: 'Alterando...',
        campos: {
            senha_atual: 'senha_atual',
            senha_nova: 'senha_nova',
            senha_confirma: 'senha_confirma'
        },
        aoSucesso: (form) => form.reset()
    });
})();