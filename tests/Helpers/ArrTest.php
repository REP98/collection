<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Exceptions\FileNotFound;

/**
 * ArrTest
 * @covers Rep98\Collection\Helpers\Arr
 */
final class ArrTest extends TestCase
{	

	public function testStatic()
	{
		// From
		$newArr = new Arr([1]);
		$this->assertInstanceOf(Arr::class, Arr::from([]));
		$a = Arr::from([1]);
		$this->assertInstanceOf(Arr::class, Arr::from($a));
		$this->assertEquals(
			$newArr->all(),
			$a->all()
		);
		// Fill
		$this->assertEquals([0=>"text", 1=> "text",2=> "text"], Arr::fill(0, "text", 3));
		$this->assertEquals(["has"=>true, "have"=>true], Arr::fill(["has", "have"], true));
		// Wrap
		$this->assertEquals([], Arr::wrap());
		$this->assertEquals(["test"], Arr::wrap("test"));
		$this->assertEquals(["test1"], Arr::wrap(["test1"]));
		$this->assertEquals([1], Arr::wrap($a));
	}

	public function testLoadFile()
	{
		$a = Arr::loadPath(PATH_FILE."debugArr.php");
		$this->assertEquals([
			"test" => "case",
			"php" => "unit",
			"rep" => 98,
			"has" => true
		], $a->all());

		$this->assertFalse(Arr::loadPath(PATH_FILE."debugArr1.php"));

		$this->expectException(FileNotFound::class);
		Arr::loadPath("file-not-found.php");
	}

	public function testIsValid()
	{
		$this->assertTrue(Arr::isAssoc(["key" => "Value"]));
		$this->assertTrue(Arr::isList(["Value"]));
	}

	public function testObjectArr()
	{
		$array = Arr::from();

		$this->assertArrayHasKey("test", $array->add("test", "some")->all());
		
		$this->assertNotContains("jajaja", $array->add("test", "jajaja")->all());
		
		$this->assertArrayHasKey(0, $array->set(null,10)->all());

		$this->assertArrayHasKey("phpunit", $array->set("phpunit.test", "case")->all());
		$array->offsetSet("lang", "php");
		$this->assertArrayHasKey("lang", $array->get());

		$this->assertEquals(
			[
				"test" => "some",
				0 => 10,
				"phpunit" => [
					"test" => "case"
				],
				"lang" => "php"
			],
			$array->get()
		);

		$this->assertEquals($array["phpunit"]["test"], $array->get("phpunit.test", null));
		$this->assertIsArray($array->get("phpunit"));

		$this->assertFalse($array->exists(1.3, true));

		$this->assertTrue($array->contains("some"));

		$this->assertFalse($array->has([]));
		$this->assertTrue($array->has("test"));
		$this->assertTrue($array->has("phpunit.test"));
		$this->assertFalse($array->has("phpunit.t"));

		$this->assertArrayNotHasKey(0, $array->except(0));

		$this->assertEquals(
			[
				0 => [0 => "some"],
				1 => [0 => ["test" => "case"]],
				2 => [0 => "php"]
			],
			$array->chunk(1)
		);

		$this->assertIsArray($array->shuffle());
		$this->assertIsArray($array->shuffle(5));

	}

	public function testUnset()
	{
		$a = Arr::from([1,2,3]);
		$a->offsetUnset(1);
		$this->assertCount(2, $a->get());
	}

	public function testColumn()
	{
		$arr = Arr::from([
			["id"=>512, "name" => "jhon doe"],
			["id"=>513, "name" => "pedro perez"]
		]);

		$this->assertEquals(
			[
				512 => "jhon doe",
				513 => "pedro perez"
			],
			$arr->column("name", "id")
		);
	}

	public function testCombineAndDivide()
	{
		$arr = Arr::from(['green', 'red', 'yellow']);
		$this->assertEquals(
			[
				"green" => "avocado",
				"red" => "apple",
				"yellow" => "banana"
			],
			$arr->combine(['avocado', 'apple', 'banana'])
		);

		$arr = Arr::from($arr->combine(['avocado', 'apple', 'banana']));

		$this->assertEquals(
			[
				['green', 'red', 'yellow'],
				['avocado', 'apple', 'banana']
			],
			$arr->divide()
		);
	}

