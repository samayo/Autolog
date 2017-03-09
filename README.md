## AUTOLOG

A simple PHP class to log your info, errors or notifications. 

You can also setup a cronjob, and autolog will detect new logs in /var/log and log/mail it. 

> NOTE: This is a lazy-week-end hack/project attempt to handle a small issue. So, it's still in `¯\_(ツ)_/¯` phase

Install
-----

Using git
```bash
$ git clone https://github.com/samayo/autolog.git
```
Using composer
````bash
$ php require samayo/autolog
````

Usage
-----
#### Simplest Example. 
A simple example to send your log to your inbox. 
```php
require __DIR__ . "/src/Logger.php"; 

$log = new Autolog\Logger(["email" => "user@domain.tld"]);

if($userRegistration){
	$log->log("new user '$username' just signed up", $log::INFO, $log::EMAIL); 	
}
```
The `log()` method accepts 4 arguments, only the first `$msg` is required, others are optional. 
```php 
log($msg, $level, $handler, $verbosity);
```
If you only pass `$msg`, the log level will be: `$log::INFO`, verbosity: `$log::SIMPLE` and will sent by email.

Options
-----
You can select any of these options for the 3 optional arguments
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
##### Simplest example. 
```php 
// requires you to change the email inside the class 
if($something){
	(new Autolog\Logger)->log("something");
}

// you can also apply configs this way
$logger = new Autolog\Logger; 
$logger["email"] = "user@domain.tld"; 

// or just pass it to the constructor
$logger = new Autolog\Logger(["email" => ""]); 
```

##### Logging to file
```php
// requires option ["error.log" => "log/logs.txt"]
$log->log("simple log", $log::INFO, $log::FILE);
```
##### Sending by email
```php
// requires option ["email" => "your email"]
$log->log("simple log", $log::INFO, $log::EMAIL);
```
##### Inserting to database
Inserting to database is easy, but first you should create database `autolog` with table `logs`
```sql
CREATE DATABASE IF NOT EXISTS autolog; 
use autolog;
CREATE TABLE `logs` (
  `id` int(11) DEFAULT '11',
  `time` datetime DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `message` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```
Then simply log your error after calling the `pdo()` method and passing it your PDO object
```php
$log = new Autolog\Logger;
$log->pdo(new \PDO(
	// your pdo host, db, pass here
)); 
$log->log("simple log", $log::INFO, $log::DATABASE);
```
#### Method chaining
Autolog allows you to chain methods to save you time
```php
(new \Autolog\Logger)
  ->pdo(new PDO(
		// your pdo details here
  ))->log("new user registration", $log::INFO, $log::DATABASE); 
```
Handling Exceptions/Errors
-----
You can wrap autolog with exception/error handlers 
```php 
$logger = Autolog\Logger(["email" => "user@example.com"]); 

// exceptions
set_exception_handler(function($e) use($logger){
	$logger->log($e, $log::ERROR, $log::EMAIL);
}); 

// errors
set_error_handler(function($no, $str, $file, $line) use ($logger){
	$logger->log("Your site has error: $str in file $file at line $line", $log::ERROR, $log::EMAIL);
})

```
Autolog
-----
To get instant log notification when something happens call the `autolog()` method as

```php
$log->autolog(true, $log::EMAIL); 
```
This will periodically send new logs that appear in `var/log/` use as shown below:
```php
// in log_mailer.php
require __DIR__ . "/src/Logger.php";
(new Autolog\Logger([
	"nginx.log" 	=> "/var/log/nginx/error.log",
	"php-fpm.log" 	=> "/var/log/php-fpm/error.log",
	"mariadb.log" 	=> "/var/log/mariadb/mariadb.log",
	"access.log" 	=> "access.txt",
]))->autolog(true, $log::EMAIL); 
```
Set up a cronjob to execute the above file, then 
autolog will mail you new error that get logged to nginx/php/mariadb. 

It is important to create a simple `access.txt` file so autolog can keep 
the timestamp of it's last error checks. 


#### License: MIT

[autolog_archive]: http://github.com/samayo/autolog/releases
