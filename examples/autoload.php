<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

$autoloadPath = dirname(__FILE__).'/../vendor/autoload.php';

if(!file_exists($autoloadPath)) {
    throw new \Exception('Try running composer update first in '.realpath('..'));
} else {
    require_once($autoloadPath);
}
