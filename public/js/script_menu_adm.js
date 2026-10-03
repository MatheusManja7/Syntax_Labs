// script_menu_adm.js
(() => {
    const menu = document.getElementById('menu');
    const hamburger = document.getElementById('hamburger');
    const overlay = document.getElementById('menu_overlay');

    const profileMenu = document.getElementById('profile_menu');
    const profileIcon = document.getElementById('profile_icon');
    const profileDropdown = document.getElementById('profile_dropdown');

    const temMenuMobile = Boolean(hamburger && menu && overlay);

    // ----- Marca como ativo o link da página atual -----
    const paginaAtual = window.location.pathname.split('/').pop() || 'home_adm.php';
    document.querySelectorAll('#menu a').forEach((link) => {
        if (link.getAttribute('href') === paginaAtual) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });

    // ----- Dropdown do perfil -----
    function abrirPerfil() {
        if (!profileDropdown) return;
        profileDropdown.hidden = false;
        profileIcon.setAttribute('aria-expanded', 'true');
    }

    function fecharPerfil() {
        if (!profileDropdown) return;
        profileDropdown.hidden = true;
        profileIcon.setAttribute('aria-expanded', 'false');
    }

    if (profileMenu && profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.hidden ? abrirPerfil() : fecharPerfil();
        });

        // Clique fora fecha
        document.addEventListener('click', (e) => {
            if (!profileMenu.contains(e.target)) fecharPerfil();
        });

        // Esc fecha e devolve o foco ao ícone
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !profileDropdown.hidden) {
                fecharPerfil();
                profileIcon.focus();
            }
        });
    }

    // ----- Menu sanduíche (mobile) -----
    function abrirMenu() {
        if (!temMenuMobile) return;
        fecharPerfil();
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

        // Fecha o menu ao clicar em qualquer link dentro dele
        menu.addEventListener('click', (e) => {
            if (e.target.closest('a')) fecharMenu();
        });

        // Fecha com a tecla Esc
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') fecharMenu();
        });

        // Se a tela voltar ao tamanho de desktop com o menu aberto, fecha
        window.matchMedia('(max-width: 900px)').addEventListener('change', (e) => {
            fecharPerfil();
            if (!e.matches) fecharMenu();
        });
    } else if (hamburger) {
        hamburger.style.display = 'none';
    }
})();