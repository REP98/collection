<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Dotenv\Dotenv;
use Nette\Schema\Schema;
use Nette\Schema\Expect;
use Rep98\Collection\Exceptions\MissingConfigException;
use Rep98\Collection\Helpers\TypeData;
use Rep98\Collection\Helpers\Config;
use Rep98\Collection\Interface\Configurable;
use Rep98\Collection\Traits\Instances;
/**
 * Env
 */
class Env implements Configurable
{
	use Instances;

	private array $_env = [];
	protected Schema $schema;

	public static function getSchema(): Schema
	{
		return self::I()->getDefault();
	}
	
	public static function getNameSchema(): string
	{
		return "env";
	}

	public function __construct(
		?string $path = null
	)
	{
		if (is_null($path)) {
			if (defined("ROOT_ENV")) {
				$path = ROOT_ENV;
			} else if(defined("CONFIG_PATH")) {
				$path = CONFIG_PATH;
			}			
		}

		$this->schema = Expect::structure([]);
		$dotenv = Dotenv::createImmutable($path);
		$dotenv->safeLoad();

		$config = [];

		if (!empty($_ENV)) {
			foreach ($_ENV as $key => $value) {
				$config[strtolower($key)] = value($value);
			}
			$td = new TypeData($config);
			$this->_env = $td->get();
			$this->schema->merge($this->_env['schema'], $this->schema);
			Config::from($this->_env['data']);
		}

	}

	public function getDefault()
	{
		return $this->schema;
	}

	#[NotReturn]
	public function setDefaultSchema(Schema $default)
	{
		$this->schema->merge($default, $this->schema);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __call($name, $args)
	{
		if (in_array($name, ['has', 'get', 'all'])) {
			$a = Arr::from($this->_env['data']);
			return $name == "all" ?
				$a->all() :
				call_user_func_array([$a, $name], $args);
		}
		throw new CollectionException("Method $name not exist in ". Env::class);
	}
}
?>