<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Json;

/**
 * Config
 */
class Config
{
	private array $config;
	private static self|null $instance = null;
	
	protected function __construct(array $defaultConfig = [])
	{
		$this->config = Arr::from($defaultConfig);
	}

	protected static function I(array $defaultConfig = [])
	{
		if (self::$instance === null) {
			self::$instance = new self($defaultConfig);
		}
		return self::$instance;
	}

	public static function default(array $defaultConfig)
	{
		return self::I($defaultConfig);
	}

	public static function loadSettingsFromJson(string $file)
	{
		$json = Json::loadPath($file);

	 	return new static($json->toArray());
	}

	public static function loadSettingsFromEnv()
	{
		return new static($_ENV);
	}

	public function loadSettingsFromFile(string $file): static|bool
	{
		if (file_exists($file)) {
			$arr = include $file;
			return new static($arr);
		}
		return false;
	}

	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	public function set($key, $value)
	{
		return $this->config->set($key, $value);
	}

}
?>