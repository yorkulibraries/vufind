<?php
/**
 * @copyright   Copyright 2013, Vince Chu for Osgoode Hall Law School
 * @license     http://opensource.org/licenses/bsd-license.php
 */

include('lib/NewBooksPost.php');
$config = json_decode(file_get_contents('config.json'));
$message = date('Y-m-d H:i:s').": Run initialized.\r\n";
file_put_contents($config->runsLogSrc, $message, FILE_APPEND | LOCK_EX);
$nbp = new NewBooksPost($config);
echo $nbp->getPost();
$nbp->pushPost();
