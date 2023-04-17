<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use Error;
use PHPUnit\Framework\TestCase;
use Doctrine\Inflector\Language;
use Rep98\Collection\Helpers\Str;
use Rep98\Collection\Helpers\Pluralizer;
use Rep98\Collection\Exceptions\StringException;
/**
 * StrTest
 * @covers Rep98\Collection\Helpers\Str
 */
class StrTest extends TestCase
{
	protected $lorem;
	protected function setUp(): void
	{
		$this->lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
	}

	protected function tearDown(): void
	{
		unset($this->lorem);
	}
	/**
	 * @uses voku\helper\ASCII
	 * @uses Rep98\Collection\Helpers\Config
	 * @uses Rep98\Collection\Helpers\Slug
	 * @uses Rep98\Collection\Helpers\Arr
	 * @uses slug
	 */
	public function testStr()
	{
		$this->assertEquals(
			" do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
			Str::afterOf($this->lorem, "sed")
		);

		$this->assertEquals(
			"Dusseldorf",
			Str::ascii('�Düsseldorf�', 'en')
		);

		$this->assertEquals(
			"Lorem ipsum dolor sit amet, consectetur adipiscing elit, ",
			Str::beforeOf($this->lorem, "sed")
		);

		$this->assertEquals(
			"consectetur adipiscing elit,", 
			Str::between($this->lorem, "amet, ", " sed")
		);

		$this->assertEquals(
			"loremIpsum",
			Str::camel("Lorem ipsum")
		);

		$this->assertEquals(
			"Sed Do Eiusmod",
			Str::capitalize("sed do eiusmod")
		);

		$this->assertEquals(
			"@",
			Str::chr(64)
		);

		$this->assertEquals(
			"Lorem Ipsum Dolor Sit Amet", 
			Str::convertCase("Lorem ipsum dolor sit amet")
		);

		$this->assertEquals(
			[
				"public","folder", ""
			],
			Str::explode("/", "public/folder/")
		);

		$this->assertEquals(123, Str::length($this->lorem));

		$this->assertEquals(
			"my name is robert", 
			Str::lower("My Name is ROBERT")
		);
		$this->assertEquals(
			"delfinmundo@gmail.com",
			Str::mask("delfinmundo@gmail.com", "")
		);

		$this->assertEquals(
			"delfinmundo@gmail.com",
			Str::mask("delfinmundo@gmail.com", "*", 21, 10)
		);

		$this->assertEquals(
			"delfinmundo++++++++++",
			Str::mask("delfinmundo@gmail.com", "+", -10)
		);

		$this->assertEquals(
			"de***************.com",
			Str::mask("delfinmundo@gmail.com", "*", 2, 15)
		);

		$this->assertEquals(64, Str::ord("@"));

		$this->assertEquals("__DIR__", Str::pad("DIR", 7, "__"));

		$this->assertEquals(
			"Lorem ipsum sit amet", 
			Str::remove("dolor ", "Lorem ipsum dolor sit amet") );

		$this->assertEquals(
			"My name is Robert",
			Str::replace("{name}", "Robert", "My name is {name}")
		);

		$this->assertEquals(
			"tema tis rolod muspi meroL",
			Str::reverse("Lorem ipsum dolor sit amet")
		);

		$this->assertEquals(
			"tema tis rolod muspi merol",
			Str::reverse("Lorem ipsum dolor sit amet", Str::LOWER_CASE)
		);

		$this->assertEquals(
			"Mgiacroule",
			Str::shuffle("Murcielago", 1500 )
		);

		$this->assertEquals(
			"el-post-de-perez",
			Str::slug("El post de Pérez")
		);

		$this->assertEquals(
			[
				"Lorem_Ipsum_Dolor",
				"lorem_ipsum_dolor",
				"LOREM_IPSUM_DOLOR"
			],
			[
				Str::snake("Lorem ipsum dolor", "_", Str::SNAKE_CAPITAL_CASE),
				Str::snake("Lorem ipsum dolor", "_", Str::SNAKE_LOWER_CASE),
				Str::snake("Lorem ipsum dolor", "_", Str::SNAKE_UPPER_CASE)
			]
		);
		$this->assertEquals(
			"Lorem",
			Str::split($this->lorem, "\s")[0]
		);
		$this->assertEquals(
			57,
			Str::stripos($this->lorem, "sed")
		);

		$this->assertEquals(2, Str::subCount($this->lorem, "dolor"));

		$this->assertEquals(2, Str::subCount([
			0 => 'Lorem ipsum dolor sit amet',
		  	1 => 'consectetur adipiscing elit',
		  	2 => 'sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
		], "dolor"));

		$this->assertEquals(1, Str::subCount("lorem", "lorem"));

		$this->assertEquals(
			"eiusmod",
			Str::substr($this->lorem, 64, 7)
		);

		$this->assertEquals( "13:00", Str::substrReplace("1300", ":", 2, 0) );

		$this->assertEquals(
			"Lorem ipsum maria",
			Str::strtr("Lorem ipsum dolor", ["dolor" => "maria"])
		);

		$this->assertEquals(
			"Lorem ipsum dolor sit amet, consectetur adipiscing elit...",
			Str::truncated($this->lorem, 58)
		);

		$this->assertSame("Hello world!", Str::ucfirst("hello world!"));
		$this->assertSame("Hello World!", Str::ucwords("hello world!"));
		$this->assertSame("hello World!", Str::uncamel("helloWorld!", " "));
		$this->assertEquals(3, Str::wordCount("Robert Pérez"));
		$this->assertEquals("This is...", Str::wordTruncate("This is my name: robert", 2));
		$this->assertEquals("Recorta palabras y separa en linea",Str::wordTruncate("Recorta palabras y separa en linea", 34));
		$this->assertEquals("wordWrap", Str::wordWrap("wordWrap", 5));
		$this->assertEquals("Recorta<br>palabras y<br>separa en<br>linea", 
				Str::wordWrap("Recorta palabras y separa en linea", 10, "<br>"));
		$this->assertEquals("Recorta palabras y separa en linea", 
				Str::wordWrap("Recorta palabras y separa en linea", 37, "<br>"));
	}
	/**
	 * @uses voku\helper\ASCII
	 * @uses Rep98\Collection\Helpers\Json
	 */
	public function testIsStr()
	{
		$this->assertTrue(
			Str::contains(
				$this->lorem, 
				"sed"
			)
		);
		$this->assertFalse(Str::endsWith("public", "/"));

		$this->assertTrue( Str::equal("lorem", "LOREM") );
		$this->assertFalse( Str::equal("lorem", "LOREM", true) );

		$this->assertFalse( Str::isAscii("ú") );

		$this->assertTrue( Str::isJson('{"a":true}') );

		$this->assertTrue(Str::startsWith($this->lorem, "Lorem"));
	}
	/**
	 * @covers Rep98\Collection\Helpers\Pluralizer
	 * @uses Doctrine\Inflector\Inflector
	 * @uses Doctrine\Inflector\Language
	 */
	public function testPluralizes()
	{
		
		$this->assertEquals("headers", Str::plural("header"));
		Pluralizer::useLanguage(Language::SPANISH);
		$this->assertEquals("carro", Str::plural("carro", [1]));
		Pluralizer::useLanguage(Language::ENGLISH);

		$this->assertEquals(
			"header",
			Str::singular("headers")
		);

		$this->assertEquals("ano", Str::unaccent("año") );
	}
	/**
	 * @uses \Rep98\Collection\Exceptions\StringException
	 */
	public function testExceptionConvertCase()
	{
		$this->expectException(StringException::class);
		Str::convertCase("This is my name", 8);
	}
	/**
	 * @uses \Rep98\Collection\Exceptions\StringException
	 */
	public function testExceptionSnake()
	{
		$this->expectException(StringException::class);
		Str::snake("This mode is not valid", "-", Str::LOWER_CASE);
	}

	public function testExceptionConstruct()
	{
		$this->expectException(Error::class);
		new Str();
	}
}
?>