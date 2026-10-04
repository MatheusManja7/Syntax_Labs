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
    <link rel="stylesheet" href="../../../public/style/style_adm/style_mensagens.css">

    <title>Mensagens</title>
</head>
<body>
    <!-- Menu adm -->
    <header class="navbar navbar_adm" id="navbar">
        <a href="../admin/home_adm.php" class="logo">
            <img src="../../../public/assets/logo_name_sf.png" alt="Início">
        </a>

        <nav class="menu" id="menu">
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

    <section class="section_msgs">
        <div class="bg_effect" aria-hidden="true"></div>

        <div class="box_info_msgs">

            <!-- Cabeçalho -->
            <div class="msgs_header">
                <div class="msgs_header_info">
                    <div class="msgs_header_text">
                        <h3>Mensagens</h3>
                        <p><span id="msgs_nao_lidas">0</span> não lidas</p>
                    </div>
                </div>

                <div class="search_box">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="msgs_busca" class="search_field"
                           placeholder="Buscar por nome, e-mail ou assunto" aria-label="Buscar mensagem">
                </div>
            </div>

            <!-- Filtros -->
            <div class="msgs_tabs" role="tablist" aria-label="Filtrar mensagens">
                <button type="button" class="msgs_tab active" data-filtro="todas" role="tab" aria-selected="true">
                    Todas <span class="msgs_tab_count" id="cnt_todas">0</span>
                </button>
                <button type="button" class="msgs_tab" data-filtro="nao_lidas" role="tab" aria-selected="false">
                    Não lidas <span class="msgs_tab_count" id="cnt_nao_lidas">0</span>
                </button>
                <button type="button" class="msgs_tab" data-filtro="lidas" role="tab" aria-selected="false">
                    Lidas <span class="msgs_tab_count" id="cnt_lidas">0</span>
                </button>
                <button type="button" class="msgs_tab" data-filtro="arquivadas" role="tab" aria-selected="false">
                    Arquivadas <span class="msgs_tab_count" id="cnt_arquivadas">0</span>
                </button>
            </div>

            <p class="msgs_aviso" id="msgs_aviso" role="status" hidden></p>

            <!-- Cards (preenchidos pelo JS) -->
            <div class="msgs_grid" id="msgs_grid"></div>

            <!-- Estado vazio -->
            <div class="msgs_empty" id="msgs_empty" hidden>
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <p>Nenhuma mensagem encontrada.</p>
            </div>
        </div>
    </section>

    <!-- Modal da mensagem -->
    <div class="msg_overlay" id="msg_modal" role="dialog" aria-modal="true" aria-labelledby="msg_modal_assunto" hidden>
        <div class="msg_modal_box">
            <button type="button" class="msg_modal_fechar" id="msg_modal_fechar" aria-label="Fechar">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>

            <div class="msg_modal_topo">
                <div class="msg_avatar msg_avatar_grande" id="msg_modal_avatar" aria-hidden="true"></div>
                <div class="msg_modal_quem">
                    <p class="msg_modal_nome" id="msg_modal_nome"></p>
                    <p class="msg_modal_email" id="msg_modal_email"></p>
                </div>
                <span class="msg_status" id="msg_modal_status"></span>
            </div>

            <h3 class="msg_modal_assunto" id="msg_modal_assunto"></h3>
            <p class="msg_modal_extra" id="msg_modal_extra" hidden></p>
            <p class="msg_modal_respondida" id="msg_modal_respondida" hidden></p>
            <p class="msg_modal_data" id="msg_modal_data"></p>

            <div class="msg_modal_texto" id="msg_modal_texto"></div>

            <div class="msg_modal_resposta">
                <label for="msg_resposta" class="msg_modal_label">Responder</label>
                <textarea id="msg_resposta" rows="5" maxlength="5000"
                          placeholder="Escreva a sua resposta..."></textarea>
                <p class="msg_modal_msg" id="msg_modal_msg" role="alert" hidden></p>
            </div>

            <div class="msg_modal_botoes">
                <div class="msg_botoes_esq">
                    <button type="button" class="msg_btn" id="msg_btn_arquivar">
                        <i class="bi bi-archive" aria-hidden="true"></i>
                        <span>Arquivar</span>
                    </button>
                    <button type="button" class="msg_btn msg_btn_perigo" id="msg_btn_excluir" hidden>
                        <i class="bi bi-trash" aria-hidden="true"></i>
                        <span>Excluir</span>
                    </button>
                </div>

                <button type="button" class="msg_btn" id="msg_btn_fechar">Fechar</button>
                <button type="button" class="msg_btn msg_btn_ok" id="msg_btn_enviar">
                    <i class="bi bi-send" aria-hidden="true"></i>
                    <span>Enviar resposta</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
    <script src="../../../public/js/script_mensagens.js"></script>
</body>
</html>