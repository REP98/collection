<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Md;
use Rep98\Collection\Exceptions\FileNotFound;

/**
 * MdTest
 * @covers Rep98\Collection\Helpers\Md
 */
final class MdTest extends TestCase
{
	public function testMarkdown()
	{
		$this->assertEquals(
			'<h1>Hello World</h1>',
			trim(Md::render("# Hello World"))
		);

		$this->assertEquals(
			"<strong>Bold</strong>",
			trim(Md::inline("**Bold**"))
		);

		$this->assertIsString(
			Md::load(PATH_FILE."debug.md")
		);

		$this->expectException(FileNotFound::class);
		Md::load("file-not-found.md");
	}
}
?>