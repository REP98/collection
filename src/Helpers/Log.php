<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Rep98\Collection\Interface\Configurable;
/**
 * Log
 * Sistema de LOG para manejar registros
 */
class Log implements Configurable
{
	/**
	 * Instacia de Monolog
	 * @var \Monolog\Logger
	 */
	protected static Logger $logger;

	/**
	 * Establece el esquema de configuraciones de la clase [`Log`](Log)
	 * @return \Nette\Schema\Schema
	 */
	public static function getSchema(): Schema
	{
		return Expect::structure([
			'enabled' => Expect::bool()->default(true),
			'path' => Expect::string()
				->assert(function ($path) { 
					return \is_writeable($path); 
				})
				->default(sys_get_temp_dir()),
			"formated" => Expect::string()
				->default("[%datetime%] %channel% <%level_name%>: %message% %context% %extra%\n"),
			"dateFormated" => Expect::string()->default("Y-m-d H:i:s"),
			"channel" => Expect::structure([
				"name" => Expect::string()->default("main"),
				"browser" => Expect::bool()->default(true)
			])
		]);
	}

	public static function getNameSchema(): string
	{
		return "logging";
	}

	/**
	 * Obtiene el canal y crea la instancia de monolog
	 * @param  string $level nivel de Adveretencia, se usa para crear el nombre
	 * @return \Monolog\Logger
	 */
	public static function getChanel($level = 'Debug'): Logger|false
	{
		if (!config("logging.enabled", false)) {
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
	public static function warning(string $messager, array $context = []): Logger
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
	public static function error(string $messager, array $context = []): Logger
	{
		static::getChanel('Error')->error($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una información
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function info(string $messager, array $context = []): Logger
	{
		static::getChanel('Info')->info($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra un Debug
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function debug(string $messager, array $context = []): Logger
	{
		static::getChanel('Debug')->debug($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Noticia
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function notice(string $messager, array $context = []): Logger
	{
		static::getChanel('Notice')->notice($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Critico
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function critical(string $messager, array $context = []): Logger
	{
		static::getChanel('Critical')->critical($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Alerta
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function alert(string $messager, array $context = []): Logger
	{
		static::getChanel('Alert')->alert($messager, $context);
		return static::$logger;
	}
	/**
	 * Registra una Emergencias
	 * @param  string $messager El Mensaje
	 * @param  array  $context  Contexto
	 * @return \Monolog\Logger
	 */
	public static function emergency(string $messager, array $context = []): Logger
	{
		static::getChanel('Emergency')->emergency($messager, $context);
		return static::$logger;
	}
}

?>