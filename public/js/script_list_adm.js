const API_BASE     = '../../../public/api/';
const LOGIN_URL = '../auth/login.html';

const corpo     = document.getElementById('list_body');
const contador  = document.getElementById('list_count');
const busca     = document.getElementById('list_search');
const msgEl     = document.getElementById('list_msg');
const vazioEl   = document.getElementById('list_empty');
const tabelaEl  = document.getElementById('table_wrap');

const modal     = document.getElementById('modal_confirmar');
const modalTit  = document.getElementById('modal_titulo');
const modalTxt  = document.getElementById('modal_texto');
const modalOk   = document.getElementById('modal_ok');
const modalCan  = document.getElementById('modal_cancelar');

let admins = [];
let timerMsg;

/* ---------- Utilidades ---------- */

function mostrarMsg(texto, sucesso = false) {
    clearTimeout(timerMsg);
    msgEl.textContent = texto;
    msgEl.classList.toggle('sucesso', sucesso);
    msgEl.hidden = false;
    timerMsg = setTimeout(() => { msgEl.hidden = true; }, 4000);
}

function formatarData(valor) {
    const [ano, mes, dia] = String(valor).slice(0, 10).split('-');
    return dia && mes && ano ? `${dia}/${mes}/${ano}` : '';
}

function el(tag, classe, texto) {
    const e = document.createElement(tag);
    if (classe) e.className = classe;
    if (texto !== undefined) e.textContent = texto;
    return e;
}

