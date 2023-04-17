<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;


use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Json;
use Rep98\Collection\Exceptions\FileNotFound;
use Rep98\Collection\Exceptions\JsonError;

/**
 * JsonTest
 * @covers Rep98\Collection\Helpers\Json
 */
final class JsonTest extends TestCase
{
	/** @uses \Rep98\Collection\Exceptions\FileNotFound */
	public function testLoadFile()
	{
		$this->assertEquals([
			"test" => "case",
			"php" => "unit",
			"rep" => 98,
			"has" => true
		], Json::loadPath(PATH_FILE."debug.json"));	

		if (file_exists(PATH_FILE."debug-copy.json")) {
			unlink(PATH_FILE."debug-copy.json");
		}

		Json::write([
			"test" => "case",
			"php" => "unit",
			"rep" => 98,
			"has" => true
		], PATH_FILE."debug-copy.json");

		$this->assertJsonFileEqualsJsonFile(
			PATH_FILE."debug.json",
			PATH_FILE."debug-copy.json"
		);

		$this->expectException(FileNotFound::class);
		Json::loadPath("file-not-found.json");
	}

	/** @uses \Rep98\Collection\Exceptions\JsonError  */
	public function testCode()
	{
		$this->assertIsArray(Json::decode('{"username":"rep98"}'));
		$this->assertEquals('{"username":"rep98"}', Json::encode(["username" => "rep98"]));
		Json::$returnMessage = true;
		$this->assertEquals([4 => 'Syntax error'], Json::decode('{ bar: "baz", }'));

		Json::$returnMessage = false;
		$this->expectException(JsonError::class);
		Json::decode('{ bar: "baz", }');		
	}

	/** @uses \Rep98\Collection\Helpers\Arr */
	public function testConverter()
	{
		$newArr = Json::collection('{"username":"rep98"}');
		$this->assertInstanceOf(Arr::class, $newArr);

		$this->assertTrue(Json::valid('{"username":"rep98"}'));
		$this->assertFalse(Json::valid('rep98'));
		$this->assertFalse(Json::valid(null));

		$this->assertInstanceOf(Json::class, Json::object('{"username":"rep98"}'));

	}

	/** @uses \Rep98\Collection\Helpers\Serializable */
	public function testSerialize()
	{
		$this->assertTrue(
			is_string(Json::serialize('{"username":"rep98"}'))		
		);

		$this->assertStringContainsString(
			'{"username":"rep98"}',
			Json::unserialize('s:20:"{"username":"rep98"}";')			
		);
	}

	/** @uses \Rep98\Collection\Helpers\Arr */
	public function testObject()
	{
		$o = Json::object('{}');
		$o->username = "rep98";
		$this->assertEquals("rep98", $o->username);

		$this->assertEquals("{}", $o->getOrigin());

		$this->assertIsArray($o->getData());

		$this->assertEquals(["username" => "rep98"], $o->getData());
		$this->assertFalse(Json::valid(null));
	}
	/*
	public function testHeaderJson()
	{
		$input = [
			"test" => "case",
			"php" => "unit",
			"rep" => 98,
			"has" => true
		];

		Json::header(true);
		Json::response($input);
		header_remove();

		$this->assertEquals(200, http_response_code());
	} */
}
?>