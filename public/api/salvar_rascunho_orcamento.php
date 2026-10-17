<?php

require_once __DIR__ . '/../../app/helpers/api_guard.php';
require_once __DIR__ . '/../../app/controllers/OrcamentoController.php';

(new OrcamentoController())->salvarRascunho();