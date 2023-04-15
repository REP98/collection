<?php 
declare(strict_types=1);
namespace Rep98\Collection\Exceptions;

use RuntimeException;
/**
 * JsonError 
 * Excepciones Para los Jsons
 */
class JsonError extends RuntimeException
{
	public function __construct()
	{
		parent::__construct(json_last_error_msg(), json_last_error());
	}

	public function __toString()
	{
		return "(".json_last_error().") ".json_last_error_msg();
	}
}
?>