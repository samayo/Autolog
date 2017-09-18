## Autolog

A PHP class that to save/log/mail errors or notifications from your app or from `/var/log/` or as they appear 

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
A simple snippet to send a message to your inbox. 
```php
require __DIR__ . "/src/Logger.php"; 

$log = new Autolog\Logger(["email" => "user@domain.tld"]);

if($userCommented){
   $log->log("Someone just commented!", $log::INFO, $log::EMAIL); 	
}
```
The `$log->log()` method accepts 4 arguments, only the first `$msg` is required, others are optional. 
```php 
log($msg, $type, $handler, $verbosity);
```
You can use different logtype, handler and verbosity: 
```php
// options for $type  - to describe the log type
$type::INFO; // info for simple tasks
$type::ERROR; // simple error like 404 .. 
$type::ALERT; // fatal error or suspecious activity

// Options for $handler - on how to register/save the log message
$handler::EMAIL; // send to email
$handler::FILE; // write to file
$handler::DATABASE; // insert to database 
$handler::SMS; // send to sms

// do you need the all the info, or relevant (simple)
$verbosity::SIMPLE; // send simplified log
$verbosity::VERBOSE; // send every log information

// to get log of all error in verbose format
$log = new Autolog\Logger(["email" => "user@domain.tld"]);
$log->log($msg, $log::ERROR, $log::EMAIL, $log::VERBOSE);
```

Passing only the message as `$log->log($msg)` is possible, and it'll be handled type: INFO, and sent by email 

Examples
-----
	
##### Sending logs to your email
```php 
// First you need to setup your email as
$log = new Autolog\Logger; 
$log["email"] = "user@domain.tld"; 

// or just pass it to the constructor as
$log = new Autolog\Logger(["email" => ""]); 

// then log it!
if($something){
   $log->log("something");
}
```

##### Logging to file
We need to pass out file location for this work
```php
$log = new Autolog\Logger(["error.log" => __DIR__ . "/mylogs.txt"]); 
$log->log("some $error ", $log::INFO, $log::FILE);
```
##### Inserting to database
To store your logs in a database, you should create a database with these schema
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
##### Method chaining
You can even quickly chain methods as: 
```php
(new \Autolog\Logger)
   ->pdo(new PDO(
   // your pdo details here
))->log("user: $user modified his/her profile", $log::INFO, $log::DATABASE); 
```
##### Handling Exceptions/Errors

You can wrap autolog inside an exception/error this way 
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
#### Autologs (via cronjob)
If you want to get notifid when ex: new errors appear in /var/log/ then use the `watch()`  method

```php
$log->watch(true); // true activates the autolog
```
This will periodically send new logs that appear in `var/log/` use as shown below:
```php
// better to create a separate php file for this script ex: log_mailer.php
require __DIR__ . "/src/Logger.php";
(new Autolog\Logger([
  "nginx.log"  => "/var/log/nginx/error.log",
  "php-fpm.log"  => "/var/log/php-fpm/error.log",
  "mariadb.log" => "/var/log/mariadb/mariadb.log",
  "access.log"  => "access.txt",
  "email"  => "user@example.com"
]))->watch(true); 
```
Now, you can set a cronjob that executes the above script every hour then 
autolog will mail you new errors that it finds in /var/log/{nginx/php/mariadb}/. 

It is important to create a simple `access.txt` file so autolog can keep 
the timestamp of it's last error checks. 


##### License: MIT
[autolog_archive]: http://github.com/samayo/autolog/releases
