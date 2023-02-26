<?php 
declare(strict_types=1);
namespace Rep98\Collection\Exceptions;

use JsonException;
use Throwable;
/**
 * JsonError 
 * Excepciones Para los Jsons
 */
class JsonError extends JsonException
{
	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
	/**
	 * Muestra el ultimo error generado por json_encode o json_decode
	 * @return JsonException
	 */
	public static function msg(): JsonException
	{
		return new static(
			json_last_error_msg(),
			json_last_error()
		)
	}
}
?>