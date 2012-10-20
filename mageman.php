#!/usr/bin/php -q 
<?php
/*
 *
 *
 *
 *
 *
 *
 */

$config = new SimpleXMLElement(file_get_contents($_SERVER['HOME'].'/.mageman')); 
require_once($config->basedir.'mageman.class.php');

$mageman = new mageman();
$mageman->initCli();
