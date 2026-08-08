<?php
/**
 * Single include for every public-facing page:
 * require_once __DIR__ . '/includes/bootstrap.php';
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/seo.php';
