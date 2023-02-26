<?php 
declare(strict_types=1);
namespace Rep98\Fasli\Helpers;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;

/**
 * Log
 * Sistema de LOG para manejar registros
 */
class Log
{
	/**
	 * Instacia de Monolog
	 * @var \Monolog\Logger
	 */
	protected static $logger;

	/**
	 * Obtiene el canal y crea la instancia de monolog
	 * @param  string $level nivel de Adveretencia, se usa para crear el nombre
	 * @return \Monolog\Logger
	 */
	public static function getChanel($level = 'Debug') {
		if (!config("logging.enabled")) {
			return false;
		}

		$dateFormat = config("logging.dateFormated");
		$formated = config("logging.formated");
		$lf = new LineFormatter($formated, $dateFormat);
		$StreamHandler = new StreamHandler(
			config("logging.path").DS.$level.".log", 
			Logger::DEBUG
		);
		$StreamHandler->setFormatter($lf);
		static::$logger = new Logger(config('logging.channel.name', 'app'));
		static::$logger->pushHandler($StreamHandler);

		if (config('logging.channel.browser', false)) {
			static::$logger->pushHandler(new BrowserConsoleHandler());
		}

		return static::$logger;
	}
	/**
	 * Registra una advertencia
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function warning(string $messager, array $context = [])
	{
		static::getChanel('Warning')->warning($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra un Error
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function error(string $messager, array $context = []) {
		static::getChanel('Error')->error($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una información
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function info(string $messager, array $context = []) {
		static::getChanel('Info')->info($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra un Debug
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function debug(string $messager, array $context = []) {
		static::getChanel('Debug')->debug($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Noticia
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function notice(string $messager, array $context = []) {
		static::getChanel('Notice')->notice($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Critico
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function critical(string $messager, array $context = []) {
		static::getChanel('Critical')->critical($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Alerta
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function alert(string $messager, array $context = []) {
		static::getChanel('Alert')->alert($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Emergencias
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function emergency(string $messager, array $context = []) {
		static::getChanel('Emergency')->emergency($messager, $context);
		return static::$logger;
	}
}

?>