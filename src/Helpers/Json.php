<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Exceptions\JsonError;
use JsonSerializable;
/**
 * Json
 * Manipula un Json
 */
class Json extends JsonSerializable
{
	/**
	 * Contiene la cadena del Json
	 * @var string
	 */
	protected string $jsonString = "";
	/**
	 * Contiene la matriz trasformada del Json
	 * @var array
	 */
	protected array $jsonArr = [];

	protected function __construct(arra|string $json)
	{
		if (is_array($json)) {
			$this->jsonString = $this->encode($json);
			$this->jsonArr = $json;
		} else {
			$this->jsonString = $json;
			$this->jsonArr = $this->decode($json, true);
		}		
	}
	/**
	 * Codifica una matriz en un Json valido
	 * @param  mixed       $value El value a ser codificado
	 * @param  int|integer $flags Máscara de bits
	 * @param  int|integer $depth Establece la profundidad máxima
	 * @return string|bool
	 */
	public function encode(mixed $value, int $flags = 0, int $depth = 512): string|false
	{
		$this->jsonString = json_encode($value, $flags, $depth);
		$this->error();
		return $this->jsonString;
	}
	/**
	 * Decodifica un string de JSON
	 * @param  string       $json        El string de json a decodificar.
	 * @param  bool|boolean $associative Cuando es true, los objects JSON devueltos serán convertidos a array asociativos
	 * @param  int|integer  $depth       Profundidad máxima de anidamiento de la estructura que se decodifica.
	 * @param  int|integer  $flags       Máscara de bit
	 * @return mixed
	 */
	public function decode(string $json,
	    ?bool $associative = true,
	    int $depth = 512,
	    int $flags = 0
	): mixed
	{
		$this->jsonArr = json_decode($json, $associative, $depth, $flags);
		$this->error();
		return $this->jsonArr();
	}
	/**
	 * Serializa un Json
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		return $this->jsonArr;
	}
	/**
	 * Error del Json
	 * @param  bool|boolean $onlyMsg Indica si solo muestra el mensaje o generar un erro
	 * @return bool|throw
	 * @throws JsonException Error generado si $onlyMsg es False y existe un error
	 */
	public function error(bool $onlyMsg = false)
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

		throw JsonError::msg();
	}
	/**
	 * Retorna una colección de array
	 * @return \Rep98\Collection\Helpers\Arr
	 */
	public function toArray()
	{
		return Arr::from(
			$this->jsonArr
		);
	}

	// Static
	/**
	 * Carga un Json desde un archivo
	 * @param  string $file Ruta del Json
	 * @return static
	 */
	public static function loadPath(string $file)
	{
		if (!file_exists($file)) {
    		throw new FileNotFound("File {$file} does not exist or cannot be loaded.", 1);
    	}

    	$json = file_get_contents($file);

		return self::from($json);
	}
	/**
	 * Carga un Json de un string o array
	 * @param  string $json la cadena o matriz a codificar
	 * @return static
	 */
	public static function from(array|string $json): static
	{
		return new static($json);
	}
	/**
	 * Crea un Json de un Array
	 * @param  array  $json Matriz a convertir
	 * @return static
	 */
	public static function fromArray(array $json): static
	{
		return new static($json);
	}
	/**
	 * Verifica si la candena dada es un json
	 * @param  mixed $json la cadena a validar
	 * @return bool
	 */
	public static function valid($json): bool
	{
		if (!empty($json)) {
			@json_decode($json);
			return (json_last_error() === JSON_ERROR_NONE);
		}
		return false;
	}

	// Magic
	public function __get($key)
	{
		return $this->toArray()->get($key, null);
	}

	public function __set($key, $value)
	{
		new static(
			$this->toArray()->set($key, $value)->toJson()
		);
	}

	public function __serialize(): array
	{
		return $this->jsonArr;
	}

	public function __unserialize(array $serialized): void
	{
		$this->jsonArr = $this->toArray()->merge($serialized);
	}

	public function __destruct()
	{
		$this->jsonArr = [];
		$this->jsonString = "";
	}
}
?>