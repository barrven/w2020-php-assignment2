<?php
session_start();


$config = [
    'MODEL_PATH' => APPLICATION_PATH . DS . 'model' . DS,
    'VIEW_PATH' => APPLICATION_PATH . DS . 'view' . DS,
    'DATA_PATH' => APPLICATION_PATH . DS . 'data' . DS
];

//connections for database using PDO
define('DB_CON', "mysql:host=gblearn.com;dbname=f9189284_test");
define('USER', "f9189284");
define('PASSWORD', "trombone22");
define('DB_NAME', 'f9189284_test');
define('HOST', 'gblearn.com');

require APPLICATION_PATH . DS . 'library' . DS . 'functions.php';
