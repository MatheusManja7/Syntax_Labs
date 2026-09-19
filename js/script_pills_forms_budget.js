// JS - efeitos da forms os campos, checklist e etc 
(function () {
    "use strict";

    const form = document.getElementById("form_orcamento");
    if (!form) return;

    /* ---------- Funcionalidades por tipo de projeto ---------- */
    const FUNC_MAP = {
        "Site institucional": [
            "Formulário de contato", "Captura de leads", "Integração com WhatsApp",
            "Blog", "SEO", "Animações", "Área administrativa",
            "Integração com redes sociais", "Google Analytics", "Outro"
        ],
        "Landing Page": [
            "Formulário de contato", "Captura de leads", "Integração com WhatsApp",
            "Blog", "SEO", "Animações", "Área administrativa",
            "Integração com redes sociais", "Google Analytics", "Outro"
        ],
        "Sistema Web": [
            "Login e cadastro", "Diferentes tipos de usuários", "Painel administrativo",
            "Dashboard", "Cadastro de clientes", "Cadastro de produtos", "Relatórios",
            "Upload de arquivos", "Sistema de notificações", "Integração com APIs",
            "Pagamentos", "Agendamentos", "Chat", "Outro"
        ],
        "Aplicativo": [
            "Android", "iOS", "Ambos", "Login", "Cadastro", "Notificações push",
            "Pagamentos", "Geolocalização", "Câmera", "Integração com sistema", "Outro"
        ],
        "SaaS": [
            "Cadastro/login", "Planos", "Assinatura recorrente", "Pagamento online",
            "Área do cliente", "Dashboard", "Multiusuário", "Diferentes níveis de acesso",
            "Painel administrativo", "Notificações", "API", "Outro"
        ],
        "E-commerce": [
            "Catálogo de produtos", "Carrinho de compras", "Pagamento online",
            "Cálculo de frete", "Cupons e promoções", "Cadastro de clientes",
            "Painel administrativo", "Integração com marketplaces", "Notificações", "Outro"
        ],
        "Portal / Plataforma": [
            "Login e cadastro", "Diferentes tipos de usuários", "Área de conteúdo/cursos",
            "Painel administrativo", "Dashboard", "Sistema de notificações",
            "Relatórios", "Integração com APIs", "Outro"
        ]
    };

    /* ---------- Utilitários ---------- */
    const slugify = (str) => str
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");


    /* ---------- (seleção única ou múltipla) ---------- */
    function syncHidden(group) {
        const groupName = group.dataset.group;
        const hiddenInput = document.getElementById(`input_${groupName}`);
        const activeTexts = [...group.querySelectorAll(".pill.active")].map((p) => p.textContent.trim());

        if (hiddenInput) hiddenInput.value = activeTexts.join(", ");

        group.classList.remove("pill_group_error");
        const error = document.querySelector(`[data-error-for="${groupName}"]`);
        if (error) error.classList.remove("is_visible");

        toggleOtherField(group, activeTexts);
        group.dispatchEvent(new CustomEvent("pillchange", { bubbles: true, detail: { activeTexts } }));
    }

    function toggleOtherField(group, activeTexts) {
        const groupName = group.dataset.group;
        const otherField = document.querySelector(`[data-other-for="${groupName}"]`);
        if (!otherField) return;
        const hasOther = activeTexts.some((t) => /^outro/i.test(t));
        otherField.hidden = !hasOther;
    }

    function initPillGroup(group) {
        const mode = group.dataset.mode || "single";

        group.querySelectorAll(".pill").forEach((pill) => {
            pill.addEventListener("click", () => {
                if (mode === "single") {
                    group.querySelectorAll(".pill").forEach((p) => p.classList.remove("active"));
                    pill.classList.add("active");
                } else {
                    pill.classList.toggle("active");
                }
                syncHidden(group);
            });
        });

        syncHidden(group);
    }

    function initAllPillGroups(root) {
        root.querySelectorAll(".pill_group").forEach(initPillGroup);
    }

    /* ---------- Campos condicionais simples (reação a um valor específico) ---------- */
    function initReveals() {
        document.querySelectorAll("[data-reveal-group]").forEach((block) => {
            const groupName = block.dataset.revealGroup;
            const acceptedValues = (block.dataset.revealValue || "").split(",").map((v) => v.trim());
            const group = document.querySelector(`.pill_group[data-group="${groupName}"]`);
            if (!group) return;

            const update = () => {
                const activeTexts = [...group.querySelectorAll(".pill.active")].map((p) => p.textContent.trim());
                block.hidden = !activeTexts.some((t) => acceptedValues.includes(t));
            };

            group.addEventListener("pillchange", update);
            update();
        });
    }

    /* ---------- Segmento da empresa (select -> campo "Outro") ---------- */
    function initSegmentoOutro() {
        const select = document.getElementById("segmento");
        const outroField = document.getElementById("campo_segmento_outro");
        if (!select || !outroField) return;

        const update = () => { outroField.hidden = select.value !== "Outro"; };
        select.addEventListener("change", update);
        update();
    }

    /* ---------- Checklist dinâmica de funcionalidades (etapa 4) ---------- */
    function buildFuncSubgroup(tipo) {
        const list = FUNC_MAP[tipo];
        if (!list) return null;

        const groupName = `func_${slugify(tipo)}`;

        const wrap = document.createElement("div");
        wrap.className = "func_subgroup";
        wrap.innerHTML = `
            <span class="func_subgroup_title">{ ${tipo} }</span>
            <div class="pill_group" data-group="${groupName}" data-mode="multi"></div>
            <input type="hidden" name="${groupName}" id="input_${groupName}" value="">
            <div class="other_field" data-other-for="${groupName}" hidden>
                <input type="text" name="${groupName}_outro" placeholder="Qual funcionalidade?">
            </div>
        `;

        const pillGroup = wrap.querySelector(".pill_group");
        list.forEach((item) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "pill";
            btn.textContent = item;
            pillGroup.appendChild(btn);
        });

        return wrap;
    }

    function renderFuncionalidades() {
        const container = document.getElementById("funcionalidades_container");
        const tipoGroup = document.querySelector('.pill_group[data-group="tipo_projeto"]');
        if (!container || !tipoGroup) return;

        const selected = [...tipoGroup.querySelectorAll(".pill.active")]
            .map((p) => p.textContent.trim())
            .filter((t) => t !== "Outro");

        container.innerHTML = "";

        if (selected.length === 0) {
            container.innerHTML = '<p class="field_hint">Selecione o que você precisa desenvolver na etapa 3 para ver sugestões de funcionalidades.</p>';
            return;
        }

        let anyRendered = false;
        selected.forEach((tipo) => {
            const subgroup = buildFuncSubgroup(tipo);
            if (!subgroup) return;
            container.appendChild(subgroup);
            initPillGroup(subgroup.querySelector(".pill_group"));
            anyRendered = true;
        });

        if (!anyRendered) {
            container.innerHTML = '<p class="field_hint">Sem sugestões automáticas para essa combinação — conte pra gente nos campos abaixo o que ela precisa ter.</p>';
        }
    }

    /* ---------- Upload de anexos ---------- */
    function initFileUpload() {
        const input = document.getElementById("anexos");
        const list = document.getElementById("anexos_list");
        if (!input || !list) return;

        let files = [];

        const render = () => {
            list.innerHTML = "";
            files.forEach((file, index) => {
                const chip = document.createElement("span");
                chip.className = "file_chip";
                chip.innerHTML = `<span>${file.name}</span>`;
                const removeBtn = document.createElement("button");
                removeBtn.type = "button";
                removeBtn.setAttribute("aria-label", `Remover ${file.name}`);
                removeBtn.textContent = "×";
                removeBtn.addEventListener("click", () => {
                    files.splice(index, 1);
                    syncInputFiles();
                    render();
                });
                chip.appendChild(removeBtn);
                list.appendChild(chip);
            });
        };

        const syncInputFiles = () => {
            const dataTransfer = new DataTransfer();
            files.forEach((file) => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        };

        input.addEventListener("change", () => {
            files = [...input.files];
            render();
        });
    }

    /* ---------- Validação dos grupos de escolhas obrigatórios ---------- */
    const REQUIRED_PILL_GROUPS = ["contato_preferido", "tipo_projeto", "objetivo_projeto"];

    function validatePillGroups() {
        let valid = true;

        REQUIRED_PILL_GROUPS.forEach((name) => {
            const input = document.getElementById(`input_${name}`);
            const group = document.querySelector(`.pill_group[data-group="${name}"]`);
            const error = document.querySelector(`[data-error-for="${name}"]`);
            const empty = !input || !input.value;

            if (empty) {
                valid = false;
                if (group) group.classList.add("pill_group_error");
                if (error) error.classList.add("is_visible");
            } else {
                if (group) group.classList.remove("pill_group_error");
                if (error) error.classList.remove("is_visible");
            }
        });

        return valid;
    }

    /* ---------- Reset completo do formulário ---------- */
    function resetForm() {
        form.reset();

        document.querySelectorAll(".pill_group .pill.active").forEach((p) => p.classList.remove("active"));
        document.querySelectorAll(".pill_group").forEach((group) => {
            group.classList.remove("pill_group_error");
            syncHidden(group);
        });

        document.querySelectorAll(".field_error").forEach((e) => e.classList.remove("is_visible"));
        document.querySelectorAll("[data-reveal-group]").forEach((b) => { b.hidden = true; });
        document.querySelectorAll(".other_field").forEach((f) => { f.hidden = true; });

        const segmentoOutro = document.getElementById("campo_segmento_outro");
        if (segmentoOutro) segmentoOutro.hidden = true;

        renderFuncionalidades();

        const list = document.getElementById("anexos_list");
        if (list) list.innerHTML = "";
    }

    /* ---------- Envio ---------- */
    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const pillsValid = validatePillGroups();
        const nativeValid = form.checkValidity();

        if (!nativeValid || !pillsValid) {
            form.reportValidity();
            const firstError = form.querySelector(".pill_group_error, :invalid");
            if (firstError) firstError.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
        }

        // TODO: substituir por uma chamada real (fetch/endpoint) quando o backend estiver pronto.
        console.log("Formulário de orçamento:", Object.fromEntries(new FormData(form)));
        alert("Recebemos sua solicitação! Vamos responder em até 48 horas.");
        resetForm();
    });

    /* ---------- Inicialização ---------- */
    initAllPillGroups(form);
    initReveals();
    initSegmentoOutro();
    initFileUpload();
    renderFuncionalidades();
})();