<?php

require_once('class/OneFileFramework.php');

ini_set('max_execution_time', 0);
ini_set('memory_limit',-1);

session_start();

OneFileFramework::ErrorHandler();
OneFileFramework::AutoloadClass();

OneFileFramework::addRoute('/', 'Simple', 'home', 'database');
OneFileFramework::addRoute('/requestlist', 'Requestlist', 'reqlist', 'requestlist');

OneFileFramework::addRoute('/getschema', 'Simple', 'get_schema');
OneFileFramework::addRoute('/gettable', 'Simple', 'get_table');
OneFileFramework::addRoute('/request', 'Simple', 'execute_request');

$config = include('conf'.DIRECTORY_SEPARATOR .'config.php');
PGSQL::setHost($config['host']);
PGSQL::setUser($config['username']);
PGSQL::setPassword($config['password']);
PGSQL::setPort($config['port']);

OneFileFramework::run();