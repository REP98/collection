<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use PhpOption\Option;
use Rep98\Collection\Helpers\Json;
/**
 * Value
 * Transforma el valor a su tipo correcto
 */
class Value
{
	/**
	 * Constante negativa
	 */
	const NONE = "NONE";

	/**
	 * El valor a manejar
	 * @var mixed
	 */
	protected $value;

	/**
	 * Contructor de Value
	 * @param mixed $value
	 */
	function __construct(mixed $value)
	{
		$this->value = $value;
	}

	/**
	 * Convierte el valor a su tipo correcto
	 * @param  mixed $default valor por defecto a devolver
	 * @return mixed
	 */
	public function convert(mixed $default = null): mixed
	{
		return Option::fromValue($this->value)
			->map(function($value) {
				if (is_string($value)) {
					switch (strtolower($value)) {
	                    case 'true':
	                    case '(true)':
	                        return true;
	                    case 'false':
	                    case '(false)':
	                        return false;
	                    case 'empty':
	                    case '(empty)':
	                        return '';
	                    case 'null':
	                    case '(null)':
	                        return;
	                }

	                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
	                    return $matches[2];
	                }
				}				

                if (is_numeric($value)) {
                	return str_contains((string) $value, ".") ? (float) $value : (int) $value;
                }

                return $value;
			})
			 ->getOrCall(fn () => values($default));
	}
	/**
	 * Verifica si el valor actual es del tipo dado
	 * @param  string  $type el tipo
	 * @return boolean
	 */
	public function is(string $type): bool
	{
		$v = $this->convert();

		if (! $this->validType($type)) {
			return false;
		}

		return gettype($v) === $type;
	}
	/**
	 * Valida si el tipo esperado es un tipo valido
	 * @param  string $type Nombre del tipo
	 * @return bool       
	 */
	public function validType(string $type): bool
	{
		$validType = [
			"boolean", "integer", "double" ,"float", "string",
			"array", "object", "resource", "resource (closed)", 
			"NULL", "unknown type"
		];

		return in_array($type, $validType);
	}
	/**
	 * Recorre un matriz y evalue el valor correcto
	 * @param  mixed 	  $default valor por defecto en caso de no existe
	 * @return array
	 */
	public function convertArray(mixed $default = null): array
	{
		$a = [];
		foreach ((array) $this->value as $key => $value) {
			if (Json::valid($value)) {
				$value = Json::decode($value);
			} 
			$n = new self ($value);
			if (is_array($value)) {
				$a[$key] = $n->convertArray($default);
			} else {
				$a[$key] = $n->convert($default);
			}			
		}
		return $a;
	}
	/**
	 * Inicializador de la clase
	 * @param  mixed  $value
	 * @return Value
	 */
	public static function create(mixed $value): Value
	{
		return new static($value);
	}

	/**
	 * Crea un valor solo si es del tipo dado
	 * @param  mixed  $value el valor
	 * @param  string $type  el tipo de valor esperado
	 * @return mixed	Si el valor no es del tipo esperado retornara un string con el texto "NONE"
	 */
	public static function createOnly(mixed $value, string $type): mixed
	{
		$v = self::create($value);
		$method = is_array($value) ? "convertArray" : "convert";
		return $v->is($type) ? $v->{$method}() : self::NONE;
	}

	public function __destruct()
	{
		$this->value = null;
	}
}
?>