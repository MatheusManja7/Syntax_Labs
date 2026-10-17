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
    <link rel="stylesheet" href="../../../public/style/style_adm/style_orcamentos.css">

    <title>Orçamentos</title>
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
            <a href="orcamentos.php">orçamentos</a>
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

    <section class="section_orc">
        <div class="bg_effect" aria-hidden="true"></div>

        <div class="box_info_orc">

            <!-- Cabeçalho -->
            <div class="orc_header">
                <div class="orc_header_text">
                    <h3>Orçamentos</h3>
                    <p><span id="orc_novos">0</span> novos</p>
                </div>

                <div class="orc_search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="orc_busca" class="orc_search_field"
                           placeholder="Buscar por nome, empresa ou e-mail" aria-label="Buscar orçamento">
                </div>
            </div>

            <!-- Abas por status -->
            <div class="orc_tabs" role="tablist" aria-label="Filtrar orçamentos por status">
                <button type="button" class="orc_tab active" data-filtro="novo" role="tab" aria-selected="true">
                    Novos <span class="orc_tab_count" id="cnt_novo">0</span>
                </button>
                <button type="button" class="orc_tab" data-filtro="editando" role="tab" aria-selected="false">
                    Editando <span class="orc_tab_count" id="cnt_editando">0</span>
                </button>
                <button type="button" class="orc_tab" data-filtro="enviado" role="tab" aria-selected="false">
                    Enviados <span class="orc_tab_count" id="cnt_enviado">0</span>
                </button>
                <button type="button" class="orc_tab" data-filtro="aceito" role="tab" aria-selected="false">
                    Aceitos <span class="orc_tab_count" id="cnt_aceito">0</span>
                </button>
                <button type="button" class="orc_tab" data-filtro="rejeitado" role="tab" aria-selected="false">
                    Rejeitados <span class="orc_tab_count" id="cnt_rejeitado">0</span>
                </button>
            </div>

            <p class="orc_aviso" id="orc_aviso" role="status" hidden></p>

            <!-- Cards (preenchidos pelo JS) -->
            <div class="orc_grid" id="orc_grid"></div>

            <div class="orc_empty" id="orc_empty" hidden>
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <p>Nenhum orçamento nesta etapa.</p>
            </div>
        </div>
    </section>

    <!-- Modal do orçamento -->
    <div class="orc_overlay" id="orc_modal" role="dialog" aria-modal="true" aria-labelledby="orc_modal_titulo" hidden>
        <div class="orc_modal_box">
            <button type="button" class="orc_modal_x" id="orc_modal_x" aria-label="Fechar">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>

            <!-- Quem pediu -->
            <div class="orc_modal_topo">
                <div class="orc_avatar orc_avatar_grande" id="orc_m_avatar" aria-hidden="true"></div>
                <div class="orc_modal_quem">
                    <h3 id="orc_modal_titulo"></h3>
                    <p class="orc_m_empresa" id="orc_m_empresa"></p>
                    <p class="orc_m_data" id="orc_m_data"></p>
                </div>
                <span class="orc_status" id="orc_m_status"></span>
            </div>

            <!-- Pedido do cliente (somente leitura) -->
            <h4 class="orc_bloco_titulo">Pedido do cliente</h4>
            <dl class="orc_dl" id="orc_m_detalhes"></dl>

            <!-- Proposta -->
            <form class="orc_form" id="orc_form" novalidate>
                <h4 class="orc_bloco_titulo">Proposta</h4>

                <p class="orc_nota_estimativa">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    O valor é uma <strong>estimativa (margem de preço)</strong> e pode mudar após a reunião com o
                    cliente, caso ele peça ou retire funcionalidades. Esse aviso aparece na proposta enviada.
                </p>

                <fieldset class="orc_fieldset" id="orc_fieldset">
                    <div class="orc_campo orc_full">
                        <label for="of_titulo">Título da proposta</label>
                        <input type="text" id="of_titulo" maxlength="150" placeholder="Ex.: Site institucional para Empresa X">
                    </div>

                    <div class="orc_campo orc_full">
                        <label for="of_escopo">Escopo (o que será entregue)</label>
                        <textarea id="of_escopo" rows="5" maxlength="5000" placeholder="Descreva as entregas, páginas, funcionalidades..."></textarea>
                    </div>

                    <div class="orc_campo">
                        <label for="of_prazo">Prazo de entrega</label>
                        <input type="text" id="of_prazo" maxlength="100" placeholder="Ex.: 6 a 8 semanas">
                    </div>

                    <div class="orc_campo">
                        <label for="of_validade">Validade da proposta</label>
                        <input type="date" id="of_validade">
                    </div>

                    <div class="orc_campo">
                        <label for="of_min">Valor estimado: de (R$)</label>
                        <input type="number" id="of_min" min="0" step="0.01" inputmode="decimal" placeholder="3000">
                    </div>

                    <div class="orc_campo">
                        <label for="of_max">até (R$)</label>
                        <input type="number" id="of_max" min="0" step="0.01" inputmode="decimal" placeholder="4500">
                    </div>

                    <div class="orc_campo orc_full">
                        <label for="of_pagto">Condições de pagamento</label>
                        <textarea id="of_pagto" rows="3" maxlength="1000" placeholder="Ex.: 50% na aprovação e 50% na entrega, via PIX ou boleto"></textarea>
                    </div>

                    <div class="orc_campo orc_full">
                        <label for="of_obs">Observações <span class="orc_opt">opcional</span></label>
                        <textarea id="of_obs" rows="3" maxlength="2000" placeholder="Informações adicionais para o cliente"></textarea>
                    </div>
                </fieldset>

                <p class="orc_modal_msg" id="orc_modal_msg" role="alert" hidden></p>

                <div class="orc_botoes">
                    <button type="button" class="orc_btn" id="orc_btn_reabrir" hidden>
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        <span>Reabrir para edição</span>
                    </button>

                    <button type="button" class="orc_btn orc_btn_esq" id="orc_btn_fechar">Fechar</button>

                    <button type="button" class="orc_btn" id="orc_btn_salvar">
                        <i class="bi bi-floppy" aria-hidden="true"></i>
                        <span>Salvar rascunho</span>
                    </button>
                    <button type="button" class="orc_btn orc_btn_ok" id="orc_btn_enviar">
                        <i class="bi bi-send" aria-hidden="true"></i>
                        <span>Enviar proposta</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts JS  -->
    <script src="../../../public/js/script_menu_adm.js"></script>
    <script src="../../../public/js/script_logout.js"></script>
    <script src="../../../public/js/script_orcamentos.js"></script>
</body>
</html>