<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Log;
use Rep98\Collection\Helpers\Config;
use Monolog\Logger;
use Nette\Schema\Schema;
/**
 * LogTest
 * @covers \Rep98\Collection\Helpers\Log
 * @uses Monolog\Logger
 */
class LogTest extends TestCase
{
	public function testGetSchema()
	{
		$this->assertInstanceOf(Schema::class, Log::getSchema());
	}
	/**
	 * @uses \Rep98\Collection\Helpers\Config
	 * @uses \Monolog\Logger
	 * @uses config
	 */
	public function testGetChanel()
	{
		Config::from(["logging" => ["enabled" => false]]);
		$this->assertFalse(Log::getChanel());

		Config::from(["logging" => ["enabled" => true]]);
		$this->assertInstanceOf(Logger::class, Log::getChanel());
	}
	/**
	 * @uses \Rep98\Collection\Helpers\Config
	 * @uses config
	 */
	public function testMethods()
	{
		Config::from([
			"logging" => [
				"path" => ROOT_TEST."log".DS
			]
		]);
		$levels = [
			"warning", "error",
			"info", "debug",
			"notice", "critical",
			"alert", "emergency"
		];

		foreach ($levels as $name) {
			Log::{$name}("this file $name.log",["file" => "LogTest.php", "line" => 40]);
			$this->assertFileExists(ROOT_TEST."log".DS.ucwords($name).".log");
			if (file_exists(ROOT_TEST."log".DS.ucwords($name).".log")) {
				 unlink(ROOT_TEST."log".DS.ucwords($name).".log");
			}
		}

	}
}
?>