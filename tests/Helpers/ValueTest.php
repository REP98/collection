<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use Rep98\Collection\Helpers\Value;
use PHPUnit\Framework\TestCase;
/**
 * ValueTest
 * @covers \Rep98\Collection\Helpers\Value
 */
class ValueTest extends TestCase
{
	/**
	 * @uses values
	 * @uses Rep98\Collection\Helpers\Json
	 */
	public function testValues()
	{
		$v = Value::create("MyBool");
		$vType = [
			"true",
			"false",
			"11", 
			"1.", 
			"1.6",
			"mystring", 
			"empty", 
			"null",
			"{\"a\":1}",
			[1, "a", '"b"']
		];

		$this->assertInstanceOf(Value::class, $v);

		$this->assertTrue(Value::createOnly("true", "boolean"));

		$this->assertEquals(Value::NONE, Value::createOnly("MyString", "boolean"));
		$this->assertFalse(Value::create("mystring")->is("typenotvalid"));
		$this->assertFalse(Value::create("false")->convert());

		$this->assertNull(Value::create(null)->convert());

		$this->assertEquals(
			[
				true,
				false, 
				11, 
				(float) 1, 
				1.6, 
				"mystring", 
				"", 
				null,
				["a" => 1],
				[1, "a", "b"]
			],
			Value::create($vType)->convertArray()
		);		

	}
}
?>