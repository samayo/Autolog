## Autolog

A PHP class to save/log/mail errors/notifications from your app or from `/var/log/` as they appear.

Install
-----

Using git
```bash
$ git clone https://github.com/samayo/autolog.git
```
Using composer
````bash
$ composer require samayo/autolog
````

Usage
-----
#### Short Example. 
To quickly email a user activity log
```php
require __DIR__ . "/src/Logger.php"; 

$log = new Autolog\Logger(["email" => "user@domain.tld"]);

if($comment){
   $log->log("$user just commented \n $comment", $log::INFO, $log::EMAIL); 	
}
```
The `$log->log()` method accepts 4 arguments, but only the first `$msg` is required. 
```php
/**
 * $msg (required) the actual content to send/log
 * $type (optional) the message type: error, info, notification..
 * $handler (optional) where to send it (db, email, file log)
 * $verbosity (optional) log as simple or verbose info
 */
log($msg, $type, $handler, $verbosity);
```
Available log types, handlers, and verbosity
```php
$type::INFO; // for simple tasks
$type::ERROR; // for errors ex: 404 .. 
$type::ALERT; // for fatal errors or suspicious activity

$handler::EMAIL; // send to email
$handler::FILE; // write to file
$handler::DATABASE; // insert to database 
$handler::SMS; // send to sms (not yet implemented)

$verbosity::SIMPLE; // send simplified log
$verbosity::VERBOSE; // send every log information

// the below will log an error, in verbose format and mail it
$log = new Autolog\Logger(["email" => "user@domain.tld"]);
$log->log($msg, $log::ERROR, $log::EMAIL, $log::VERBOSE);
```
By passing only the first arg: `$log->log($msg)` the log will tread as `ERROR`, `EMAIL`, `VERBOSE`

Examples
-----
	
#### Sending logs to your email
```php 
$log = new Autolog\Logger; 
$log["email"] = "user@domain.tld"; // add email

// or add your email list this: 
$log = new Autolog\Logger(["email" => "user@domain.tld"]); 

// then log it!
if($something){
   $log->log("something"); // email 'something'
}
```

#### Logging to file
To log to a file, you need to pass a writable file to `error.log`
```php
$log = new Autolog\Logger(["error.log" => __DIR__ . "/mylogs.txt"]); 
$log->log("ERROR: $error", $log::INFO, $log::FILE); // don't forget $log::FILE
```
#### Inserting to database
To store your logs in a database, create a db with these schema
```sql
--- database name can be anything, but table and columns should be as seen below
CREATE DATABASE IF NOT EXISTS autolog;  
USE autolog;
CREATE TABLE `logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `time` DATETIME DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `level` VARCHAR(50) DEFAULT NULL,
  `message` TEXT,
  PRIMARY KEY (id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4
```
Then log your info/error after calling the `pdo()` PDO object
```php
$log = new Autolog\Logger;
$log->pdo(new \PDO(
   // your pdo host, db, pass here
)); 
$log->log("simple log", $log::ERROR, $log::DATABASE);
```
#### Method chaining
You can even quickly chain methods as: 
```php
(new \Autolog\Logger)
   ->pdo(new PDO(/**/))->log("user: $user modified his/her profile", $log::INFO, $log::DATABASE); 
```
#### Handling Exceptions/Errors

To log all your exceptions/errors use example below: 
```php 
$logger = Autolog\Logger(["email" => "user@example.com"]); 

// mail all thrown exceptions
set_exception_handler(function($e) use($logger){
   $logger->log($e, $log::ERROR, $log::EMAIL);
}); 

// mail all errors
set_error_handler(function($no, $str, $file, $line) use ($logger){
   $logger->log("Your site has error: $str in file $file at line $line", $log::ERROR, $log::EMAIL);
})
```
#### Autologs `(via cronjob)`
To automatically detect log file changes and log messages, use `watch()` method. 
```php
// always watch new errors that appear in (nginx, php) log files
$log->watch(true);
```
To watch new logs and get notified, place the `watch()` method  in it's own file like: `log_mailer.php`
```php
// log_mailer.php
require __DIR__ . "/src/Logger.php";
(new Autolog\Logger([
  "nginx.log"  => "/var/log/nginx/error.log",
  "php-fpm.log"  => "/var/log/php-fpm/error.log",
  "mariadb.log" => "/var/log/mariadb/mariadb.log",
  "access.log"  => "access.txt",
  "email"  => "user@example.com"
]))->watch(true); 
```
Now, you can set a cronjob that executes the above script every min/hour then 
you'll get a new mail everytime a new error is logged in /var/log/... 

It is important give the `access.log` a file where the last time like: nginx.log is accessed. 
This is because to detect new error, you must store the timestamp of the last time we check nginx.log 
So, if the mtime for nginx file is not the same as we have stored, it means a new log is found ~

#### License: MIT
[autolog_archive]: http://github.com/samayo/autolog/releases
