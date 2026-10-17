(() => {
    const API_BASE = '../../../public/api/';

    const token = new URLSearchParams(window.location.search).get('token') || '';
    history.replaceState(null, '', window.location.pathname);

    const $ = (id) => document.getElementById(id);
    const estados = ['st_carregando', 'st_erro', 'st_proposta', 'st_resultado'];
    const BRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    function mostrar(id) {
        estados.forEach((s) => { $(s).hidden = s !== id; });
    }

    function erro(titulo, texto) {
        $('erro_titulo').textContent = titulo;
        $('erro_texto').textContent = texto;
        mostrar('st_erro');
    }

    function dia(ymd) {
        const [a, m, d] = String(ymd || '').split('-');
        return a && m && d ? `${d}/${m}/${a}` : '';
    }

    async function chamar(url, corpo) {
        const r = await fetch(API_BASE + url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(corpo)
        });
        return { status: r.status, dados: await r.json() };
    }

    function resultado(estado) {
        const aceito = estado === 'aceito';
        const icone = $('r_icone');
        icone.textContent = aceito ? '✓' : '✕';
        icone.className = 'prop_icone ' + (aceito ? 'ok' : 'nao');

        $('r_titulo').textContent = aceito ? 'Proposta aceita!' : 'Proposta recusada';
        $('r_texto').textContent = aceito
            ? 'Obrigado pela confiança! A Syntax Labs entrará em contato com você pelo WhatsApp para dar continuidade ao projeto, marcar uma reunião e alinhar os detalhes.'
            : 'Obrigado pelo retorno. Registramos a sua decisão. Se mudar de ideia ou quiser ajustar algo, é só responder o e-mail que recebeu.';
        mostrar('st_resultado');
    }

    function preencher(p) {
        $('p_titulo').textContent = p.titulo || '';
        $('p_para').textContent = `Preparada para ${p.cliente} (${p.empresa})`;
        $('p_valor').textContent = `${BRL.format(p.valor_min)} a ${BRL.format(p.valor_max)}`;
        $('p_prazo').textContent = p.prazo || '';
        $('p_validade').textContent = dia(p.validade);
        $('p_escopo').textContent = p.escopo || '';
        $('p_pagamento').textContent = p.pagamento || '';
        $('p_obs').textContent = p.obs || '';
        $('p_obs_bloco').hidden = !p.obs;
        mostrar('st_proposta');
    }

    /* ---------- Decisão ---------- */
    let escolha = null;

    function pedirConfirmacao(decisao) {
        escolha = decisao;
        $('p_msg').hidden = true;
        $('p_confirma_texto').textContent = decisao === 'aceitar'
            ? 'Confirmar que você aceita esta proposta? A decisão é definitiva e a nossa equipe entrará em contato.'
            : 'Confirmar que você rejeita esta proposta? A decisão é definitiva.';
        $('btn_confirmar').className = 'prop_btn ' + (decisao === 'aceitar' ? 'prop_btn_aceitar' : 'prop_btn_rejeitar');
        $('p_acoes').hidden = true;
        $('p_confirma').hidden = false;
    }

    $('btn_aceitar').addEventListener('click', () => pedirConfirmacao('aceitar'));
    $('btn_rejeitar').addEventListener('click', () => pedirConfirmacao('rejeitar'));

    $('btn_voltar').addEventListener('click', () => {
        escolha = null;
        $('p_confirma').hidden = true;
        $('p_acoes').hidden = false;
    });

    $('btn_confirmar').addEventListener('click', async () => {
        if (!escolha) return;

        const btn = $('btn_confirmar');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        $('p_msg').hidden = true;

        try {
            const { dados } = await chamar('responder_proposta.php', { token, decisao: escolha });

            if (dados.sucesso) {
                return resultado(dados.estado);
            }
            if (dados.estado === 'aceito' || dados.estado === 'rejeitado') {
                return resultado(dados.estado);          // já havia sido respondida
            }
            if (dados.estado === 'expirada') {
                return erro('Proposta expirada', 'O prazo de validade desta proposta terminou. Responda o e-mail para solicitarmos uma nova.');
            }

            $('p_msg').textContent = dados.mensagem || 'Não foi possível registrar. Tente novamente.';
            $('p_msg').hidden = false;
        } catch {
            $('p_msg').textContent = 'Erro de conexão. Tente novamente.';
            $('p_msg').hidden = false;
        }

        btn.disabled = false;
        btn.textContent = 'Confirmar';
    });

    /* ---------- Início ---------- */
    (async () => {
        if (!token) {
            return erro('Link inválido', 'Abra a proposta pelo botão do e-mail que enviamos.');
        }

        try {
            const { status, dados } = await chamar('ver_proposta.php', { token });

            if (status === 429) {
                return erro('Muitas tentativas', 'Aguarde um pouco e abra o link novamente.');
            }
            if (!dados.sucesso) {
                return erro('Link inválido', dados.mensagem || 'Este link não é válido.');
            }

            const p = dados.proposta;
            if (p.estado === 'aceito' || p.estado === 'rejeitado') return resultado(p.estado);
            if (p.estado === 'expirada') {
                return erro('Proposta expirada', 'O prazo de validade desta proposta terminou. Responda o e-mail para solicitarmos uma nova.');
            }

            preencher(p);
        } catch {
            erro('Erro de conexão', 'Não foi possível carregar a proposta. Tente novamente em instantes.');
        }
    })();
})();