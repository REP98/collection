<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Config;
use Rep98\Collection\Helpers\Slug;
use Nette\Schema\Schema;

/**
 * SlugTest
 * @covers Rep98\Collection\Helpers\Slug
 * @uses config
 * @uses Rep98\Collection\Helpers\Config
 */
class SlugTest extends TestCase
{
	protected $slug;

	protected function setUp(): void
	{
		$this->slug = new Slug(["delimiter" => "_", "limit" => 10]);
	}

	public function testGetSchema()
	{
		$this->assertInstanceOf(Schema::class, Slug::getSchema());
	}

	/**
	 * @uses \Rep98\Collection\Helpers\Str
	 */
	public function testGenerate()
	{
		$this->assertEquals(
			"hello_word", 
			$this->slug->generator("Hello Word")
		);

		$this->assertEquals(
			"hello_word", 
			$this->slug->generator("Hello word of star Trek")
		);

		$this->assertEquals("", 
			$this->slug->generator("")
		);
	}
}
?>