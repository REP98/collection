<?php 
declare(strict_types=1);

use Monolog\ErrorHandler;
use Monolog\Logger;
use Rep98\Collection\Exceptions\MissingConfigException;
use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Config;
use Rep98\Collection\Helpers\Env;
use Rep98\Collection\Helpers\Log;
use Rep98\Collection\Helpers\Json;
use Rep98\Collection\Helpers\Slug;
use Rep98\Collection\Helpers\Str;
use Rep98\Collection\Helpers\TypeData;
use Rep98\Collection\Helpers\Value;

if (!defined("DS")) {
	define("DS", DIRECTORY_SEPARATOR);
}

// CONFIGURATION
if (! function_exists('config')) {
	/**
	 * Ayudante de configuraciones
	 * @param  mixed $key     La clave de configuración a buscar
	 * @param  mixed $default Valor por defecto en caso de que no exista la clave
	 * @return mixed
	 */
	function config($key = null, $default = null): mixed
	{
		return Config::I()->get($key) ?? $default;
	}
}

if (! function_exists("config_has")) {
	/**
	 * Valida si una configuración existe
	 * @param  mixed  $key La clave
	 * @return bool
	 */
	function config_has(mixed $key): bool
	{
		return Config::I()->exists($key);
	}
}

if (!function_exists("env")) {
	/**
	 * Obtene las configuraciones de los archivos .env
	 * @param  string  $key     La clave
	 * @param  mixed $default Valor por defecto
	 * @return mixed
	 */
	function env($key, $default = false)
	{
		return Env::I()->get($key, $default);
	}
}

if (!function_exists("getConfigPath")) {
	/**
	 * Obtiene las configuraciones de una carpeta
	 * @param  string $path ruta
	 * @return array<Schema, array>
	 */
	function getConfigPath(string $path)
	{
		$baseConfig = [];
		$configure = [];
		if (is_dir($path)) {
			$dir = scandir($path);
			$dir = array_diff($dir, ['.', '..']);
			foreach ($dir as $file) {
				if (is_file($path.DS.$file)) {
					if (Str::endsWith($file, ".php") || Str::endsWith($file, ".json")) {
						$data = [];
						if (Str::endsWith($file, ".php")) {
							$data = Arr::loadPath($path.DS.$file)->all();
						} else if (Str::endsWith($file, ".json")){
							$data = Json::loadPath($path.DS.$file);
						}
						$td = new TypeData($data);
						$s = $td->get();
						$baseConfig[pathinfo($path.DS.$file, PATHINFO_FILENAME)] = $s['schema'];
						$configure[pathinfo($path.DS.$file, PATHINFO_FILENAME)] = $s['data'];
					}					
				}
			}
		}
		return [
			"schema" => $baseConfig,
			"data" => $configure
		];
	}
}

// URL
if(! function_exists('slug') ){
	/**
	 * Genera una Url amigable de una cadena de texto
	 * @param  string $slug    La cadena de texto
	 * @param  array  $options Opciones de configuración
	 * @return string
	 */
	function slug(string $slug, array $options = []): string
	{
		$s = new Slug($options);
		return $s->generator($slug);
	}
}

// LOG

if (!function_exists('_log')) {
	/**
	 * Almacenamiento de registro
	 * @param  string $level   Tipo de registro, error, notice, warning
	 * @param  string $message Mensaje
	 * @param  array  $context Contexto
	 * @return void
	 */
	function _log(string $level, string $message, array $context = []): Logger
	{
		return Log::{$level}($message, $context);
	}
}

// UTILS
if (!function_exists('values')) {
	/**
	 * Verfica si el valor es una función 
	 * @see https://laravel.com/docs/10.x/helpers#method-value
	 * @param  mixed $value el valor
	 * @return mixed
	 */
	function values($value, ...$args): mixed
	{
		return $value instanceof Closure ? $value(...$args) : $value;
	}
}

if (! function_exists("value")) {
	/**
	 * Evalue y establece el valor correcto
	 * @param  mixed      $value   el valor a analizar
	 * @param  mixed 	  $default valor por defecto en caso de no existir
	 * @return mixed
	 */
	function value(mixed $value, mixed $default = null): mixed
	{
		$v = Value::create($value);
		return is_array($value) ? $v->convertArray($default) : $v->convert($default);
	}
}

// Debugs
if (!function_exists("dump")) {

	#[NotReturn]
	function dump(...$args)
	{
		var_dump(...$args);
	}
}

if (!function_exists("dd")) {
	
	#[NotReturn]
	function dd(...$args) {
		die(dump(...$args));
	}
}
/**
 * Inicializamos las configuraciones y el registro log
 */
if (!defined("CONFIG_NOT_START")) {
	$baseConfig = [];
	$merge = [];
	if (defined("CONFIG_PATH")) {
		$pathConfig = getConfigPath(CONFIG_PATH);
		$baseConfig = $pathConfig['schema'];
		$merge = $pathConfig['data'];
		Env::I(CONFIG_PATH);
	}

	Config::start($baseConfig)->merge($merge);

	if (config("logging.enabled")) {
		ErrorHandler::register(
			Log::getChanel('Error')
		);
	}
}
?>