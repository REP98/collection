<?php
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use League\Config\Configuration;
use Nette\Schema\Expect;
use Rep98\Collection\Helpers\Env;
use Rep98\Collection\Helpers\Log;
use Rep98\Collection\Helpers\Slug;
use Rep98\Collection\Traits\Instances;
/**
 * Config
 */
class Config
{
	use Instances;
	/**
	 * Motor de Configuraciones
	 * @var \League\Config\Configuration
	 */
	protected Configuration $engine;
	/**
	 * Nombre de Clases por defecto a auto cargar
	 * @var array
	 */
	protected array $nameClassDefault = [Log::class, Slug::class, Env::class];
	/**
	 * Squemas por defecto
	 * @var array
	 */
	protected array $defaultSchema = [];

	private function __construct(){
		$this->engine = new Configuration();
	}
	/**
	 * Estabece las clases configurables a autocargar
	 * @param array $nameClass
	 * @return static
	 */
	public static function setDefault(string|array $nameClass)
	{
		self::$instance->nameClassDefault = array_merge(
			self::$instance->nameClassDefault,
			(array) $nameClass
		);

		return self::$instance;
	}
	/**
	 * Establece los Squemas por defecto
	 * @param array $baseSchemas matriz de esquemas
	 * @return this
	 */
	public function setSchema(array $baseSchemas = [])
	{
		if (empty($this->engine)) {
			$this->engine = new Configuration(
				array_merge($this->defaultSchema, $baseSchemas)
			);
		} else {
			foreach ($baseSchemas as $nameSchema => $value) {
				$this->engine->addSchema($nameSchema, $value);
			}
		}
		
		return $this; 
	}
	/**
	 * Establece los esquemas por defecto de las clases de autocarga
	 * @return static
	 */
	public function default()
	{
		foreach ($this->nameClassDefault as $nameSpace) {
			$name = call_user_func_array([$nameSpace, "getNameSchema"], []);
			$schema = call_user_func_array(
				[$nameSpace, "getSchema"], 
				[]
			);
			$this->defaultSchema[$name] = $schema;
			if (!$this->engine->exists($name)) {
				$this->engine->addSchema($name, $schema);
			}
		}

		return $this;
	}
	/**
	 * Inicializa el motor de configuraciones
	 * @param  array  $baseSchemas Esquema de configuraciones iniciales
	 * @return static
	 */
	public static function start(array $baseSchemas = [])
	{
		return self::I()->default()->setSchema($baseSchemas);
	}
	/**
	 * Asigna y/o mezcla los parametros de configuración
	 * @param  array  $data información de configuración
	 * @return static
	 */
	public static function from(array $data = [])
	{
		$i = self::I();
		$i->merge($data);
		return $i;
	}
	/**
	 * Magic
	 */
	public function __call($name, $args)
	{
		if (method_exists($this->engine, $name)) {
			return call_user_func_array([$this->engine, $name], $args);
		}

		throw new CollectionException("Method $name not exist ");
	}

	public function __set($key, $value)
	{
		$this->engine->set($key, $value);
	}

	public function __get($key)
	{
		return $this->engine->get($key);
	}
}
?>