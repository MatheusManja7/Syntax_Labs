// JS - formulário de orçamento (versão curta)
(function () {
    "use strict";

    const form = document.getElementById("form_orcamento");
    if (!form) return;

    /* ---------- Pills (seleção única ou múltipla) ---------- */
    function syncHidden(group) {
        const groupName = group.dataset.group;
        const hiddenInput = document.getElementById(`input_${groupName}`);
        const activeTexts = [...group.querySelectorAll(".pill.active")].map((p) => p.textContent.trim());

        if (hiddenInput) hiddenInput.value = activeTexts.join(", ");

        group.classList.remove("pill_group_error");
        const error = document.querySelector(`[data-error-for="${groupName}"]`);
        if (error) error.classList.remove("is_visible");

        const otherField = document.querySelector(`[data-other-for="${groupName}"]`);
        if (otherField) otherField.hidden = !activeTexts.some((t) => /^outro/i.test(t));
    }

    function initPillGroup(group) {
        const mode = group.dataset.mode || "single";

        group.querySelectorAll(".pill").forEach((pill) => {
            pill.addEventListener("click", () => {
                if (mode === "single") {
                    const wasActive = pill.classList.contains("active");
                    group.querySelectorAll(".pill").forEach((p) => p.classList.remove("active"));
                    if (!wasActive) pill.classList.add("active"); // clicar de novo desmarca
                } else {
                    pill.classList.toggle("active");
                }
                syncHidden(group);
            });
        });

        syncHidden(group);
    }

    /* ---------- Upload de anexos ---------- */
    function initFileUpload() {
        const input = document.getElementById("anexos");
        const list = document.getElementById("anexos_list");
        if (!input || !list) return;

        let files = [];

        const syncInputFiles = () => {
            const dt = new DataTransfer();
            files.forEach((f) => dt.items.add(f));
            input.files = dt.files;
        };

        const render = () => {
            list.innerHTML = "";
            files.forEach((file, index) => {
                const chip = document.createElement("span");
                chip.className = "file_chip";

                const name = document.createElement("span");
                name.textContent = file.name;

                const removeBtn = document.createElement("button");
                removeBtn.type = "button";
                removeBtn.setAttribute("aria-label", `Remover ${file.name}`);
                removeBtn.textContent = "×";
                removeBtn.addEventListener("click", () => {
                    files.splice(index, 1);
                    syncInputFiles();
                    render();
                });

                chip.append(name, removeBtn);
                list.appendChild(chip);
            });
        };

        input.addEventListener("change", () => {
            files = [...input.files];
            render();
        });
    }

    /* ---------- Validação dos grupos obrigatórios ---------- */
    const REQUIRED_PILL_GROUPS = ["tipo_projeto", "objetivo_projeto"];

    function validatePillGroups() {
        let valid = true;

        REQUIRED_PILL_GROUPS.forEach((name) => {
            const input = document.getElementById(`input_${name}`);
            const group = document.querySelector(`.pill_group[data-group="${name}"]`);
            const error = document.querySelector(`[data-error-for="${name}"]`);
            const empty = !input || !input.value;

            if (group) group.classList.toggle("pill_group_error", empty);
            if (error) error.classList.toggle("is_visible", empty);
            if (empty) valid = false;
        });

        return valid;
    }

    /* ---------- Reset ---------- */
    function resetForm() {
        form.reset();
        document.querySelectorAll(".pill.active").forEach((p) => p.classList.remove("active"));
        document.querySelectorAll(".pill_group").forEach(syncHidden);
        document.querySelectorAll(".field_error").forEach((e) => e.classList.remove("is_visible"));
        document.querySelectorAll(".other_field").forEach((f) => { f.hidden = true; });
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

        // TODO: enviar ao backend (fetch com FormData, pois há anexos)
        console.log("Formulário de orçamento:", Object.fromEntries(new FormData(form)));
        alert("Recebemos sua solicitação! Vamos responder em até 48 horas.");
        resetForm();
    });

    /* ---------- Inicialização ---------- */
    form.querySelectorAll(".pill_group").forEach(initPillGroup);
    initFileUpload();
})();