	public function testCount()
	{
		$arr = Arr::from([1, "hello", 1, "world", "hello"]);

		$this->assertEquals(5, $arr->count());
		$this->assertEquals([
			1 => 2,
			"hello" => 2,
			"world" => 1
		], $arr->count(true));
	}

	public function testDiff()
	{
		$arr = Arr::from(["a" => "green", "b" => "brown", "c" => "blue", "red"]);
		$dif = ["a" => "green", "yellow", "red"];
		
		$this->assertEquals([
			'b' => 'brown',
			'c' => 'blue'
		], $arr->diff($dif));

		$this->assertEquals([
			'a' => 'green',
  			'b' => 'brown',
  			'c' => 'blue',
  			0 => 'red'
		], $arr->diff(Arr::DIFF_ASSOC, $dif));

		$this->assertEquals([
			'a' => 'green',
  			'b' => 'brown',
  			'c' => 'blue',
  			0 => 'red'
		], $arr->diff(Arr::DIFF_KEY, $dif));

		// UPDATE ARR
		
		$arr = Arr::from(['blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4]);
		$dif = ['green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8];
		$a = $arr->diff(Arr::DIFF_UKEY, function($k1, $k2) {
			if ($k1 == $k2) {
				return 0;
			}
			return $k1 > $k2 ? 1 : -1;
		}, $dif);

		$this->assertEquals([
			'red'  => 2,
			'purple' => 4
		], $a);

		$b = $arr->diff(Arr::DIFF_UASSOC, function($k1, $k2) {
			if ($k1 == $k2) {
				return 0;
			}
			return $k1 > $k2 ? 1 : -1;
		}, $dif);

		$this->assertEquals([
			'blue' => 1,
			'red' =>  2,
			'green' => 3,
			'purple' => 4
		], $b);

		$this->assertEquals([], $arr->diff("diff_no_found", $dif));
	}

	public function testMergeAndSort()
	{
		$arr = Arr::from(["html", "css"]);

		$this->assertEquals(["html","css", "js"], 
			$arr->merge(["js"]));

		$this->assertEquals(["html","css", "js", "php", "sql"], 
			$arr->merge(["js"], ["php", "sql"]));

		$arr = Arr::from(["color" => array("favorite" => "red"), 5]);
		$arr2 = [
			10, 
			"color" => ["favorite" => "green", "blue"]
		];

		$this->assertEquals([
			'color' => [
				'favorite' => [
					'red', 'green'
				],
				0 => 'blue'
			],
			0 => 5,
			1 => 10
			],
			$arr->mergeRecursive($arr2)
		);
		// UPDATE ARR
		$arr = Arr::from([
			"system" => [
				"lang" => "en-US",
				"theme" => "auto"
			],
			"debug" => false
		]);
		$arr2 = [
			"system" => [
				"lang" => "es-VE"
			],
			"debug" => true
		];
		$a2 = Arr::from($arr->extend($arr2));
		$this->assertEquals(
			"es-VE",
			$a2->get('system.lang')
		);

		$this->assertEquals(
			"es-VE",
			$a2->get(['system','lang'])
		);
		$s = new class extends Arr {
			public function testDots()
			{
				return $this->dots(1.1);
			}
		};

		$this->assertTrue(
			Arr::from(["1.0" => "hello", "1.1" => "world" ])
				->has($s->testDots())
		);
	}
	public function testFilter()
	{
		$a1 = Arr::from(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
		$filter1 = $a1->filter(function($a, $b) {
			return $a & 1;
		});

		$this->assertEquals(
			[
				'a' => 1,
				'c' => 3,
				'e' => 5
			],
			$filter1
		);

		$a2 = Arr::from([
			0=>"not_null",
			1 => null,
		]);

		$this->assertEquals([ 0 => "not_null"], $a2->filterNotNull());
	}

	public function testFlip()
	{
		$arr = Arr::from([
			"fruta" =>"mango",
			"color" => "rojo",
		]);

		$this->assertEquals(
			[
				"mango" =>"fruta",
				"rojo" => "color",
			], 
			$arr->flip());
	}

	public function testFirstLastKeys()
	{
		$arr = Arr::from([
			"fruta" =>"mango",
			"color" => "rojo",
		]);

		$this->assertEquals("mango", $arr->first());
		$this->assertEquals("rojo", $arr->last());
		$this->assertEquals(["fruta", "color"], $arr->keys());
	}

	public function testMapSearch()
	{
		$arr = Arr::from([1, 2, 3, 4, 5]);

		$this->assertContains(8, 
			$arr->map(function ($n)
			{
				 return ($n * $n * $n);
			})
		);

		$arr2 = Arr::from([1 => "one", 2=> "two", 3 => "there"]);
		$nMap = $arr2->map(function ($n, $m)
		{
			 return [$n => $m];
		});
		$this->assertArrayHasKey("one", $nMap[1]);

		$this->assertEquals(
			[
				1 => ["one", 1],
				2 => ["two", 2],
				3 => ["there", 3]
			],
			$arr2->map()
		);
		$this->assertEquals([1], $arr2->searchKeyByValue("one"));
		$this->assertEquals(2, $arr2->search("two"));
		$this->assertArrayNotHasKey(2, $arr2->only([1,3]));

		$i = $arr2->intersect([1 => "one"]);
		$this->assertInstanceOf(Arr::class, $i);
		$this->assertEquals([1 => "one"], $i->get());

		$this->assertJsonStringEqualsJsonString(
			json_encode([1 => "one", 2=> "two", 3 => "there"]),
			$arr2->toJson()
		);

		$this->assertFalse($arr2->isEmpty());
	}

	public function testJoinPadPod()
	{
		$this->assertEquals(
			'lastname, email, phone',
			Arr::from(['lastname', 'email', 'phone'])->join(", ")
		);

		$this->assertEmpty(
			Arr::from()->join(", ")
		);

		$this->assertEquals("oneKey", Arr::from(["oneKey"])->join(", "));

		$this->assertEquals(
			'lastname, email and phone',
			Arr::from(['lastname', 'email', 'phone'])->join(", ", ' and ')
		);

		$this->assertEquals(
			[12, 10, 1, 0], 
			Arr::from([12, 10, 1])->pad(4, 0)
		);

		$arr = Arr::from([12, 10, 1]);
		$arr->push(0);

		$this->assertEquals(
			[12, 10, 1, 0], 
			$arr->get()
		);

		$arr2 = Arr::from([12, 10, 1]);
		$i = $arr2->pull(2);

		$this->assertEquals(1, $i);
		$this->assertEquals([12, 10], $arr2->get());
	}

	public function testDomMode()
	{
		$this->assertEquals(
			["apple", "orange", "banana"],
			Arr::from(["orange", "banana"])->prepend("apple")->all()
		);

		$this->assertEquals(
			["orange", "banana", 'manzana' => 'apple'],
			Arr::from(["orange", "banana"])->prepend("apple", "manzana")->all()
		);		
	}

	public function testRemove()
	{
		$this->assertEmpty(Arr::from([12, 10, 1])->remove()->all());

		$this->assertEquals(
			[12, 10, 1],
			Arr::from([12, 10, 1])->remove([])->all()
		);

		$this->assertEquals(
			[12, 10, 1],
			Arr::from([12, 10, 1])->remove([])->all()
		);
		$a = Arr::from([
				"color" => ["red" => "#f00", "blue" =>"#0f0"]
			]);
		$whithoutBlue = $a->remove("color.blue");
		$this->assertEquals("NONE",
			$whithoutBlue->get("color.blue", "NONE")
		);

		$this->assertEquals(
			["color" => ["red" => "#f00", "blue" =>"#0f0"]],
			Arr::from(["color" => ["red" => "#f00", "blue" =>"#0f0"]])
				->remove("color.yellow")->all()
		);
		
	}

	public function testReplaceReverseUnique()
	{
		$this->assertEquals(
			["uva", "plátano", "manzana", "frambuesa", "cereza"],
			 Arr::from(["naranja", "plátano", "manzana", "frambuesa"])
	          ->replace(
	               [0 => "piña", 4 => "cereza"],
	               [0 => "uva"]
	          )
		);

		$this->assertEquals(
			[
		        'cítricos' => ['piña'],
		        'bayas' => ["arándano", "frambuesa"]
		     ],
			Arr::from([
		          'cítricos' =>  ["naranja"], 
		          'bayas' => ["mora", "frambuesa"]
		     ])->replace(true, [
		          'cítricos' => ['piña'], 
		          'bayas' => ['arándano']
		     ])
		);

		$this->assertEquals(
			["frambuesa", "manzana", "plátano", "naranja"],
			Arr::from(["naranja", "plátano", "manzana", "frambuesa"])
				->reverse(false)
		);

		$this->assertEquals(
			[0 => "hello", 5 => "world"],
			Arr::from(["hello"])
          		->union([5 => "world"])->all()
		);

		$this->assertCount(3, 
			Arr::from(["a" => "verde", "rojo", "b" => "verde", "azul", "rojo"])
				->unique()->all()
		);

		$this->assertEquals(["XL", "gold"],
			Arr::from(["size" => "XL", "color" => "gold"])
				->values()
		);
		$this->assertEquals(
			"username=myuser%40domain.com&alias=jhon%20doe",
			Arr::from(["username" => "myuser@domain.com", "alias" => "jhon doe"])
          		->query()
		);
	}

	public function testSort()
	{
		$a = Arr::from([
			'a' => 4, 'b' => 8, 'c' => -1, 'd' => -9, 
			'e' => 2, 'f' => 5, 'g' => 3, 'h' => -4
		]);

		$this->assertEquals(
			[
				'd' => -9,
				'h' => -4,
				'c' => -1,
				'e' =>  2,
				'g' =>  3,
				'a' =>  4,
				'f' =>  5,
				'b' =>  8
			],
			$a->sort()->all()
		);

		$this->assertEquals(
			[
				'b' =>  8,
				'f' =>  5,
				'a' =>  4,
				'g' =>  3,
				'e' =>  2,
				'c' => -1,
				'h' => -4,
				'd' => -9
			],
			$a->sort(function($k, $v) {
				if ($k == $v) {
					return 0;
				}

				return $k > $v ? -1 : 1;
			})->all()
		);

		$this->assertEquals(
			[
				'b' =>  8,
				'f' =>  5,
				'a' =>  4,
				'g' =>  3,
				'e' =>  2,
				'c' => -1,
				'h' => -4,
				'd' => -9
			],
			$a->sortDesc()->all()
		);

		$this->assertEquals(
			[
				'a' =>  4,
				'b' =>  8,
				'c' => -1,
				'd' => -9,
				'e' =>  2,
				'f' =>  5,
				'g' =>  3,
				'h' => -4
			],
			$a->sortKeys()->all()
		);

		$this->assertEquals(
			[
				'h' => -4,
				'g' =>  3,
				'f' =>  5,
				'e' =>  2,
				'd' => -9,
				'c' => -1,
				'b' =>  8,
				'a' =>  4
			],
			$a->sortKeysDesc()->all()
		);

		$ab = Arr::from([
			"Víctor" => 1, 
			"la Tierra" => 2, 
			"una manzana" => 3, 
			"un plátano" => 4
		]);

		$this->assertEquals(
			[
				'una manzana' 	=> 3,
				'un plátano' 	=> 4,
				'la Tierra' 	=> 2,
				'Víctor' 		=> 1
			],
			$ab->sortKeysUsing(function($a, $b){
				$a = preg_replace('@^(un|una|la) @', '', $a);
			    $b = preg_replace('@^(un|una|la) @', '', $b);
			    return strcasecmp($a, $b);
			})->all()
		);
		
	}

	// MAGIG
	public function testMaggic()
	{
		$a = Arr::from();
		$a->color = "red";

		$this->assertEquals(
			"red",
			$a->color
		);

		$this->assertEquals('{"color":"red"}', "$a");
	}
}
?>