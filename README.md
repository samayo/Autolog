## Autolog

A lonely PHP class to help log/send your errors, notifications from your app or from `/var/log/` or as they appear 

> (!) this is a slightly polished: lazy weekend-hack/project from some years ago. you know what that means `¯\_(ツ)_/¯`

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
$log::INFO; // info for simple tasks
$log::ERROR; // simple error like 404 .. 
$log::ALERT; // fatal error or suspecious activity

// Options for $handler - on how to register/save the log message
$log::EMAIL; // send to email
$log::FILE; // write to file
$log::DATABASE; // insert to database 
$log::SMS; // send to sms

// do you need the all the info, or relevant (simple)
$log::SIMPLE; // send simplified log
$log::VERBOSE; // send every log information

// to get log of all error in verbose format
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

// the log it!
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
To store your logs in a database, you can create something like this
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
You can quickly chain methods as: 
```php
(new \Autolog\Logger)
   ->pdo(new PDO(
   // your pdo details here
))->log("user: $user modied profile", $log::INFO, $log::DATABASE); 
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
// better to create a separate php file ex: log_mailer.php
require __DIR__ . "/src/Logger.php";
(new Autolog\Logger([
  "nginx.log"		=> "/var/log/nginx/error.log",
  "php-fpm.log"		=> "/var/log/php-fpm/error.log",
  "mariadb.log"		=> "/var/log/mariadb/mariadb.log",
  "access.log"		=> "access.txt",
  "email"			=> "user@example.com"
]))->watch(true); 
```
Now, you can set a cronjob that executes the above script every hour then 
autolog will mail you new error that get are found to nginx/php/mariadb. 

It is important to create a simple `access.txt` file so autolog can keep 
the timestamp of it's last error checks. 


##### License: MIT
[autolog_archive]: http://github.com/samayo/autolog/releases
