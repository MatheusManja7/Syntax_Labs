<?php

require_once __DIR__ . '/../../app/helpers/api_guard.php';
require_once __DIR__ . '/../../app/controllers/AdminController.php';

(new AdminController())->alterarStatus();