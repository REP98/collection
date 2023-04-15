<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Config;
use Rep98\Collection\Helpers\Log;
use Monolog\Logger;

class functionsTest extends TestCase
{
	/**
	 * @covers config
	 * @covers config_has
	 * @uses Rep98\Collection\Helpers\Log
	 * @uses Rep98\Collection\Helpers\Config
	 */
	public function testConfig()
	{
		Config::from([
			"logging" => [
				"path" => ROOT_TEST."html".DS
			]
		]);
		$this->assertTrue(config("logging.enabled"));
		$this->assertFalse(config_has("logging.noexist"));

		$this->assertTrue(config_has("logging.enabled"));
	}
	/**
	 * @covers _log
	 * @uses Monolog\Logger
	 * @uses Rep98\Collection\Helpers\Log
	 * @uses Rep98\Collection\Helpers\Config
	 * @uses config
	 */
	public function testLog()
	{
		Config::from([
			"logging" => [
				"path" => ROOT_TEST."html".DS
			]
		]);
		$this->assertInstanceOf(
			Logger::class, 
			_log("warning", "test valid", ["file" => "functionsTest.php", "line" => 31])
		);
		$this->assertFileExists(ROOT_TEST."html".DS."Warning.log");
	}
	/**
	 * @covers slug
	 * @uses \Rep98\Collection\Helpers\Slug
	 * @uses \Rep98\Collection\Helpers\Config
	 * @uses \Rep98\Collection\Helpers\Str
	 */
	public function testUrl()
	{
		$this->assertEquals("hello-word", slug("Hello Word"));
	}
	/**
	 * @covers values
	 * @covers value
	 * @uses \Rep98\Collection\Helpers\Value
	 */
	public function testValues()
	{
		$this->assertEquals("MyString", values("MyString"));
		$this->assertEquals("myString", values(function($v){ return $v; }, "myString"));

		$this->assertIsBool(value("true"));
	}
}
?>