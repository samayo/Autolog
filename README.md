## AUTOLOG

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
$ php composer.phar require samayo/autolog
````

Usage
-----
#### Simplest Example. 
A simple example to send your log to your inbox. 
```php
require __DIR__ . '/src/Logger.php'; 

$log = new Autolog\Logger;
$log['email'] = 'user@domain.tld'; 

if(/* something happens */){
	$log->log('something just happened', $log::INFO, $log::EMAIL); 	
}
?>
```
The `log()` method accepts 4 arguments, only the first `$msg` is required, others are optional. 
```php 
// method signature
function log($msg, $level = $log::INFO, $handler = $log::EMAIL, $verbosity = $log::SIMPLE){}
```
If you only pass `$msg`, the log will be considered as type: `INFO`, verbosity: `SIMPLE` and will sent by email.

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
##### Shortest example. 
```php 
// if you modify your email from within the class, you can just use this 
if($newComment){
	(new Autolog\Logger)->log('something');
}

// if not, you can set up an email in two ways
$logger = new Autolog\Logger; 
$logger['email'] = ''; 

// or just pass to the constructor
$logger = new Autolog\Logger(['email' => '']); 
```

##### Logging to file
```php

// requires option ['access.log' => 'log/logs.txt']
$log->log('simple log', $log::INFO, $log::FILE);
```
##### Sending by email
```php
// requires option ['email' => 'your email']
$log->log('simple log', $log::INFO, $log::EMAIL);
```
##### To database
```php
// requires table autolog and columns 'time', 'subject', 'level', 'message'
$log = new Autolog\Logger;
$log->pdo(new \PDO(
	// your pdo host, db, pass here
)); 
$log->log('simple log', $log::INFO, $log::DATABASE);
```
#### Method chaining
Autolog allows you to chain methods to save you time
```php
(new \Autolog\Logger)
	->pdo(new PDO(
		// your pdo details here
  ))->log('new user registration', $log::INFO, $log::DATABASE); 
```
Handling Exceptions/Errors
-----
You can wrap autolog with exception/error handlers 
```php 
// exceptions
$logger = Autolog\Logger(['email' => 'user@example.com']); 

set_exception_handler(function($e){
	$logger->log($e, $log::ERROR, $log::EMAIL);
}); 

// errors
set_error_handler(function($no, $str, $file, $line){
	$logger->log("Your site has error: $str in file $file at line $line", $log::ERROR, $log::EMAIL);
})

```
Autolog
-----
To get instant log notification when something happens invoke the `autolog()` method as

```php
$log->autolog(true, $log::EMAIL); 
```
This will periodically send new logs that appear in var/log/ for php/nginx/mysql/apache
For this to work, you need to follow the below example

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
