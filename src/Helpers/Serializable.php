<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Exceptions\SerializeError;
/**
 * Serializable
 */
final class Serializable
{
	/**
	 * Genera una representación apta para el almacenamiento de un valor
	 * @param  mixed  $value El valor a ser serializado
	 * @return string
	 */
	public static function serialize(mixed $value): string
	{
		try {
			return serialize($value);
			// @codeCoverageIgnoreStart
		} catch (SerializeError $e) {
			throw new SerializeError($e->getMessage());
		}
		// @codeCoverageIgnoreEnd
	}
	/**
	 * Crea un valor PHP a partir de una representación almacenada
	 * @param  string     $str     El string serializado.
	 * @param  array|null $options Cualquier opción para `unserialize()`, dada como un array asociativo.
	 * @return mixed
	 */
	public static function unserialize(string $str, ?array $options = null): mixed
	{
		if (self::valid($str)) {
			return unserialize($str);
		}
		
		throw new SerializeError(SerializeError::NOT_VALID, 100);
	}
	/**
	 * Comprueba el valor para saber si se serializó.
	 * @see https://developer.wordpress.org/reference/functions/is_serialized/ is_serialized Wordpress
	 * @param  string       $data   Valor para verificar si se serializó.
	 * @param  bool $strict Se utiliza para estricto con el final de la cadena.
	 * @return bool
	 */
	public static function valid(string $data, bool $strict = true): bool
	{
		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, -1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace ) {
				return false;
			}
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 ) {
				return false;
			}
			if ( false !== $brace && $brace < 4 ) {
				return false;
			}
		}
		$token = $data[0];
		switch ( $token ) {
			case 's':
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
				// Or else fall through.
			case 'a':
			case 'O':
			case 'E':
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b':
			case 'i':
			case 'd':
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
		}
		// @codeCoverageIgnoreStart
		return false;
		// @codeCoverageIgnoreEnd
	}
}
?>