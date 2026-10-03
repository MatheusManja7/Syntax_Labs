<?php require_once __DIR__ . '/../../helpers/auth_guard.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../../../public/style/root.css">
    <link rel="stylesheet" href="../../../public/style/style_adm/style_home_adm.css">
    <link rel="stylesheet" href="../../../public/style/style_menus_rod/style_menu_rod.css">
    <link rel="stylesheet" href="../../../public/style/style_menus_rod/style_menu_adm.css">

    <title>Home Adm</title>
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
            <a href="mensagens.html">mensagens</a>
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

    <!-- Conteúdo da home adm -->
    <main class="main_adm">
        <!-- Efeito de Fundo  -->
        <div class="bg_effect"></div>

        <!-- Caixa que contem as informações da página  -->
        <div class="box_info_home_adm">

            <div class="box_info_home_adm">
                <div class="welcome">
                    <h1>Bem-vindo ao menu administrativo</h1>
                    <p class="welcome_user">
                        <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <!-- Grade dos Cards  -->
            <div class="grid_container">
                <!-- Card_1 - Orçamento  -->
                <a href="orcamentos.html" class="cards">
                    <span class="card_deco" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
                    <div class="card_top">
                        <span class="card_icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                        <div class="card_text">
                            <h3>Orçamentos</h3>
                            <p>Veja os pedidos de orçamento recebidos.</p>
                        </div>
                    </div>
                </a>

                <!-- Card_2 - Mensagens -->
                <a href="mensagens.html" class="cards">
                    <span class="card_deco" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                    <div class="card_top">
                        <span class="card_icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                        <div class="card_text">
                            <h3>Mensagens</h3>
                            <p>Leia as mensagens enviadas pelo site.</p>
                        </div>
                    </div>
                </a>

                <!-- Card_3 - Cadastro de Adms  -->
                <a href="cadastro_adm.php" class="cards">
                    <span class="card_deco" aria-hidden="true"><i class="bi bi-person-plus"></i></span>
                    <div class="card_top">
                        <span class="card_icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                        <div class="card_text">
                            <h3>Cadastrar adm</h3>
                            <p>Adicione um novo administrador ao painel.</p>
                        </div>
                    </div>
                </a>

                <!-- Card_4 - Lista de Adms  -->
                <a href="listagem_adm.php" class="cards">
                    <span class="card_deco" aria-hidden="true"><i class="bi bi-people"></i></span>
                    <div class="card_top">
                        <span class="card_icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                        <div class="card_text">
                            <h3>Listar adms</h3>
                            <p>Consulte os administradores cadastrados.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
</body>
</html>