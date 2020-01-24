<?php

const DS = DIRECTORY_SEPARATOR;
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . DS . '..' . DS . 'app'));
require APPLICATION_PATH . DS . 'config' . DS . 'config.php';

$page = getParam('page', 'home');
$model = $config['MODEL_PATH'] . $page . '.php';
$view = $config['VIEW_PATH'] . $page . '.phtml';
$_404 = $config['VIEW_PATH'] . '404.phtml';
$subMenu = ''; //change this later when implementing login

if (file_exists($model)) {
    //echo $model;
    require $model;
}
$view_content = $_404; //default view is 404

if (file_exists($view)) {
    $view_content = $view; //if there's a view file for this page, assign it. $view_content is included in the layout.phtml
    //echo '<br>'.$view_content;
}

include $config['VIEW_PATH'] . 'layout.phtml';
