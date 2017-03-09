<?php 
use PHPUnit\Framework\TestCase;

//@TODO: This class is far from complete. Needs more and more tests
class LoggerTest extends TestCase
{

	function __construct(){
		$this->logger = new \AutologTest\LoggerOverride();
	}


	public function testClassPropertiesReturnAssignedValues(){
		$prop = $this->logger->getProperties(); 
		$this->assertEquals($prop['email'], "user@domain.tld");
		$this->assertEquals($prop['nginx.log'], "/var/log/nginx/error.log");
		$this->assertNotEquals($prop['error.log'], '/fake/path/error.log');
	}



	public function testAssingNewValuesViaOffsetSettings(){
		$this->logger['email'] = 'fakemail@tld.com'; 
		$this->logger['error.log'] = '/fake/path/error.log'; 
		$prop = $this->logger->getProperties();
		$this->assertEquals($prop['email'], 'fakemail@tld.com');
		$this->assertNotEquals($prop['error.log'], '/var/log/nginx/error.log');
	}



	public function testAAssingNewValuesViaOffsetSettings(){
		$this->logger['email'] = 'fakemail@tld.com'; 
		$prop = $this->logger->getProperties();
		$this->assertEquals($prop['email'], 'fakemail@tld.com');
	}
}