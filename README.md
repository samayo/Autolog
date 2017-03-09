## AUTOLOG
=======================================

A single class library to log messages, errors to files, database, email, sms which 
also reads your system log files (php, nginx, mariadb) .. and sends new logs automatically (using a cronjob).   

This project is a simple week-end hack/solution to a problem. It needs improvements
Install
-----

Using git
```bash
$ git clone https://github.com/samayo/autolog.git
```
Using composer
````bash
$ php composer.phar require samayo/autolog:2.0.*
````
Or [download it manually][autolog_archive] based on the archived version of release-cycles.

Usage
-----
#### Simplest Example. 
A simple example to send your log to your inbox. 
```php
require __DIR__ . '/src/autolog.php'; 

$log = new Autolog\Logger([
	'email' => 'user@email.com', 
	'nginx.log' => '/var/log/nginx/error.log',
	'error.log' => 'log/logs.txt',
	'access.log' => 'log/access.txt'
]);

if(/* something happens */){
	$log->log('something just happened', $log::INFO, $log::EMAIL); 	
}
?>
```
The `log()` method accepts 4 arguments, only the first is required, others are optional. 
```php 
// method signature
log($msg, $level = $log::INFO, $handler = $log::EMAIL, $verbosity = $log::SIMPLE){}
```
If you only pass `$msg`, the log will be considered as INFO, SIMPLE and will sent by email. 

Options
-----
```php 
// to describe the log type
$log::INFO; // info for simple tasks
$log::ERROR; // simple error like 404 .. 
$log::ALERT; // fatal error or suspecious activity

// how to register/save the log message
$log::EMAIL; // send to email
$log::FILE; // write to file
$log::DATABASE; // insert to database 
$log::SMS; // send to sms

// do you prefer simple/verbose logs
$log::SIMPLE; // send simplified log
$log::VERBOSE // send every log information
``` 

Examples
-----
Shortest example. 
```php 
//if you modify your email from within the class, you can just use this 
if($newComment){
	(new Autolog\Logger)->log('something');
}

// if not, you can set up an email in two ways
$logger = new Autolog\Logger; 
$logger['email'] = ''; 
// or just pass to constructor
$logger = new Autolog\Logger(['email' => '']); 
```

Sending your log to file 
```php
$log = new Autolog\Logger([
	'access.log' => 'log/access.txt'
]);
$log->log('simple log', $log::INFO, $log::FILE);
```
Sending your log to email 
```php
$log = new Autolog\Logger([
	'email' => 'your email here', 
]);
$log->log('simple log', $log::INFO, $log::EMAIL);
```
Sending your log to database 
```php
// make sure to create to create table named 'autolog' and 
// 'time', 'subject', 'level', 'message' columns
$pdo = new PDO('mysql:host=localhost; dbname=##', '##', '##'); 
$log = new Autolog\Logger();
$log->db($pdo); 
$log->log('simple log', $log::INFO, $log::DATABASE);
```

Handling php exceptions and error
```php 
// if exception is thrown, log it
set_exception_handler(function($error){
	(new Autolog\Logger)->log($e, $log::ERROR, $log::FILE);
}); 

// if there is an error. 
set_error_handler(function($no, $str, $file, $line){
	(new Autolog\Logger)->log("Your site has error: $str in file $file at line $line", $log::ERROR);
})

```
To periodically send new logs from php/nginx/mysql/apache ... require the class and trigger a task
it is better to do this in a separate file so you can execute it via cronjob 

```php
// in log_mailer.php
require __DIR__ . '/src/autolog.php';
$log = new Autolog\Logger([
    "nginx.log" => "/var/log/nginx/error.log",
    "php-fpm.log" => "/var/log/php-fpm/error.log",
    "mariadb.log" => "/var/log/mariadb/mariadb.log",
    "access.log" => "access.log",
]);

$log->autolog(true, $log::EMAIL); 
```
In the above example, if you set up a cronjob to execute the file every hour, then 
autolog with check the timestamp of your error logs, and sends you new logs. 
It is important to create a simple access.log file so autolog can keep 
the timestamp of it's last error checks. 


#### License: MIT

[autolog_archive]: http://github.com/samayo/autolog/releases
