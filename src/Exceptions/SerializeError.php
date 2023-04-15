<?php 
declare(strict_types=1);
namespace Rep98\Collection\Exceptions;

use RuntimeException;
/**
 * SerializeError
 */
class SerializeError extends RuntimeException
{
	const NOT_VALID = "Data to be serialized is invalid.";
}
?>