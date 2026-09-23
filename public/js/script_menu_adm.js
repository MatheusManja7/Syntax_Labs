// script_menu_adm.js
const menu = document.getElementById('menu');
const hamburger = document.getElementById('hamburger');
const overlay = document.getElementById('menu_overlay');
const navbarActions = document.getElementById('navbar_actions');
const profileIcon = document.getElementById('profile_icon');
const logoutBtn = document.getElementById('logout_btn');

const temMenuMobile = Boolean(hamburger && menu && overlay);

// ----- Marca como ativo o link da página atual -----
const paginaAtual = window.location.pathname.split('/').pop() || 'home_adm.html';
document.querySelectorAll('#menu a').forEach((link) => {
    if (link.getAttribute('href') === paginaAtual) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
    }
});

// ----- Menu sanduíche (mobile) -----
function abrirMenu() {
    if (!temMenuMobile) return;
    hamburger.classList.add('active');
    menu.classList.add('active');
    overlay.classList.add('active');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
}

function fecharMenu() {
    if (!temMenuMobile) return;
    hamburger.classList.remove('active');
    menu.classList.remove('active');
    overlay.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}

if (temMenuMobile) {
    hamburger.addEventListener('click', () => {
        menu.classList.contains('active') ? fecharMenu() : abrirMenu();
    });

    overlay.addEventListener('click', fecharMenu);

    // Fecha o menu ao clicar em qualquer link dentro dele (inclui o "sair")
    menu.addEventListener('click', (e) => {
        if (e.target.closest('a')) fecharMenu();
    });

    // Fecha com a tecla Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fecharMenu();
    });
} else if (hamburger) {
    hamburger.style.display = 'none';
}

// ----- Move perfil e "sair" entre o navbar e o menu mobile -----
if (temMenuMobile && navbarActions) {
    const mediaQuery = window.matchMedia('(max-width: 900px)');

    function reorganizarNavbar(e) {
        if (e.matches) {
            // Mobile: perfil no topo do menu, "sair" no fim
            if (profileIcon) menu.insertBefore(profileIcon, menu.firstChild);
            if (logoutBtn) menu.appendChild(logoutBtn);
        } else {
            // Desktop: volta para a direita (sair, depois o perfil)
            if (logoutBtn) navbarActions.insertBefore(logoutBtn, hamburger);
            if (profileIcon) navbarActions.insertBefore(profileIcon, hamburger);
            fecharMenu();
        }
    }

    reorganizarNavbar(mediaQuery);
    mediaQuery.addEventListener('change', reorganizarNavbar);
}