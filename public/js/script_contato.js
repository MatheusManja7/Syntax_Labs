(() => {
    const form   = document.getElementById('form_contato');
    if (!form) return;

    const msg    = document.getElementById('contato_msg');
    const btn    = form.querySelector('.btn_content');
    const API    = 'public/api/enviar_mensagem.php';
    const textoBtn = btn.textContent.trim();

    function mostrar(texto, sucesso = false) {
        msg.textContent = texto;
        msg.classList.toggle('sucesso', sucesso);
        msg.hidden = false;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msg.hidden = true;

        const dados = {
            nome:     form.nome.value.trim(),
            empresa:  form.empresa.value.trim(),
            email:    form.email.value.trim(),
            telefone: form.telefone.value.trim(),
            assunto:  form.assunto.value.trim(),
            mensagem: form.mensagem.value.trim(),
            site:     form.site.value          // honeypot
        };

        if (!dados.nome || !dados.email || !dados.assunto || !dados.mensagem) {
            return mostrar('Preencha nome, e-mail, assunto e mensagem.');
        }

        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const r = await fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
            const resp = await r.json();

            if (resp.sucesso) {
                form.reset();
                mostrar(resp.mensagem, true);
            } else {
                mostrar(resp.mensagem || 'Não foi possível enviar.');
            }
        } catch {
            mostrar('Erro de conexão. Tente novamente.');
        }

        btn.disabled = false;
        btn.textContent = textoBtn;
    });
})();