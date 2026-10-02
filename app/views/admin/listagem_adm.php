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
    <link rel="stylesheet" href="../../../public/style/style_adm/style_listagem_adm.css">

    <title>Listar ADM</title>
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

    <section class="section_list_adm">
        <!-- Efeito de fundo (mesmo da home do ADM) -->
        <div class="bg_effect" aria-hidden="true"></div>

        <div class="box_info_list_adm">

            <!-- Cabeçalho da listagem -->
            <div class="list_header">
                <div class="list_header_info">
                    <div class="list_icon" aria-hidden="true">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="list_header_text">
                        <h3>Administradores</h3>
                        <p><span id="list_count">0</span> cadastrados</p>
                    </div>
                </div>

                <div class="list_tools">
                    <div class="search_box">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="list_search" class="search_field"
                            placeholder="Buscar por nome ou e-mail" aria-label="Buscar administrador">
                    </div>
                    <a href="cadastro_adm.php" class="btn_novo">
                        <i class="bi bi-person-plus" aria-hidden="true"></i>
                        <span>Novo ADM</span>
                    </a>
                </div>
            </div>

            <p class="list_msg" id="list_msg" role="status" hidden></p>

            <!-- Tabela -->
            <div class="table_wrap" id="table_wrap">
                <table class="adm_table">
                    <thead>
                        <tr>
                            <th scope="col">Administrador</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">Status</th>
                            <th scope="col">Criado em</th>
                            <th scope="col" class="col_acoes">Ações</th>
                        </tr>
                    </thead>

                    <tbody id="list_body">

                    </tbody>
                </table>
            </div>

            <!-- Estado vazio -->
            <div class="list_empty" id="list_empty" hidden>
                <i class="bi bi-person-x" aria-hidden="true"></i>
                <p>Nenhum administrador encontrado.</p>
            </div>
        </div>
    </section>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
    <script src="../../../public/js/script_list_adm.js"></script>

</body>
</html>