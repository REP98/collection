<?php 
declare(strict_types=1);
namespace Rep98\Collection\Test\Helpers;


use PHPUnit\Framework\TestCase;
use Rep98\Collection\Helpers\Config;
use Nette\Schema\Expect;

/**
 * ConfigTest
 * @covers \Rep98\Collection\Helpers\Config
 * @uses Rep98\Collection\Helpers\Log
 */
class ConfigTest extends TestCase
{
	protected function setUp(): void
    {
		if (!defined("ROOT_ENV")) {
			define("ROOT_ENV", PATH_FILE."config");
		}
    }

    protected function tearDown(): void
    {
    	
    }

	public function testConfig()
	{
		$c = Config::default("database", Expect::structure([
		    'driver' => Expect::anyOf('mysql', 'postgresql', 'sqlite')->required(),
	        'host' => Expect::string()->default('localhost'),
	        'port' => Expect::int()->min(1)->max(65535),
	        'ssl' => Expect::bool(),
	        'database' => Expect::string()->required(),
	        'username' => Expect::string()->required(),
	        'password' => Expect::string()->nullable()
		]));

		$userProvidedValues = [
		    'database' => [
		        'driver' => 'mysql',
		        'port' => 3306,
		        'host' => 'localhost',
		        'database' => 'myapp',
		        'username' => 'myappdotcom',
		        'password' => 'hunter2',
		    ],
		    'logging' => [
		        'path' => ROOT_TEST."html".DS,
		    ]
		];

		$config = Config::from($userProvidedValues);

		$this->assertEquals(
			ROOT_TEST."html".DS,
			$config->get('logging.path')
		);

		$config->set("database.password", "startrek1960");

		$this->assertEquals(
			'startrek1960',
			$config->get("database.password")
		);

		$config->database_username = "rep98@github.com";

		$this->assertEquals("rep98@github.com", $config->database_username);

		$this->assertIsArray($config->database);
	}

	public function testConfigSingleton()
	{
		$c = Config::I();

		$this->assertInstanceOf(Config::class, $c);
	}
	/**
	 * @uses Rep98\Collection\Helpers\Json
	 */
	public function testFileJson()
	{
		Config::default(
			[
				"database" => Expect::structure([
				    'driver' => Expect::anyOf('mysql', 'postgresql', 'sqlite')->required(),
			        'host' => Expect::string()->default('localhost'),
			        'port' => Expect::int()->min(1)->max(65535),
			        'ssl' => Expect::bool(),
			        'database' => Expect::string()->required(),
			        'username' => Expect::string()->required(),
			        'password' => Expect::string()->nullable()
				])
			]
		);

		$c = Config::loadSettingsFromJson(PATH_FILE."config".DS."settings.json");

		$this->assertEquals(
			"mysql",
			$c->get("database.driver")
		);
	}
	/**
	 * @uses \Rep98\Collection\Helpers\Arr
	 */
	public function testFilePhp()
	{
		Config::default(
			[
				"database" => Expect::structure([
				    'driver' => Expect::anyOf('mysql', 'postgresql', 'sqlite')->required(),
			        'host' => Expect::string()->default('localhost'),
			        'port' => Expect::int()->min(1)->max(65535),
			        'ssl' => Expect::bool(),
			        'database' => Expect::string()->required(),
			        'username' => Expect::string()->required(),
			        'password' => Expect::string()->nullable()
				])
			]
		);

		$c = Config::loadSettingsFromFile(PATH_FILE."config".DS."settings.php");

		$this->assertEquals(
			"mysql",
			$c->get("database.driver")
		);
	}
	/**
	 * @uses Dotenv\Dotenv
	 *
	public function testFileEnv()
	{
		Config::default(
			[
				"database" => Expect::structure([
				    'driver' => Expect::anyOf('mysql', 'postgresql', 'sqlite')->required(),
			        'host' => Expect::string()->default('localhost'),
			        'port' => Expect::int()->min(1)->max(65535),
			        'ssl' => Expect::bool(),
			        'database' => Expect::string()->required(),
			        'username' => Expect::string()->required(),
			        'password' => Expect::string()->nullable()
				])
			]
		);

		$c = Config::loadSettingsFromEnv("database");

		$this->assertEquals(
			"mysql",
			$c->get("database.driver")
		);
	} */
}
?>