<?php

chdir(__DIR__);

include __DIR__ . '/vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

if (!$pheanstalk->getConnection()->isServiceListening()) {
    echo 'isServiceListening() returned false' . PHP_EOL;
    exit(1);
}

$jobid = $argv[1];

$response = $pheanstalk->statsJob($jobid);
print_r($response->getArrayCopy());
sleep(2);

$response = $pheanstalk->statsJob($jobid);
print_r($response->getArrayCopy());
sleep(2);

$response = $pheanstalk->statsJob($jobid);
print_r($response->getArrayCopy());
sleep(2);

$response = $pheanstalk->statsJob($jobid);
print_r($response->getArrayCopy());
sleep(2);

$response = $pheanstalk->statsJob($jobid);
print_r($response->getArrayCopy());
sleep(2);
