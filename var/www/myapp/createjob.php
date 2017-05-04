<?php

chdir(__DIR__);

include __DIR__ . '/vendor/autoload.php';

use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

if (!$pheanstalk->getConnection()->isServiceListening()) {
    echo 'isServiceListening() returned false' . PHP_EOL;
    exit(1);
}

$jobid = $pheanstalk->putInTube(
    'testtube',
    json_encode(
        [
            'hello' => 'world',
        ]
    )
);

echo 'Job ID ' . $jobid . 'created' . PHP_EOL;
