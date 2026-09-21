// script_menu.js
// JS - Fundo Menu: Ao scrollar o menu fica preto
const navbar = document.getElementById('navbar');

function verificarScroll() {
    if (!navbar) return;
    if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
}

window.addEventListener('scroll', verificarScroll);
verificarScroll();

// JS - Menu sanduíche (mobile)
const hamburger = document.getElementById('hamburger');
const menu = document.getElementById('menu');
const overlay = document.getElementById('menu_overlay');
const navbarActions = document.getElementById('navbar_actions');
const profileIcon = document.getElementById('profile_icon');
const budgetBtn = document.getElementById('budget_btn');

const temMenuMobile = Boolean(hamburger && menu && overlay);

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
        const estaAberto = menu.classList.contains('active');
        estaAberto ? fecharMenu() : abrirMenu();
    });

    overlay.addEventListener('click', fecharMenu);

    // Fecha o menu ao clicar em qualquer link dentro dele
    menu.addEventListener('click', (e) => {
        if (e.target.tagName === 'A') {
            fecharMenu();
        }
    });
} else if (hamburger) {
    // Página sem <nav>/overlay: o hambúrguer não tem o que abrir, então some
    hamburger.style.display = 'none';
}

// ----- Move o perfil e o botão de orçamento entre o navbar e o menu mobile -----
if (temMenuMobile && navbarActions) {
    const mediaQuery = window.matchMedia('(max-width: 900px)');

    function reorganizarNavbar(e) {
        const ehMobile = e.matches;

        if (ehMobile) {
            if (profileIcon) menu.insertBefore(profileIcon, menu.firstChild);

            if (budgetBtn) menu.appendChild(budgetBtn);

        } else {
            if (budgetBtn) navbarActions.insertBefore(budgetBtn, hamburger);
            
            if (profileIcon) navbarActions.insertBefore(profileIcon, hamburger);
            fecharMenu();
        }
    }

    reorganizarNavbar(mediaQuery);
    mediaQuery.addEventListener('change', reorganizarNavbar);
}