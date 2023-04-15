<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Config;
use Rep98\Collection\Helpers\Url;
use Nette\Schema\Schema;
use League\Uri\Uri;

/**
 * UrlTest
 * @covers Rep98\Collection\Helpers\Url
 */
class UrlTest extends TestCase
{

	public function testGetSchema()
	{
		$this->assertInstanceOf(Schema::class, Url::getSchema());
	}
	/**
	public function testUrlCurrent()
	{
		// $this->assertInstanceOf(Uri::class, Url::current());
		//$this->assertEquals("localhost", Url::current());
	}
	*/
}
?>