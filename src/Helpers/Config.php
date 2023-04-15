<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Json;
use Rep98\Collection\Helpers\Log;
use Rep98\Collection\Helpers\Slug;
use League\Config\Configuration;
use Nette\Schema\Schema;
use Nette\Schema\Expect;
use Rep98\Collection\Exceptions\MissingConfigException;
use Dotenv\Dotenv;

/**
 * Config
 */
class Config
{
	private Configuration|null $config = null;
	private static self|null $instance = null;
	
	protected function __construct()
	{
		$default = [
			"app" => Expect::structure([
				"debug" => Expect::bool()->default(true)
			]),
			"logging" => Log::getSchema(),
			"slug" => Slug::getSchema()
		];

		$this->config = new Configuration($default);
	}
	/**
	 * Cargador de Instancia de Configuraciones
	 */
	public static function I(): Config
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Establece las configuraciones por defecto a utilizar
	 * @param  array|string  $name   Nombre de la Configuraciones
	 * @param  Schema $defaultConfig Esquema de configuraciones a establecer por defecto
	 * @return Config
	 */
	public static function default(string|array $name, Schema $defaultConfig = null): Config
	{
		if (is_array($name)) {
			$c = self::I();
			foreach ($name as $key => $value) {
				if ($value instanceof Schema) {
					$c->setSchema($key, $value);
				}
			}
			return  $c;
		}
		
		return self::I()->setSchema($name, $defaultConfig);
	}
	/**
	 * Establece la configuraciones actuales
	 * @param  array  $configure Matriz de configuraciones
	 * @return Config
	 */
	public static function from(array $configure): Config
	{
		self::I();
		self::$instance->config->merge($configure);
		return self::$instance;
	}
	/**
	 * Carga configuraciones desde un archivo `Json`
	 * @param  string $file Ruta de archivos `Json`
	 * @return Config
	 */
	public static function loadSettingsFromJson(string $file): Config
	{
		$json = Json::loadPath($file);
	 	return self::from($json);
	}
	/**
	 * Permite cargar configuracione desde una archivo de variable de entorno
	 * @param  string $dir ruta del .env
	 * @return Config      
	 */
	public static function loadSettingsFromEnv(string $name, string $dir = null): Config
	{
		if (is_null($dir) && defined("ROOT_ENV")) {
			$dir = ROOT_ENV;
		}
		$dotenv = Dotenv::createImmutable($dir);
		$dotenv->safeLoad();

		$config = [];

		if (!empty($_ENV)) {
			foreach ($_ENV as $key => $value) {
				$config[strtolower($key)] = value($value);
			}
		}
		
		return self::from([$name => $config]);
	}
	/**
	 * Carga configuraciones desde un archivo `PHP`
	 * @param  string $file Ruta del Archivo `PHP`
	 * @return Config
	 */
	public static function loadSettingsFromFile(string $file): Config
	{
		return self::from(Arr::loadPath($file)->all());
	}
	/**
	 * Obtiene el valor de la configuración dada.
	 * @param  mixed      $key     La clave a buscar, soporta la notación de puntos (.)
	 * @param  mixed      $default EL valor por defecto en caso de que no exista
	 * @return mixed
	 */
	public function get(mixed $key, mixed $default = null): mixed
	{
		try {
			return $this->config->get($key);
		} catch (MissingConfigException $e) {
			if (self::exists("app.debug")) {
				_log("warning", $e->getMessage());
			}
			return $default;
		}		
	}
	/**
	 * Establece una configuración
	 * @param mixed $key   La clave
	 * @param mixed $value El valor
	 * @return Config
	 */
	public function set(mixed $key, mixed $value): Config
	{
		$this->config->set($key, $value);
		return $this;
	}
	/**
	 * Verifica si una clave de configuración existe
	 * @param  mixed  $key la clave
	 * @return bool
	 */
	public function exists(mixed $key): bool
	{
		try {
			return $this->config->exists($key);
		} catch (MissingConfigException $e) {
			return false;
		}
	}
	/**
	 * Permite Establecer un esquema de configuraciones
	 * @param string $key    El nombre del esquema
	 * @param Schema $squema El Esquema
	 * @return Config
	 */
	public function setSchema(string $key, Schema $schema): Config
	{
		$this->config->addSchema($key, $schema);
		return $this;
	}
	// Magic
	public function __get(mixed $key)
	{
		return $this->get(
			$this->propertyDinamic($key), 
			null
		);
	}

	public function __set(mixed $key, mixed $value)
	{
		$this->set(
			$this->propertyDinamic($key), 
			$value
		);
	}

	private function propertyDinamic(string $propertyName): string
	{
		if (str_contains($propertyName, "_")) {
			$p = str_replace("_", ".", $propertyName);
			if ($this->exists($p)) {
				return $p;
			}
		}
		return $propertyName;
	}

	public function __destruct()
	{
		$this->config = null;
		self::$instance = null;
	}
}
?>