<?php
declare(strict_types=1);

define('EXIT_SUCCESS', 0);
define('EXIT_FAILURE', 1);

define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'first-bootstrap');
define('DB_DRIVER', 'pdo_mysql');

define('BASEDIR', __DIR__ . '/../');

//define('PLANNER_URL', 'http://vertretungsplan.gym-oppenheim.de/V_DC_001.html');
define('PLANNER_URL', BASEDIR . 'tests/res/planner_page_1.html');

define('LOG_FILE', '../var/logfile.log');
define('LAST_UPDATE', '../var/last_update');

