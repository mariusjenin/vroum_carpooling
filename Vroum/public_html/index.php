<?php

use Vroum\VroumApp;
use Vroum\Controler\DBManager;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

date_default_timezone_set('Europe/Paris');

DBManager::setup();

$vroum= VroumApp::getInstance();

$vroum->addRoutes();

// TODO à retirer
$vroum->addRoutesTest();
$vroum->addRoutesDesignDev();
//

$vroum->run();
