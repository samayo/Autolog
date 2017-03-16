## AUTOLOG

Autolog is a simple PHP class to log your info, errors and notifications. 

You can also setup a cronjob, and autolog will detect new logs in /var/log and log/mail it. 

> (!) This was a lazy-week-end-hack attempt to solve a small issue from some years ago, slightly polished. So, it's:  `¯\_(ツ)_/¯`

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
log($msg, $type, $handler, $verbosity);
```
You can use different options for $type, $handler, $verbosity
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
 
```
If you only pass `$msg` as `log($msg)` the default will be as: `log($msg, $log::INFO, $log::EMAIL, $log::SIMPLE);` 
-----


Examples
-----
##### Simplest example. 
```php 
// although requires you alter the class, and change the email
if($something){
	(new Autolog\Logger)->log("something");
}

// you can also config this way
$logger = new Autolog\Logger; 
$logger["email"] = "user@domain.tld"; 

// or just pass it to the constructor as
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
To log into a database, you can create something like this
```sql
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
Then simply log your error after calling the `pdo()` method and passing it your PDO object
```php
$log = new Autolog\Logger;
$log->pdo(new \PDO(
	// your pdo host, db, pass here
)); 
$log->log("simple log", $log::ERROR, $log::DATABASE);
```
#### Method chaining
You can quickly chain methods as: 
```php
(new \Autolog\Logger)
  ->pdo(new PDO(
		// your pdo details here
  ))->log("new user registration", $log::INFO, $log::DATABASE); 
```
Handling Exceptions/Errors
-----
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
Autolog
-----
To get instant log notification when something happens call the `autolog()` method as

```php
$log->autolog(true, $log::EMAIL); 
```
This will periodically send new logs that appear in `var/log/` use as shown below:
```php
// better to create a separate php file ex: log_mailer.php
require __DIR__ . "/src/Logger.php";
(new Autolog\Logger([
	"nginx.log" 	=> "/var/log/nginx/error.log",
	"php-fpm.log" 	=> "/var/log/php-fpm/error.log",
	"mariadb.log" 	=> "/var/log/mariadb/mariadb.log",
	"access.log" 	=> "access.txt",
]))->autolog(true, $log::EMAIL); 
```
Now, you can set a cronjob that executes the above script every hour then 
autolog will mail you new error that get are found to nginx/php/mariadb. 

It is important to create a simple `access.txt` file so autolog can keep 
the timestamp of it's last error checks. 


#### License: MIT

[autolog_archive]: http://github.com/samayo/autolog/releases
