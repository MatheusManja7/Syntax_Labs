<?php require_once __DIR__ . '/../../helpers/auth_guard.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Unbounded:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../../../public/style/root.css">
    <link rel="stylesheet" href="../../../public/style/style.css">
    <link rel="stylesheet" href="../../../public/style/style_menus_rod/style_menu_rod.css">
    <link rel="stylesheet" href="../../../public/style/style_menus_rod/style_menu_adm.css">
    <link rel="stylesheet" href="../../../public/style/style_adm/style_cad_adm.css">

    <title>Cadastro de ADM</title>
</head>
<body>
    <!-- Menu adm -->
    <header class="navbar navbar_adm" id="navbar">
        <a href="../admin/home_adm.php" class="logo">
            <img src="../../../public/assets/logo_name_sf.png" alt="Início">
        </a>

        <nav class="menu" id="menu">
            <a href="orcamentos.html">orçamentos</a>
            <a href="mensagens.html">mensagens</a>
            <a href="cadastro_adm.php">novo adm</a>
            <a href="listagem_adm.php">admins</a>
        </nav>

        <div class="navbar_actions" id="navbar_actions">
            <a href="../auth/login.html" class="btn" id="logout_btn">sair</a>

            <a href="perfil_adm.html" class="profile_icon" id="profile_icon" aria-label="Meu perfil">
                <i class="bi bi-person" aria-hidden="true"></i>
            </a>
            <a href="../admin/home_adm.php" class="btn_voltar" aria-label="Voltar para a home do ADM">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>Voltar</span>
            </a>

            <button class="hamburger" id="hamburger" aria-label="Abrir menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div>

        <div class="menu_overlay" id="menu_overlay"></div>
    </header>

    <section class="section_cad_adm">
        <!-- Efeito de fundo (mesmo da home do ADM) -->
        <div class="bg_effect" aria-hidden="true"></div>

        <div class="box_info_cad_adm">

            <form method="post" class="form_main" id="form_cad_adm" novalidate>

                <!-- Decoração interna do card -->
                <div class="form_deco" aria-hidden="true">
                    <i class="bi bi-person-plus-fill"></i>
                </div>

                <!-- Cabeçalho -->
                <div class="form_header">
                    <div class="form_icon" aria-hidden="true">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="form_header_text">
                        <h3>Novo ADM</h3>
                        <p>Cadastre um novo administrador</p>
                    </div>
                </div>

                <!-- Nome -> usuarios.nome (VARCHAR 100) -->
                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"></path>
                    </svg>
                    <input type="text" class="inputField" id="nome" name="nome"
                           placeholder="Nome completo" autocomplete="name"
                           maxlength="100" aria-label="Nome completo">
                </div>

                <!-- Email -> usuarios.email (VARCHAR 150, UNIQUE) -->
                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z"></path>
                    </svg>
                    <input type="email" class="inputField" id="email" name="email"
                           placeholder="E-mail" autocomplete="email"
                           maxlength="150" aria-label="E-mail">
                </div>

                <!-- Senha -> o back-end gera usuarios.senha_hash -->
                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="password" class="inputField" id="senha" name="senha"
                           placeholder="Senha (mín. 8 caracteres)" autocomplete="new-password" aria-label="Senha">
                </div>

                <!-- Confirmar senha (só validação no front) -->
                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="password" class="inputField" id="confirma_senha" name="confirma_senha"
                           placeholder="Confirmar senha" autocomplete="new-password" aria-label="Confirmar senha">
                </div>

                <p class="form_msg" id="cad_adm_msg" role="alert" hidden></p>

                <button type="submit" id="button">Cadastrar</button>
            </form>
        </div>
    </section>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
    <script src="../../../public/js/script_cad_adm.js"></script>
</body>
</html>