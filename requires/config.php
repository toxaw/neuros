<?php
const DB_NAME = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_BASE = 'neuros';


eval('const APP_URL = "' . ('http' . ($_SERVER['HTTPS'] ? 's':'') . '://' . $_SERVER['SERVER_NAME']) . '/";');