async function chamar(url, opcoes) {
    const r = await fetch(API_BASE + url, opcoes);

    if (r.status === 401) {          // sessão expirou ou usuário desativado
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

/* ---------- Modal de confirmação ---------- */

function confirmar({ titulo, texto, botao, perigo = false }) {
    return new Promise((resolve) => {
        modalTit.textContent = titulo;
        modalTxt.textContent = texto;
        modalOk.textContent  = botao;
        modalOk.classList.toggle('perigo', perigo);
        modal.hidden = false;
        modalCan.focus();

        const fechar = (resultado) => {
            modal.hidden = true;
            modalOk.removeEventListener('click', aoConfirmar);
            modalCan.removeEventListener('click', aoCancelar);
            modal.removeEventListener('click', aoClicarFora);
            document.removeEventListener('keydown', aoTeclar);
            resolve(resultado);
        };

        const aoConfirmar  = () => fechar(true);
        const aoCancelar   = () => fechar(false);
        const aoClicarFora = (e) => { if (e.target === modal) fechar(false); };
        const aoTeclar     = (e) => { if (e.key === 'Escape') fechar(false); };

        modalOk.addEventListener('click', aoConfirmar);
        modalCan.addEventListener('click', aoCancelar);
        modal.addEventListener('click', aoClicarFora);
        document.addEventListener('keydown', aoTeclar);
    });
}

/* ---------- Renderização ---------- */

function criarBotao(classe, titulo, icone, aoClicar) {
    const b = el('button', `adm_btn ${classe}`);
    b.type = 'button';
    b.title = titulo;
    b.setAttribute('aria-label', titulo);
    const i = el('i', `bi ${icone}`);
    i.setAttribute('aria-hidden', 'true');
    b.appendChild(i);
    b.addEventListener('click', aoClicar);
    return b;
}

function montarLinha(a) {
    const tr = document.createElement('tr');
    if (!a.ativo) tr.classList.add('inativo');

    // Administrador
    const tdNome = document.createElement('td');
    tdNome.dataset.label = 'Administrador';
    const user   = el('div', 'adm_user');
    const avatar = el('div', 'adm_avatar', (a.nome.trim().charAt(0) || '?').toUpperCase());
    avatar.setAttribute('aria-hidden', 'true');
    const nome = el('span', 'adm_nome', a.nome);
    user.append(avatar, nome);
    if (a.voce) user.appendChild(el('span', 'adm_voce', '(você)'));
    tdNome.appendChild(user);

    // E-mail
    const tdEmail = document.createElement('td');
    tdEmail.dataset.label = 'E-mail';
    tdEmail.appendChild(el('span', 'adm_email', a.email));

    // Status
    const tdStatus = document.createElement('td');
    tdStatus.dataset.label = 'Status';
    tdStatus.appendChild(el('span', 'adm_status', a.ativo ? 'Ativo' : 'Inativo'));

    // Criado em
    const tdData = document.createElement('td');
    tdData.dataset.label = 'Criado em';
    tdData.appendChild(el('span', 'adm_data', formatarData(a.criado_em)));

    // Ações (o próprio usuário não tem botões)
    const tdAcoes = document.createElement('td');
    tdAcoes.dataset.label = 'Ações';
    const acoes = el('div', 'adm_actions');

    if (!a.voce) {
        acoes.appendChild(criarBotao(
            'adm_btn_toggle',
            a.ativo ? 'Desativar' : 'Ativar',
            a.ativo ? 'bi-slash-circle' : 'bi-check-circle',
            () => alternarStatus(a)
        ));
        acoes.appendChild(criarBotao(
            'adm_btn_delete', 'Excluir', 'bi-trash',
            () => excluir(a)
        ));
    }

    tdAcoes.appendChild(acoes);
    tr.append(tdNome, tdEmail, tdStatus, tdData, tdAcoes);
    return tr;
}

function render() {
    const termo = busca.value.trim().toLowerCase();

    const filtrados = admins.filter((a) =>
        a.nome.toLowerCase().includes(termo) || a.email.toLowerCase().includes(termo)
    );

    corpo.replaceChildren(...filtrados.map(montarLinha));

    contador.textContent = admins.length;
    const vazio = filtrados.length === 0;
    vazioEl.hidden  = !vazio;
    tabelaEl.hidden = vazio;
}

/* ---------- Ações ---------- */

async function carregar() {
    try {
        const { dados } = await chamar('listar_admins.php');

        if (!dados.sucesso) {
            mostrarMsg(dados.mensagem || 'Não foi possível carregar a lista.');
            return;
        }

        admins = dados.admins;
        render();
    } catch (e) {
        if (e.message !== 'sessao') {
            mostrarMsg('Erro de conexão com o servidor.');
        }
    }
}

async function alternarStatus(a) {
    const desativando = a.ativo;

    const ok = await confirmar({
        titulo: desativando ? 'Desativar administrador?' : 'Ativar administrador?',
        texto: desativando
            ? `${a.nome} perderá o acesso ao painel imediatamente.`
            : `${a.nome} poderá acessar o painel novamente.`,
        botao: desativando ? 'Desativar' : 'Ativar',
        perigo: desativando
    });
    if (!ok) return;

    try {
        const { dados } = await post('alterar_status_admin.php', { id: a.id, ativo: !a.ativo });

        if (dados.sucesso) {
            a.ativo = !a.ativo;
            render();
            mostrarMsg(a.ativo ? 'Administrador ativado.' : 'Administrador desativado.', true);
        } else {
            mostrarMsg(dados.mensagem || 'Não foi possível alterar o status.');
        }
    } catch (e) {
        if (e.message !== 'sessao') mostrarMsg('Erro de conexão com o servidor.');
    }
}

async function excluir(a) {
    const ok = await confirmar({
        titulo: 'Excluir administrador?',
        texto: `${a.nome} será removido permanentemente. Essa ação não pode ser desfeita.`,
        botao: 'Excluir',
        perigo: true
    });
    if (!ok) return;

    try {
        const { dados } = await post('excluir_admin.php', { id: a.id });

        if (dados.sucesso) {
            admins = admins.filter((x) => x.id !== a.id);
            render();
            mostrarMsg('Administrador excluído.', true);
        } else {
            mostrarMsg(dados.mensagem || 'Não foi possível excluir.');
        }
    } catch (e) {
        if (e.message !== 'sessao') mostrarMsg('Erro de conexão com o servidor.');
    }
}

/* ---------- Início ---------- */

busca.addEventListener('input', render);
carregar();