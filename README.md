## Autolog

A PHP class to save/log/mail errors/notifications from your app or from `/var/log/` or as they appear.

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
This will send an email whenever you need. 
```php
require __DIR__ . "/src/Logger.php"; 

$log = new Autolog\Logger(["email" => "user@domain.tld"]);

if($commented){
   $log->log("someone just commented", $log::INFO, $log::EMAIL); 	
}
```
The `$log->log()` method accepts 4 arguments, only the first `$msg` is required, others are optional. 
```php
/**
 * $msg - the actuall content to send/log
 * $type - the message type: error, info, notification..
 * $handler - where to send it (db, email, file log)
 * $verbosity - log simple or verbose messages
 */
log($msg, $type, $handler, $verbosity);
```
You can use different logtypes, handler and verbosity as:  
```php
$type::INFO; // info for simple tasks
$type::ERROR; // simple error like 404 .. 
$type::ALERT; // fatal error or suspecious activity

$handler::EMAIL; // send to email
$handler::FILE; // write to file
$handler::DATABASE; // insert to database 
$handler::SMS; // send to sms

$verbosity::SIMPLE; // send simplified log
$verbosity::VERBOSE; // send every log information

// the below will log an error, in verbose format and mail it
$log = new Autolog\Logger(["email" => "user@domain.tld"]);
$log->log($msg, $log::ERROR, $log::EMAIL, $log::VERBOSE);
```
By passing only the first arg: `$log->log($msg)` the log will be info and email in verbose format

Examples
-----
	
#### Sending logs to your email
```php 
$log = new Autolog\Logger; 
$log["email"] = "user@domain.tld"; // add email

// or add your email list this: 
$log = new Autolog\Logger(["email" => ""]); 

// then log it!
if($something){
   $log->log("something"); // will be sent by email
}
```

#### Logging to file
To log in a file, you need to referent a writtable file to `error.log`
```php
$log = new Autolog\Logger(["error.log" => __DIR__ . "/mylogs.txt"]); 
$log->log("ERROR: $error", $log::INFO, $log::FILE); // don't forget $log::FILE
```
#### Inserting to database
To store your logs in a database, you should create a db with these schema
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
Then simply log your info/error after calling the `pdo()` method and passing it your PDO object
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
   ->pdo(new PDO(
   // your pdo details here
))->log("user: $user modified his/her profile", $log::INFO, $log::DATABASE); 
```
#### Handling Exceptions/Errors

To log all your exceptions/errors use Autolog as: 
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
To automatically watch and log error from your log folder like: `/var/log/` use `watch()`

```php
/**
 * always watch new errors that appear in your other (nginx, php) log files
 */ 
$log->watch(true);
```
This is how you should watch all file and get email when new log appears.
For this to work, place the `watch()` code in it's own file like: log_mailer.php
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
