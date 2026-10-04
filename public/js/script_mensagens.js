(() => {
    const grid     = document.getElementById('msgs_grid');
    const vazio    = document.getElementById('msgs_empty');
    const aviso    = document.getElementById('msgs_aviso');
    const busca    = document.getElementById('msgs_busca');
    const tabs     = document.querySelectorAll('.msgs_tab');

    const cntTodas   = document.getElementById('cnt_todas');
    const cntNao     = document.getElementById('cnt_nao_lidas');
    const cntLidas   = document.getElementById('cnt_lidas');
    const cntArq      = document.getElementById('cnt_arquivadas');
    const btnArquivar = document.getElementById('msg_btn_arquivar');
    const cabNaoLidas = document.getElementById('msgs_nao_lidas');

    const modal      = document.getElementById('msg_modal');
    const mAvatar    = document.getElementById('msg_modal_avatar');
    const mNome      = document.getElementById('msg_modal_nome');
    const mEmail     = document.getElementById('msg_modal_email');
    const mStatus    = document.getElementById('msg_modal_status');
    const mAssunto   = document.getElementById('msg_modal_assunto');
    const mData      = document.getElementById('msg_modal_data');
    const mTexto     = document.getElementById('msg_modal_texto');
    const mResposta  = document.getElementById('msg_resposta');
    const mMsg       = document.getElementById('msg_modal_msg');
    const mExtra = document.getElementById('msg_modal_extra');
    const mRespondida = document.getElementById('msg_modal_respondida');
    const btnEnviar  = document.getElementById('msg_btn_enviar');
    const btnFechar  = document.getElementById('msg_btn_fechar');
    const btnX       = document.getElementById('msg_modal_fechar');
    const btnExcluir = document.getElementById('msg_btn_excluir');

    let mensagens = [];
    let filtro = 'todas';
    let atual  = null;          // mensagem aberta no modal
    let timerAviso;
    let ultimoFoco = null;
    let timerExcluir;

    const API_BASE  = '../../../public/api/';
    const LOGIN_URL = '../auth/login.html';

    async function chamar(url, opcoes) {
        const r = await fetch(API_BASE + url, opcoes);

        if (r.status === 401) {          // sessão expirou
            window.location.href = LOGIN_URL;
            throw new Error('sessao');
        }

        return { status: r.status, dados: await r.json() };
    }

    const post = (url, body) => chamar(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });

    async function carregar() {
        try {
            const { dados } = await chamar('listar_mensagens.php');

            if (!dados.sucesso) {
                mostrarAviso(dados.mensagem || 'Não foi possível carregar as mensagens.');
                return;
            }

            mensagens = dados.mensagens;
            render();
        } catch (e) {
            if (e.message !== 'sessao') mostrarAviso('Erro de conexão com o servidor.');
        }
    }

    /* ---------- Utilidades ---------- */
    function el(tag, classe, texto) {
        const e = document.createElement(tag);
        if (classe) e.className = classe;
        if (texto !== undefined) e.textContent = texto;
        return e;
    }

    function inicial(nome) {
        return (nome.trim().charAt(0) || '?').toUpperCase();
    }

    function formatarData(iso) {
        const d = new Date(iso);
        if (isNaN(d)) return '';
        return d.toLocaleDateString('pt-BR') + ' às ' +
               d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    function mostrarAviso(texto, sucesso = false) {
        clearTimeout(timerAviso);
        aviso.textContent = texto;
        aviso.classList.toggle('sucesso', sucesso);
        aviso.hidden = false;
        timerAviso = setTimeout(() => { aviso.hidden = true; }, 4000);
    }

    /* ---------- Renderização ---------- */
    function montarCard(m) {
        const card = el('button', `msg_card ${m.lida ? 'lida' : 'nao_lida'}`);
        card.type = 'button';
        card.setAttribute('aria-label', `${m.lida ? '' : 'Não lida: '}${m.assunto}, de ${m.nome}`);

        const topo = el('div', 'msg_card_topo');
        const avatar = el('div', 'msg_avatar', inicial(m.nome));
        avatar.setAttribute('aria-hidden', 'true');

        const quem = el('div', 'msg_card_quem');
        quem.append(el('p', 'msg_nome', m.nome), el('p', 'msg_email', m.email));

        topo.append(avatar, quem, el('span', `msg_status ${m.lida ? 'vista' : 'nova'}`, m.lida ? 'Lida' : 'Nova'));

        card.append(
            topo,
            el('h4', 'msg_assunto', m.assunto),
            el('p', 'msg_previa', m.mensagem),
            m.respostas > 0 ? el('p', 'msg_respondida', '✓ Respondida') : '',
            el('p', 'msg_data', formatarData(m.data))
        );

        card.addEventListener('click', () => abrirModal(m, card));
        return card;
    }

    function render() {
        const termo = busca.value.trim().toLowerCase();
        const arquivando = filtro === 'arquivadas';

        const filtradas = mensagens
            .filter((m) => arquivando ? m.arquivada : !m.arquivada)
            .filter((m) => filtro === 'todas' || arquivando || (filtro === 'lidas' ? m.lida : !m.lida))
            .filter((m) => !termo ||
                m.nome.toLowerCase().includes(termo) ||
                m.email.toLowerCase().includes(termo) ||
                m.assunto.toLowerCase().includes(termo))
            .sort((a, b) => new Date(b.data) - new Date(a.data));

        grid.replaceChildren(...filtradas.map(montarCard));

        const vazia = filtradas.length === 0;
        grid.hidden  = vazia;
        vazio.hidden = !vazia;

        const caixa    = mensagens.filter((m) => !m.arquivada);
        const naoLidas = caixa.filter((m) => !m.lida).length;
        cntTodas.textContent    = caixa.length;
        cntNao.textContent      = naoLidas;
        cntLidas.textContent    = caixa.length - naoLidas;
        cntArq.textContent      = mensagens.length - caixa.length;
        cabNaoLidas.textContent = naoLidas;
    }

    /* ---------- Filtros ---------- */
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            filtro = tab.dataset.filtro;
            tabs.forEach((t) => {
                const ativa = t === tab;
                t.classList.toggle('active', ativa);
                t.setAttribute('aria-selected', String(ativa));
            });
            render();
        });
    });

    busca.addEventListener('input', render);

    /* ---------- Modal ---------- */
    function atualizarStatusModal(m) {
        mStatus.textContent = m.lida ? 'Lida' : 'Nova';
        mStatus.className = `msg_status ${m.lida ? 'vista' : 'nova'}`;
    }

    function atualizarRespondida(m) {
        if (m.respostas > 0 && m.respondida_em) {
            mRespondida.textContent = `✓ Respondida em ${formatarData(m.respondida_em)}`;
            mRespondida.hidden = false;
        } else {
            mRespondida.hidden = true;
        }
    }

    function resetarExcluir() {
        clearTimeout(timerExcluir);
        btnExcluir.classList.remove('confirmando');
        btnExcluir.querySelector('span').textContent = 'Excluir';
    }

    function atualizarBotoesArquivo(m) {
        btnArquivar.querySelector('span').textContent = m.arquivada ? 'Desarquivar' : 'Arquivar';
        btnArquivar.querySelector('i').className = m.arquivada ? 'bi bi-inbox' : 'bi bi-archive';
        btnExcluir.hidden = !m.arquivada;
    }

    function abrirModal(m, origem) {
        resetarExcluir();
        atual = m;
        ultimoFoco = origem;

        mAvatar.textContent  = inicial(m.nome);
        mNome.textContent    = m.nome;
        mEmail.textContent   = m.email;
        mAssunto.textContent = m.assunto;
        mData.textContent    = formatarData(m.data);
        const extra = [m.empresa, m.telefone].filter(Boolean).join('  ·  ');
        mExtra.textContent = extra;
        mExtra.hidden = !extra;
        mTexto.textContent   = m.mensagem;
        mResposta.value      = '';
        mMsg.hidden          = true;
        atualizarStatusModal(m);
        atualizarRespondida(m);
        atualizarBotoesArquivo(m);

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        mResposta.focus();

        // Ao abrir uma mensagem não lida, marca como lida
        if (!m.lida) {
            m.lida = true;
            atualizarStatusModal(m);
            render();
            post('marcar_mensagem_lida.php', { id: m.id }).catch(() => {});
        }
    }

    function fecharModal() {
        resetarExcluir();
        modal.hidden = true;
        document.body.style.overflow = '';
        atual = null;
        if (ultimoFoco && document.contains(ultimoFoco)) ultimoFoco.focus();
    }

    btnX.addEventListener('click', fecharModal);
    btnFechar.addEventListener('click', fecharModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) fecharModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.hidden) fecharModal();
    });

    function msgModal(texto, sucesso = false) {
        mMsg.textContent = texto;
        mMsg.classList.toggle('sucesso', sucesso);
        mMsg.hidden = false;
    }

    btnEnviar.addEventListener('click', async () => {
        const resposta = mResposta.value.trim();
        const m = atual;

        if (!m) return;
        if (!resposta) {
            msgModal('Escreva uma resposta antes de enviar.');
            return;
        }

        const textoOriginal = btnEnviar.querySelector('span').textContent;
        btnEnviar.disabled = true;
        btnEnviar.querySelector('span').textContent = 'Enviando...';
        mMsg.hidden = true;

        try {
            const { dados } = await post('responder_mensagem.php', { id: m.id, resposta });

            if (dados.sucesso) {
                m.lida = true;
                m.respostas = (m.respostas || 0) + 1;
                m.respondida_em = dados.respondida_em;
                render();
                fecharModal();
                mostrarAviso('Resposta enviada para ' + m.email + '.', true);
            } else {
                msgModal(dados.mensagem || 'Não foi possível enviar a resposta.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor. Tente novamente.');
        }

        btnEnviar.disabled = false;
        btnEnviar.querySelector('span').textContent = textoOriginal;
    });

    btnArquivar.addEventListener('click', async () => {
        const m = atual;
        if (!m) return;

        const arquivar = !m.arquivada;
        btnArquivar.disabled = true;

        try {
            const { dados } = await post('arquivar_mensagem.php', { id: m.id, arquivada: arquivar });

            if (dados.sucesso) {
                m.arquivada = arquivar;
                fecharModal();
                render();
                mostrarAviso(dados.mensagem, true);
            } else {
                msgModal(dados.mensagem || 'Não foi possível alterar.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor. Tente novamente.');
        }

        btnArquivar.disabled = false;
    });

    btnExcluir.addEventListener('click', async () => {
        const m = atual;
        if (!m) return;

        // 1º clique pede confirmação; 2º clique (em até 4s) exclui
        if (!btnExcluir.classList.contains('confirmando')) {
            btnExcluir.classList.add('confirmando');
            btnExcluir.querySelector('span').textContent = 'Clique para confirmar';
            timerExcluir = setTimeout(resetarExcluir, 4000);
            return;
        }

        resetarExcluir();
        btnExcluir.disabled = true;

        try {
            const { dados } = await post('excluir_mensagem.php', { id: m.id });

            if (dados.sucesso) {
                mensagens = mensagens.filter((x) => x.id !== m.id);
                fecharModal();
                render();
                mostrarAviso('Mensagem excluída.', true);
            } else {
                msgModal(dados.mensagem || 'Não foi possível excluir.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor. Tente novamente.');
        }

        btnExcluir.disabled = false;
    });

    carregar();
})();