<?php

namespace AutologTest;


require_once __DIR__ . '/../src/Logger.php';

use Autolog\Logger;

class LoggerOverride extends Logger
{
    public function toEmail($msg, $level, $subject = "Autolog\Log .. "){
    	return func_get_args(); 
    }
    public function toFile($time, $level, $message){
    	return func_get_args(); 
    }
    public function toDatabase($level, $subject, $message){
    	return func_get_args(); 
    }

    public function getProperties(){
    	return $this->config; 
    }
}

