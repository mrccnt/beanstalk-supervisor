<?php

chdir(__DIR__);

include __DIR__ . '/vendor/autoload.php';

use Pheanstalk\Pheanstalk;
use Pheanstalk\Exception;

$pheanstalk = new Pheanstalk('127.0.0.1');

if (!$pheanstalk->getConnection()->isServiceListening()) {
    echo 'isServiceListening() returned false' . PHP_EOL;
    exit(1);
}

while (true) {
    try {
        // Retrieve a job out of tube "testtube" by marking it as reserved
        //$job = $pheanstalk
        //    ->watch('testtube')
        //    ->ignore('default')
        //    ->reserve(10);

        // Retrieve a job out of tube "testtube" by marking it as reserved
        $job = $pheanstalk->reserveFromTube('testtube', 10);

        // Do we really have a job?
        if ($job) {
            // Find output in default.log
            echo 'Job-ID:   ' . $job->getId() .PHP_EOL;
            echo 'Job-Data: ' . PHP_EOL;
            print_r(json_decode($job->getData(), true));
            echo PHP_EOL;

            // If job is done, we can remove it from the queue
            $pheanstalk->delete($job);

            // Free memory
            $job = null;
        }
    } catch (Exception $exception) {
        // TODO: Log $exception
        // TODO: Maybe continue (after little sleep) if error is not service specific;
        // TODO; If exception is service specific we have got a problem => Log Critical and rethrow
        // Supervisor writes exception output to the configured "stderr_logfile" file
        // we defined in /etc/supervisor/conf.d/myapp.conf
        throw $exception;
    }
}
