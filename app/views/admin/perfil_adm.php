<?php
require_once __DIR__ . '/../../helpers/auth_guard.php';

$usuario = (new Usuario())->buscarPorId((int) $_SESSION['usuario_id']);
$h = fn (string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>

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
    <link rel="stylesheet" href="../../../public/style/style_adm/style_perfil_adm.css">

    <title>Meu Perfil</title>
</head>
<body>
    <!-- Menu adm -->
    <header class="navbar navbar_adm" id="navbar">
        <a href="../admin/home_adm.php" class="logo">
            <img src="../../../public/assets/logo_name_sf.png" alt="Início">
        </a>

        <nav class="menu" id="menu">
            <!-- Só aparecem no mobile -->
            <a href="perfil_adm.php" class="profile_icon menu_mobile_only" aria-label="Meu perfil">
                <i class="bi bi-person" aria-hidden="true"></i>
            </a>

            <a href="home_adm.php">home</a>
            <a href="orcamentos.html">orçamentos</a>
            <a href="mensagens.php">mensagens</a>
            <a href="cadastro_adm.php">novo adm</a>
            <a href="listagem_adm.php">admins</a>

            <button type="button" class="menu_mobile_only menu_logout" data-logout>
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>sair</span>
            </button>
        </nav>

        <div class="navbar_actions" id="navbar_actions">
            <div class="profile_menu" id="profile_menu">
                <button type="button" class="profile_icon" id="profile_icon"
                        aria-label="Menu do perfil" aria-haspopup="true"
                        aria-expanded="false" aria-controls="profile_dropdown">
                    <i class="bi bi-person" aria-hidden="true"></i>
                </button>

                <div class="profile_dropdown" id="profile_dropdown" role="menu" hidden>
                    <a href="perfil_adm.php" role="menuitem">
                        <i class="bi bi-person-gear" aria-hidden="true"></i>
                        <span>Perfil</span>
                    </a>
                    <button type="button" data-logout role="menuitem">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        <span>Sair</span>
                    </button>
                </div>
            </div>

            <button class="hamburger" id="hamburger" aria-label="Abrir menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="menu_overlay" id="menu_overlay"></div>
    </header>

    <section class="section_perfil_adm">
        <div class="bg_effect" aria-hidden="true"></div>

        <div class="box_info_perfil_adm">

            <!-- Card 1: dados -->
            <form method="post" class="form_main" id="form_perfil_dados" novalidate>
                <div class="form_deco" aria-hidden="true">
                    <i class="bi bi-person-circle"></i>
                </div>

                <div class="form_header">
                    <div class="form_icon" aria-hidden="true">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div class="form_header_text">
                        <h3>Meu perfil</h3>
                        <p>Atualize os seus dados</p>
                    </div>
                </div>

                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"></path>
                    </svg>
                    <input type="text" class="inputField" id="nome" name="nome"
                        value="<?= $h($usuario['nome'] ?? '') ?>"
                        placeholder="Nome completo" autocomplete="name"
                        maxlength="100" aria-label="Nome completo">
                </div>

                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"></path>
                    </svg>
                    <input type="email" class="inputField" id="email" name="email"
                        value="<?= $h($usuario['email'] ?? '') ?>"
                        readonly aria-label="E-mail (não editável)">
                </div>

                <p class="form_msg" id="msg_dados" role="alert" hidden></p>

                <button type="submit" class="form_button">Salvar alterações</button>
            </form>

            <!-- Card 2: senha -->
            <form method="post" class="form_main" id="form_perfil_senha" novalidate>
                <div class="form_deco" aria-hidden="true">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>

                <div class="form_header">
                    <div class="form_icon" aria-hidden="true">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="form_header_text">
                        <h3>Alterar senha</h3>
                        <p>Informe a senha atual para trocar</p>
                    </div>
                </div>

                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="password" class="inputField" id="senha_atual" name="senha_atual"
                        placeholder="Senha atual" autocomplete="current-password" aria-label="Senha atual">
                </div>

                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="password" class="inputField" id="senha_nova" name="senha_nova"
                        placeholder="Nova senha" autocomplete="new-password" aria-label="Nova senha">
                </div>

                <div class="inputContainer">
                    <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="password" class="inputField" id="senha_confirma" name="senha_confirma"
                        placeholder="Confirmar nova senha" autocomplete="new-password" aria-label="Confirmar nova senha">
                </div>

                <p class="form_msg" id="msg_senha" role="alert" hidden></p>

                <button type="submit" class="form_button">Alterar senha</button>
            </form>

        </div>
    </section>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
    <script src="../../../public/js/script_perfil_adm.js"></script>
</body>
</html>