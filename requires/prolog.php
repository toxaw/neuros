<?php
require 'config.php';
header('Content-Type: text/html; charset=utf-8');
spl_autoload_register(function ($class) {
    require __DIR__ . '/../modules/' . $class . '.php';
});