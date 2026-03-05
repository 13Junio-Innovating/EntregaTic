<?php

$env = parse_ini_file('../config/.env');
define('DIRETORIO_BACKEND', $env['DIRETORIO_BACKEND']);

require DIRETORIO_BACKEND . 'report/send_mail_delivery_report.php';


