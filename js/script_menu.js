// script_menu.js
// JS - Fundo Menu: Ao scrollar o menu fica preto
const navbar = document.getElementById('navbar');

function verificarScroll() {
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

function abrirMenu() {
    hamburger.classList.add('active');
    menu.classList.add('active');
    overlay.classList.add('active');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
}

function fecharMenu() {
    hamburger.classList.remove('active');
    menu.classList.remove('active');
    overlay.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}

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

// ----- Move o perfil e o botão de orçamento entre o navbar e o menu mobile -----
const mediaQuery = window.matchMedia('(max-width: 900px)');

function reorganizarNavbar(e) {
    const ehMobile = e.matches;

    if (ehMobile) {
        // Ícone de perfil vai para o topo do menu (antes dos links)
        menu.insertBefore(profileIcon, menu.firstChild);
        // Botão de orçamento vai para o final do menu (depois dos links)
        menu.appendChild(budgetBtn);
    } else {
        // Move de volta para o navbar, antes do hambúrguer
        navbarActions.insertBefore(budgetBtn, hamburger);
        navbarActions.insertBefore(profileIcon, hamburger);
        fecharMenu();
    }
}

reorganizarNavbar(mediaQuery);
mediaQuery.addEventListener('change', reorganizarNavbar);   