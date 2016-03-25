<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

if(!file_exists('../vendor/autoload.php')) {
    throw new \Exception('Try running composer update first in '.realpath('..'));
} else {
    require_once('../vendor/autoload.php');
}
