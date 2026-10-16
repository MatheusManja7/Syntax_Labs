(() => {
    const API_BASE  = '../../../public/api/';
    const LOGIN_URL = '../auth/login.html';

    const STATUS_TXT = { novo: 'Novo', editando: 'Editando', enviado: 'Enviado', aceito: 'Aceito', rejeitado: 'Rejeitado' };
    const EDITAVEIS  = ['novo', 'editando', 'enviado'];   // aceito/rejeitado ficam travados

    const grid   = document.getElementById('orc_grid');
    const vazio  = document.getElementById('orc_empty');
    const aviso  = document.getElementById('orc_aviso');
    const busca  = document.getElementById('orc_busca');
    const tabs   = document.querySelectorAll('.orc_tab');
    const cabNovos = document.getElementById('orc_novos');

    const modal    = document.getElementById('orc_modal');
    const mAvatar  = document.getElementById('orc_m_avatar');
    const mTitulo  = document.getElementById('orc_modal_titulo');
    const mEmpresa = document.getElementById('orc_m_empresa');
    const mData    = document.getElementById('orc_m_data');
    const mStatus  = document.getElementById('orc_m_status');
    const mDetalhes = document.getElementById('orc_m_detalhes');
    const form     = document.getElementById('orc_form');
    const fieldset = document.getElementById('orc_fieldset');
    const mMsg     = document.getElementById('orc_modal_msg');
    const btnSalvar  = document.getElementById('orc_btn_salvar');
    const btnEnviar  = document.getElementById('orc_btn_enviar');
    const btnReabrir = document.getElementById('orc_btn_reabrir');
    const btnFechar  = document.getElementById('orc_btn_fechar');
    const btnX       = document.getElementById('orc_modal_x');

    const campos = {
        titulo: document.getElementById('of_titulo'),
        escopo: document.getElementById('of_escopo'),
        prazo: document.getElementById('of_prazo'),
        validade: document.getElementById('of_validade'),
        min: document.getElementById('of_min'),
        max: document.getElementById('of_max'),
        pagto: document.getElementById('of_pagto'),
        obs: document.getElementById('of_obs')
    };

    let orcamentos = [];
    let filtro = 'novo';
    let atual = null;
    let ultimoFoco = null;
    let timerAviso;

    const BRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    /* ---------- Utilidades ---------- */
    async function chamar(url, opcoes) {
        const r = await fetch(API_BASE + url, opcoes);

        if (r.status === 401) {
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
            const { dados } = await chamar('listar_orcamentos.php');

            if (!dados.sucesso) {
                mostrarAviso(dados.mensagem || 'Não foi possível carregar os orçamentos.');
                return;
            }

            orcamentos = dados.orcamentos;
            render();
        } catch (e) {
            if (e.message !== 'sessao') mostrarAviso('Erro de conexão com o servidor.');
        }
    }

    function el(tag, classe, texto) {
        const e = document.createElement(tag);
        if (classe) e.className = classe;
        if (texto !== undefined) e.textContent = texto;
        return e;
    }

    const inicial = (nome) => (nome.trim().charAt(0) || '?').toUpperCase();

    function formatarData(iso) {
        const d = new Date(iso);
        if (isNaN(d)) return '';
        return d.toLocaleDateString('pt-BR') + ' às ' +
               d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    function formatarDia(ymd) {
        const [a, m, d] = String(ymd || '').split('-');
        return a && m && d ? `${d}/${m}/${a}` : '';
    }

    function faixaValor(p) {
        return p && p.valor_min != null && p.valor_max != null
            ? `${BRL.format(p.valor_min)} a ${BRL.format(p.valor_max)}`
            : '';
    }

    function mostrarAviso(texto, sucesso = false) {
        clearTimeout(timerAviso);
        aviso.textContent = texto;
        aviso.classList.toggle('sucesso', sucesso);
        aviso.hidden = false;
        timerAviso = setTimeout(() => { aviso.hidden = true; }, 4000);
    }

    function msgModal(texto, sucesso = false) {
        mMsg.textContent = texto;
        mMsg.classList.toggle('sucesso', sucesso);
        mMsg.hidden = false;
    }

    /* ---------- Cards ---------- */
    function montarCard(o) {
        const card = el('button', 'orc_card');
        card.type = 'button';
        card.dataset.status = o.status;
        card.setAttribute('aria-label', `${STATUS_TXT[o.status]}: orçamento de ${o.nome}, ${o.empresa}`);

        const topo = el('div', 'orc_card_topo');
        const avatar = el('div', 'orc_avatar', inicial(o.nome));
        avatar.setAttribute('aria-hidden', 'true');

        const quem = el('div', 'orc_card_quem');
        quem.append(el('p', 'orc_nome', o.nome), el('p', 'orc_empresa', o.empresa));

        const badge = el('span', 'orc_status', STATUS_TXT[o.status]);
        badge.dataset.status = o.status;
        topo.append(avatar, quem, badge);

        const chips = el('div', 'orc_chips');
        o.tipos.slice(0, 2).forEach((t) => chips.appendChild(el('span', 'orc_chip', t)));
        if (o.tipos.length > 2) chips.appendChild(el('span', 'orc_chip', `+${o.tipos.length - 2}`));

        card.append(topo, chips, el('p', 'orc_previa', o.descricao));

        const valor = faixaValor(o.proposta);
        if (valor && o.status !== 'novo') card.appendChild(el('p', 'orc_valor', valor));

        card.appendChild(el('p', 'orc_data', formatarData(o.data)));
        card.addEventListener('click', () => abrirModal(o, card));
        return card;
    }

    function render() {
        const termo = busca.value.trim().toLowerCase();

        const filtrados = orcamentos
            .filter((o) => o.status === filtro)
            .filter((o) => !termo ||
                o.nome.toLowerCase().includes(termo) ||
                o.empresa.toLowerCase().includes(termo) ||
                o.email.toLowerCase().includes(termo))
            .sort((a, b) => new Date(b.data) - new Date(a.data));

        grid.replaceChildren(...filtrados.map(montarCard));

        const semItens = filtrados.length === 0;
        grid.hidden = semItens;
        vazio.hidden = !semItens;

        Object.keys(STATUS_TXT).forEach((s) => {
            document.getElementById('cnt_' + s).textContent = orcamentos.filter((o) => o.status === s).length;
        });
        cabNovos.textContent = orcamentos.filter((o) => o.status === 'novo').length;
    }

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

    /* ---------- Modal: pedido do cliente ---------- */
    function linhaDetalhe(rotulo, valor, tipo) {
        if (!valor || (Array.isArray(valor) && !valor.length)) return;
        mDetalhes.appendChild(el('dt', '', rotulo));
        const dd = el('dd');

        if (tipo === 'email') {
            const a = el('a', '', valor);
            a.href = 'mailto:' + valor;
            dd.appendChild(a);
        } else if (tipo === 'whatsapp') {
            const digitos = valor.replace(/\D/g, '');
            const a = el('a', '', valor);
            a.href = 'https://wa.me/55' + digitos;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            dd.appendChild(a);
        } else if (Array.isArray(valor)) {
            dd.textContent = valor.join(', ');
        } else {
            dd.textContent = valor;
        }
        mDetalhes.appendChild(dd);
    }

    function preencherDetalhes(o) {
        mDetalhes.replaceChildren();
        linhaDetalhe('E-mail', o.email, 'email');
        linhaDetalhe('WhatsApp', o.telefone, 'whatsapp');
        linhaDetalhe('Tipo de projeto', o.tipos);
        linhaDetalhe('Objetivo', o.objetivos);
        linhaDetalhe('Ideia', o.descricao);
        linhaDetalhe('Já possui algo', o.possui);
        linhaDetalhe('Referências', o.referencias);       
        linhaDetalhe('Investimento', o.faixa);
        linhaDetalhe('Início desejado', o.inicio);
        if (o.anexos && o.anexos.length) {
            mDetalhes.appendChild(el('dt', '', 'Anexos'));
            const dd = el('dd');
            o.anexos.forEach((a) => {
                const link = el('a', '', a.nome);
                link.href = API_BASE + 'baixar_anexo_orcamento.php?id=' + encodeURIComponent(a.id);
                link.target = '_blank';
                link.rel = 'noopener';
                dd.append(link, document.createElement('br'));
            });
            mDetalhes.appendChild(dd);
        }
    }

    /* ---------- Modal: proposta ---------- */
    function preencherForm(o) {
        const p = o.proposta || {};
        campos.titulo.value   = p.titulo || '';
        campos.escopo.value   = p.escopo || '';
        campos.prazo.value    = p.prazo || '';
        campos.validade.value = p.validade || '';
        campos.min.value      = p.valor_min ?? '';
        campos.max.value      = p.valor_max ?? '';
        campos.pagto.value    = p.pagamento || '';
        campos.obs.value      = p.obs || '';
    }

    function lerForm() {
        return {
            titulo: campos.titulo.value.trim(),
            escopo: campos.escopo.value.trim(),
            prazo: campos.prazo.value.trim(),
            validade: campos.validade.value,
            valor_min: campos.min.value === '' ? null : Number(campos.min.value),
            valor_max: campos.max.value === '' ? null : Number(campos.max.value),
            pagamento: campos.pagto.value.trim(),
            obs: campos.obs.value.trim()
        };
    }

    /** Retorna a mensagem de erro, ou '' se estiver tudo certo (usado só no envio). */
    function validarEnvio(p) {
        if (!p.titulo) return 'Informe o título da proposta.';
        if (!p.escopo) return 'Descreva o escopo.';
        if (!p.prazo) return 'Informe o prazo de entrega.';
        if (p.valor_min == null || p.valor_max == null || p.valor_min <= 0) return 'Informe a faixa de valor estimado.';
        if (p.valor_max < p.valor_min) return 'O valor máximo deve ser maior ou igual ao mínimo.';
        if (!p.pagamento) return 'Informe as condições de pagamento.';
        if (!p.validade) return 'Informe a validade da proposta.';
        const hoje = new Date().toISOString().slice(0, 10);
        if (p.validade < hoje) return 'A validade não pode estar no passado.';
        return '';
    }

    function atualizarEstadoModal(o) {
        mStatus.textContent = STATUS_TXT[o.status];
        mStatus.dataset.status = o.status;

        const editavel = EDITAVEIS.includes(o.status);
        fieldset.disabled = !editavel;
        btnSalvar.hidden  = !editavel;
        btnEnviar.hidden  = !editavel;
        btnReabrir.hidden = editavel;
        btnEnviar.querySelector('span').textContent = o.status === 'enviado' ? 'Reenviar proposta' : 'Enviar proposta';
    }

    function abrirModal(o, origem) {
        atual = o;
        ultimoFoco = origem;

        mAvatar.textContent  = inicial(o.nome);
        mTitulo.textContent  = o.nome;
        mEmpresa.textContent = o.empresa;
        mData.textContent    = 'Recebido em ' + formatarData(o.data);
        mMsg.hidden = true;

        preencherDetalhes(o);
        preencherForm(o);

        if (o.status === 'novo') {
            o.status = 'editando';
            render();
            post('marcar_orcamento_editando.php', { id: o.id }).catch(() => {});
        }
        atualizarEstadoModal(o);

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        modal.querySelector('.orc_modal_box').scrollTop = 0;
        btnFechar.focus();
    }

    function fecharModal() {
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
    form.addEventListener('submit', (e) => e.preventDefault());

    btnSalvar.addEventListener('click', async () => {
        if (!atual) return;
        const o = atual;
        const p = lerForm();

        btnSalvar.disabled = true;
        try {
            const { dados } = await post('salvar_rascunho_orcamento.php', { id: o.id, proposta: p });

            if (dados.sucesso) {
                o.proposta = p;
                render();
                msgModal('Rascunho salvo.', true);
            } else {
                msgModal(dados.mensagem || 'Não foi possível salvar.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor.');
        }
        btnSalvar.disabled = false;
    });

    btnEnviar.addEventListener('click', async () => {
        if (!atual) return;
        const o = atual;

        const p = lerForm();
        const erro = validarEnvio(p);
        if (erro) return msgModal(erro);

        const reenvio = o.status === 'enviado';
        const rotulo = btnEnviar.querySelector('span');
        const textoOriginal = rotulo.textContent;

        btnEnviar.disabled = true;
        btnSalvar.disabled = true;
        rotulo.textContent = 'Enviando...';
        mMsg.hidden = true;

        try {
            const { dados } = await post('enviar_proposta_orcamento.php', { id: o.id, proposta: p });

            if (dados.sucesso) {
                o.proposta = p;
                o.status = 'enviado';
                o.enviada_em = dados.enviada_em;
                const email = o.email;

                fecharModal();
                filtro = 'enviado';
                tabs.forEach((t) => {
                    const ativa = t.dataset.filtro === 'enviado';
                    t.classList.toggle('active', ativa);
                    t.setAttribute('aria-selected', String(ativa));
                });
                render();
                mostrarAviso(`${reenvio ? 'Proposta reenviada' : 'Proposta enviada'} para ${email}.`, true);
            } else {
                if (dados.mensagem && dados.mensagem.startsWith('A proposta foi salva')) o.proposta = p;
                msgModal(dados.mensagem || 'Não foi possível enviar a proposta.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor. Tente novamente.');
        }

        btnEnviar.disabled = false;
        btnSalvar.disabled = false;
        rotulo.textContent = textoOriginal;
    });

    btnReabrir.addEventListener('click', async () => {
        if (!atual) return;
        const o = atual;

        btnReabrir.disabled = true;
        try {
            const { dados } = await post('reabrir_orcamento.php', { id: o.id });

            if (dados.sucesso) {
                o.status = 'editando';
                atualizarEstadoModal(o);
                render();
                msgModal(dados.mensagem, true);
            } else {
                msgModal(dados.mensagem || 'Não foi possível reabrir.');
            }
        } catch (e) {
            if (e.message !== 'sessao') msgModal('Erro de conexão com o servidor.');
        }
        btnReabrir.disabled = false;
    });

    carregar();
})();