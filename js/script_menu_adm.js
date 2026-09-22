// script_menu_adm.js
// JS - Fundo do menu adm: ao scrollar, o menu fica escuro
const navbarAdm = document.getElementById('navbar_adm');

function verificarScrollAdm() {
    if (!navbarAdm) return;
    if (window.scrollY > 40) {
        navbarAdm.classList.add('scrolled');
    } else {
        navbarAdm.classList.remove('scrolled');
    }
}

window.addEventListener('scroll', verificarScrollAdm);
verificarScrollAdm();

// JS - Menu mobile (hambúrguer) do painel administrativo
const hamburgerAdm = document.getElementById('hamburger_adm');
const navbarAdmActions = document.getElementById('navbar_adm_actions');
const menuOverlayAdm = document.getElementById('menu_overlay_adm');

const temMenuMobileAdm = Boolean(hamburgerAdm && navbarAdmActions && menuOverlayAdm);

function abrirMenuAdm() {
    if (!temMenuMobileAdm) return;
    hamburgerAdm.classList.add('active');
    navbarAdmActions.classList.add('active');
    menuOverlayAdm.classList.add('active');
    hamburgerAdm.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
}

function fecharMenuAdm() {
    if (!temMenuMobileAdm) return;
    hamburgerAdm.classList.remove('active');
    navbarAdmActions.classList.remove('active');
    menuOverlayAdm.classList.remove('active');
    hamburgerAdm.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}

if (temMenuMobileAdm) {
    hamburgerAdm.addEventListener('click', () => {
        const estaAberto = navbarAdmActions.classList.contains('active');
        estaAberto ? fecharMenuAdm() : abrirMenuAdm();
    });

    menuOverlayAdm.addEventListener('click', fecharMenuAdm);

    // Fecha o menu ao clicar em "Meu perfil" ou "Sair"
    navbarAdmActions.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
            fecharMenuAdm();
        }
    });

    // Fecha automaticamente se a tela voltar para desktop
    const mediaQueryAdm = window.matchMedia('(max-width: 720px)');
    mediaQueryAdm.addEventListener('change', (e) => {
        if (!e.matches) fecharMenuAdm();
    });
}

// JS - Botão "Sair" (logout)
// Aqui fica o ponto para limpar sessão/token antes de redirecionar ao login.
// Ex.: sessionStorage.removeItem('token_adm');
const btnSair = document.getElementById('btn_sair');
if (btnSair) {
    btnSair.addEventListener('click', () => {
        try {
            sessionStorage.removeItem('token_adm');
        } catch (err) {
            // Ambiente sem sessionStorage disponível — segue normalmente
        }
    });
}