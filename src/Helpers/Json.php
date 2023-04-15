<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Serializable;
use Rep98\Collection\Exceptions\JsonError;
use Rep98\Collection\Exceptions\FileNotFound;
use JsonSerializable;
/**
 * Json
 * Manipula un Json
 */
class Json
{
	public static bool $returnMessage = false;

	protected ?Arr $data = null;
	protected string $origin = "";

	/**
	 * Carga un `Json` desde un archivo
	 * @param  string $file Ruta del `Json`
	 * @return array
	 */
	public static function loadPath(string $file): array
	{
		if (!file_exists($file)) {
    		throw new FileNotFound("File $file does not exist or cannot be loaded.", 1);
    	}

    	$json = file_get_contents($file);

		return self::decode($json);
	}

	/**
	 * Codifica una matriz en un `Json` valido
	 * @param  mixed       $value El valor a ser codificado
	 * @param  int|integer $flags Máscara de bits
	 * @param  int|integer $depth Establece la profundidad máxima
	 * @return string|bool
	 */
	public static function encode($value, int $flags = 0, int $depth = 512): string|false
	{
		$json = json_encode($value, $flags, $depth);
		$s = self::error(self::$returnMessage);
		// @codeCoverageIgnoreStart
		if ($s !== false) {
			return $s;
		}
		// @codeCoverageIgnoreEnd
		return $json;
	}
	/**
	 * Decodifica una cadena de `JSON`
	 * @param  string       $json        La cadena de `json` a decodificar.
	 * @param  bool|boolean $associative Cuando es true, los objectos `JSON` devueltos serán convertidos a un `array` asociativo
	 * @param  int|integer  $depth       Profundidad máxima de anidamiento de la estructura que se decodifica.
	 * @param  int|integer  $flags       Máscara de bit
	 * @return mixed
	 */
	public static function decode(string $json,
	    ?bool $associative = true,
	    int $depth = 512,
	    int $flags = 0
	): mixed
	{
		$data = json_decode($json, $associative, $depth, $flags);
		$s = self::error(self::$returnMessage);
		if ($s !== false) {
			return $s;
		}
		
		return $data;
	}
	/**
	 * Serializa un `Json`
	 * @param mixed $json El `Json` a Serializar
	 * @return mixed
	 */
	public static function serialize(string $json): string
	{
		return Serializable::serialize($json);
	}
	/**
	 * Crea un `Json` de un `Json` Serializado
	 * @param  string $str La cadena serializada
	 * @return string
	 */
	public static function unserialize(string $str): string
	{
		return Serializable::unserialize($str);
	}
	/**
	 * Retorna el ultimo error generado
	 * @param  bool $onlyMsg Indica si solo muestra el mensaje o generar un error
	 * @return array|false
	 * @throws JsonException Error generado si $onlyMsg es False y existe un error
	 */
	public static function error(bool $onlyMsg = false): array|false
	{
		$lastError = json_last_error();
		if ($lastError === JSON_ERROR_NONE) {
			return false;
		}

		if ($onlyMsg) {
			return [
				json_last_error() => json_last_error_msg()
			];
		}

		throw new JsonError;
	}
	/**
	 * Transforma una cadena `json` un una matriz dominanda por la clase [`Arr`](Arr)
	 * @param string $json La cadena `json` a convertir en matriz
	 * @return \Rep98\Collection\Helpers\Arr
	 */
	public static function collection(string $json): Arr
	{
		return Arr::from(
			self::decode($json)
		);
	}
	
	/**
	 * Verifica si la cadena dada es un `Json`
	 * @param  mixed $json la cadena a validar
	 * @return bool
	 */
	public static function valid($json): bool
	{
		if (!is_string($json) || "" === $json) {
			return false;
		}

		if (! in_array($json[0], ["{", "["], true)) {
			return false;
		}

		if (!empty($json)) {
			@json_decode($json);
			return (json_last_error() === JSON_ERROR_NONE);
		}

		json_decode([]);
		return false;
	}
	/**
	 * Establece las cabeceras principales
	 * @param bool $remove  Indica si remueve las cabeceras anteriores
	 */
	public static function header(bool $remove = false)
	{
		if ($remove) {
			ob_clean();

			header_remove();
		}

		header('Content-Type: application/json; charset=utf-8');
	}
	/**
	 * Respuesa Http del Json
	 * @param  mixed  $data los datos
	 */
	public static function response(mixed $data)
	{
		self::$returnMessage = true;
		$json = $data;

		if (is_array($data)) {
			$json = self::encode($json);
		}

		if (is_array($json)) {
			http_response_code(500);
			echo Json::encode($json);
			exit();
		}
		http_response_code(200);
		echo $json;

		exit();
	}
	
	/**
	 * Escribe un Archivo Json
	 * @param  mixed  $data     Los datos a escribir
	 * @param  string $filename Nombre y ruta del archivo
	 * @return int
	 */
	public static function write(mixed $data, string $filename): int
	{
		self::$returnMessage = false;
		$json = self::encode($data);

		return file_put_contents($filename, $json, FILE_APPEND | LOCK_EX);
	}

	/**
	 * Permite usar la clase `Json` como matriz de datos dinámicos para nuestra cadena Json
	 * @param  string $json La cadena a utilizar
	 * @return [`Json`](Json)
	 */
	public static function object(string $json): Json
	{
		return new static($json);
	}

	/**
	 * Constructor de la Clase
	 * @param string $json
	 */
	public function __construct(string $json)
	{
		if (self::valid($json)) {
			$this->data = self::collection($json);
			$this->origin = $json;
		}
	}
	/**
	 * Retorna la cadena original pasada como argumento en [`Json::object`](Json#object)
	 * @return string
	 */
	public function getOrigin(): string
	{
		return $this->origin;
	}
	/**
	 * Retorna el `Json` como un `array`
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data->all();
	}

	// Magic
	public function __get($key)
	{
		return $this->data->get($key, null);
	}

	public function __set($key, $value)
	{
		new static(
			$this->data->set($key, $value)->toJson()
		);
	}

	public function __destruct()
	{
		$this->data = null;
	}
}
?>