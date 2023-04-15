<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Serializable;
use Rep98\Collection\Exceptions\SerializeError;

/**
 * SerializableTest
 * @covers Rep98\Collection\Helpers\Serializable
 * @uses Rep98\Collection\Exceptions\SerializeError
 */
class SerializableTest extends TestCase
{
	
	public function testSerialize()
	{
		$this->assertEquals(
			'a:1:{s:4:"user";s:4:"Name";}',
			Serializable::serialize(["user" => "Name"])
		);

		$this->assertEquals(
			["user" => "Name"],
			Serializable::unserialize('a:1:{s:4:"user";s:4:"Name";}')
		);

		$this->assertTrue(Serializable::valid('s:37:"Rep98\Collection\Helpers\Serializable";'));
		$this->assertTrue(Serializable::valid('b:1;'));
		$this->assertTrue(Serializable::valid('i:1;'));
		$this->assertTrue(Serializable::valid('d:1.1;'));
		$this->assertTrue(Serializable::valid('N;'));
		$this->assertTrue(Serializable::valid('a:1:{s:4:"user";s:4:"name";}'));
		$this->assertTrue(Serializable::valid('O:37:"Rep98\Collection\Helpers\Serializable":0:{}'));

		$this->assertFalse(Serializable::valid('s:37:Rep98\Collection\Helpers\Serializable;', false));
		$this->assertFalse(Serializable::valid('a:1'));
		$this->assertTrue(Serializable::valid('a:1:{s:4:"user";s:4:"Name";}', false));
		$this->assertFalse(Serializable::valid('a:1:', false));
		$this->assertFalse(Serializable::valid('a1:', false));
		$this->assertFalse(Serializable::valid('a1:{}', false));
		$this->assertFalse(Serializable::valid('a:;1:{s:4:"user";s:4:"Name";}', false));
		$this->assertFalse(Serializable::valid('a:}1:{s:4:"user";s:4:"Name";}', false));

		$this->expectException(SerializeError::class);
		Serializable::unserialize('a:1:');
	}
}
?>