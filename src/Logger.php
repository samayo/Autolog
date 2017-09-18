<?php
/**
 * Autolog | Simple log handler
 *
 * @author      Samson Daniel <samayo@protonmail.ch>
 * @link        https://github.com/samayo/autolog
 * @copyright   Copyright (c) 2017 
 * @license     http://www.opensource.org/licenses/mit-license.html | MIT License
 */
namespace Autolog;
class LoggerException extends \Exception{}
use ReflectionClass as Reflection; 
/**
 * An Autolog Class
 *
 * @category    Fastpress Framework
 * @package     Autolog
 * @version     0.1.0
 */
class Logger implements \ArrayAccess
{
    const INFO     = 1;
    const ALERT    = 2;
    const ERROR    = 3;

    const SMS      = 6; /* todo or not todo*/
    const FILE     = 7;
    const EMAIL    = 8;
    const DATABASE = 9;

    const SIMPLE   = 10;
    const VERBOSE  = 11;

    const AUTOLOG  = true;

    protected $defaultTitle = [
        self::INFO      => "new info from your logs",
        self::ALERT     => "new alert from your logs",
        self::ERROR     => "new error from your logs",
        self::SIMPLE    => "SIMPLE",
        self::VERBOSE   => "VERBOSE",
    ];

    protected $config = [
        'email'         => 'user@domain.tld', 
        "nginx.log"     => "/var/log/nginx/error.log",
        "php-fpm.log"   => "/var/log/php-fpm/error.log",
        "mariadb.log"   => "/var/log/mariadb/mariadb.log",
        'error.log'     => '', 
        'access.log'    => ''
    ]; 

    private $pdo = null; 
    private $dateFormat = "Y-m-d H:i:s";

    public function __construct($config = []){
        if(!empty($config)){
            $this->config = array_merge($this->config, $config); 
        }
    }

    public function pdo(\PDO $pdo){
        $this->pdo = $pdo;
    }

    protected function toSMS(){} // @TODO .. maybe

    protected function toEmail($msg, $subject = "Autolog\Log .. "){
        $email = $this->config["email"]; 
        $headers  = "From: " . $email . " \r\n";
        $headers .= "Reply-To: " . $email . " \r\n";
        $headers .= "MIME-Version: 1.0 \r\n";
        $headers .= "Content-Type: text/html; charset=UTF8 \r\n";
        mail($email, $subject, $msg, $headers);
    }

    protected function toFile($time, $level, $message){
        $path = $this->config["error.log"]; 
        if(!is_file($path) || !is_readable($path)){
           throw new LoggerException("Error file '$path' is not valid/readable file");
        }
        
        $array = [$time, $level, $message]; 
        file_put_contents($path, json_encode($array), FILE_APPEND);
    }

    protected function toDatabase($level, $subject, $message){
        if (!$this->pdo) {
            throw new LoggerException("Database connection not found");
        }

        $log = $this->pdo->prepare("INSERT INTO autolog (`time`, `level`, `subject`, `message`) VALUES (NOW(), ?, ?, ?)");
        $log->execute([$level, $subject, $message]);
    }

    protected function formatter($data, $level, $verbosity, $handler){
        $log = is_array($data) ? $data : array(); 
        
        $log['time'] = date($this->dateFormat); 
        $log["level"] = $this->defaultTitle[$level]; 

        if(is_string($data)){
            $log["msg"] = $data . "\r\n";
            $log["title"] = $this->defaultTitle[$level]; 
        }
        
        // if error passed as array
        if(is_array($data)){
            $log['title'] =  isset($log["title"]) ? $log['title'] : $this->defaultTitle[$level]; 
            $log["msg"]   =  isset($log["msg"]) ? $log['msg'] :  print_r($log, true);; 
        }

        // if error passed as object
        if(is_object($data)){
            $log['msg']   = print_r($data, true);
            $log['title'] = get_class($data);

            // no need to log incase this class itself throws exceptions
            // this will avoid recursive non-stop errors from being logged
            if($log['title'] == 'LoggerException'){
                return false; 
            }
        }
 
        if(self::FILE == $handler){
            $log['level'] = @array_flip($this->getConstants())[$level];
        }

        return (object) $log; 
    }

    public function log($msg, $level = self::INFO, $handler = self::EMAIL, $verbosity = self::SIMPLE){
        $log = $this->formatter($msg, $level, $verbosity, $handler);

        if(false === $log){
            return ; 
        }
       
        switch ($handler) {
            case self::FILE:
                $this->toFile($log->time, $log->level, $log->msg);
                break;

            case self::EMAIL:
                $this->toEmail($log->msg, $log->title);
                break;

            case self::DATABASE:
                $this->toDatabase($log->level, $log->title, $log->msg);
                break;

            case self::SMS:
                // $this->toSMS(); // TODO :
                break;
            default:
                break;
        }

        return $this;

    }

    private function isFile($filename){
        if (is_file($filename) && is_readable($filename)) {
            return $filename;
        }
    }

    public function watch($watch = false, $handler = self::EMAIL){
        if($watch){
            $this->Autologger($handler);
        }
    }

    protected function Autologger($handler){
        $systemLogFiles = $this->config['system.files']; 
        if (!$accessLog = $this->isFile($this->config["log.access"])) { 
            return;
        }

        $logTime = json_decode(file_get_contents($accessLog), true);

        foreach ($systemLogFiles as $system => $logFile) {
            if (!$this->isFile($logFile)) {
                return;
            }

            if (!in_array($system, array_keys($logTime))) {
                return;
            }

            if (substr($logTime[$system], 0, 9) === substr(filemtime($logFile), 0, 9)) {
                return;
            }

            $logTime[$system] = time();
            touch($logFile);

            file_put_contents($accessLog, json_encode($logTime));

            $log = array(); 
            $log["msg"] = exec("tail " . $logFile); 
            $log["title"] = "New error log from " . $system;
            $this->log($log, self::ERROR, $handler);
        }
    }

    public function offsetGet($offset){
        if(!array_key_exists($offset, $this->config)){
           throw new Exception(sprintf(
                    "Unknown config passed. %s is not recognized option", $offset
                ));
        }
    }
    
    public function offsetSet($offset, $value){
         $this->config[$offset] = $value;
    }
    
    public function offsetUnset($offset){}
    public function offsetExists($offset){}

    public function logParser(){} //@TODO

    protected function getConstants(){
        $reflection = new Reflection(__CLASS__); 
        return $reflection->getConstants(); 
    }
}

