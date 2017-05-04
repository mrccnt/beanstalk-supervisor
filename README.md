# Background Job Queue

This is an example of how to manage a job queue on a local virtual machine. The queue can handle jobs in the background,
while web applications can continue their work without having the current user to wait for long blocking responses.

Example would be to handle long person imports, doing some course-assignments using thousands of persons/courses or
generating large system reports.

## Prerequisits

We can achive all this by having the following components available:

 * Ubuntu 16.04 LTS 64 Bit Virtual Machine
 * Installed LAMP stack
 * Installed service [Beanstalk](http://kr.github.io/beanstalkd) (A simple and fast "in memory" work queue)
 * Installed service [Supervisor](http://supervisord.org) (A process control system)
 * Composer dependency [pda/pheanstalk](https://github.com/pda/pheanstalk) (Beanstalk client library)

## Glossary

Beanstalk internally uses the term "tube" which is some kind of named job- or work-queue. Let us keep that in mind
when reading further.

## Prepare test application

Copy all files from `var/www/myapp` directory to your remote host and execute `composer install` on the remote machine.

## Beanstalk

Beanstalk installs as a service (beanstalkd) and opens up port 11300 by default. The Beanstalk client we will use, will
connect to the service via `127.0.0.1:11300`. Keep an eye on that if you are going to use Beanstalk in production
systems. Make sure port `11300` is not available to public.

    $ sudo apt install beanstalkd

Beanstalk has no configuration which we should modifiy some how. It is ready to use out of the box. If you would like to
customize beanstalk, feel free to read their manuals.

## Supervisor

Supervisor is a client/server system that allows its users to monitor and control a number of processes on UNIX-like
operating systems. Supervisor is meant to be used to control processes related to a project or a customer, and is meant
to start like any other program at boot time.

    $ sudo apt install supervisor

Easy installation too. Supervisor comes preconfigured, but is disabled by default. If you need to know more about how
supervisor works, read the manual. They have pretty good docs. Go and create a config file for our joblistener. The
settings are pretty self explanatory:

    # create file /etc/supervisor/conf.d/myapp.conf as root and paste the following:
    
    [program:myapp]
    command=php /var/www/myapp/myapp.php
    process_name=%(program_name)s_%(process_num)02d
    stdout_logfile=/var/www/myapp/default.log
    stderr_logfile=/var/www/myapp/error.log
    user=acme
    environment=HOME="/home/acme",USER="acme",APPLICATION_ENV="development"
    autostart=true
    autorestart=true
    numprocs=2

Now we can start Supervisor:

    $ sudo service supervisor start

You should find two processes executing our configured command from the configuration file above. This means we have 2
threads of this listener up and running in parallel. Lets check if everything went well:

    $ ps aux | grep myapp
    
    acme      5484  0.0  1.3 328240 27072 ?        S    16:14   0:00 php /var/www/myapp/myapp.php
    acme      5485  0.0  1.3 328240 27192 ?        S    16:14   0:00 php /var/www/myapp/myapp.php
    root      5501  0.0  0.0  14224  1088 pts/0    S+   16:21   0:00 grep --color=auto myapp

## Usage

Now that we have supervisor up and running, we have prepared our job listener ([myapp.php](var/www/myapp/myapp.php)). This listener is
running in an endless loop and is listening to the tube *testtube*.

Remeber to restart Supervisor if you modified `myapp.php`.

    $ service supervisor restart

## Create/Insert Job

The [createjob.php](var/www/myapp/createjob.php) script can be executed manually and puts a new job into the tube *testtube*.
Additionally a payload (string) is sent with the job. We are using it as some kind of parameter container. To keep
things simple we are using json here.
    
    $ php createjob.php
    
If you want to see what both instances of our [myapp.php](var/www/myapp/myapp.php) processes are doing while looping and picking up
jobs, just tail the default and error logfiles and keep it in sight while putting new jobs into tube *testtube*.

    $ tail -f *.log
