<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Rep98\Collection\Exceptions\StringException;
use Rep98\Collection\Helpers\Arr;
use Rep98\Collection\Helpers\Json;
use Rep98\Collection\Helpers\Pluralizer;
use voku\helper\ASCII;
/**
 * Str
 * Manejado de elementos de texto 
 */
final class Str  
{
    const ORIGIN_CASE = -1;
    const SNAKE_LOWER_CASE = 10;
    const SNAKE_UPPER_CASE = 20;
    const SNAKE_CAPITAL_CASE = 30;
    const LOWER_CASE = 100;
    const UPPER_CASE = 200;

    // @codeCoverageIgnoreStart
	private function __construct()
    {
    }
    // @codeCoverageIgnoreEnd
    /**
     * Obtiene el valor del contenido antes del texto dado
     * @param  string      $haystack La cadena
     * @param  string      $needle   EL valor a buscar
     * @return string
     */
    public static function afterOf(string $haystack, string $needle): string
    {
        return $needle === "" ? $haystack : array_reverse(explode($needle, $haystack, 2))[0];
    }
    /**
     * Transforma la cadena en sus caracteres `ASCII` según el idioma dado
     * @param  string $value La cadena
     * @param  string $lang  EL idioma
     * @return string
     */
    public static function ascii(string $value, string $lang = "es"): string
    {
        return ASCII::to_ascii($value, $lang, true, true, true); 
    }
    /**
     * Obtiene el valor del contenido después del texto dado
     * @param  string      $haystack La cadena
     * @param  string      $needle   El valor a buscar
     * @param  string      $encoding Codificación
     * @return string
     */
    public static function beforeOf(string $haystack, string $needle, ?string $encoding = null): string
    {
         $result = mb_stristr($haystack, $needle, true, $encoding);
         return $result === false ? $haystack : $result;
    }
    /**
     * Extrae una parte de la cadena dada por dos cadenas de búsqueda
     * @param  string      $haystack La cadena
     * @param  string      $from     Inicio de búsqueda
     * @param  string      $to       Fin de búsqueda
     * @param  string|null $encoding Codificación
     * @return string
     */
    public static function between(string $haystack, string $from, string $to, ?string $encoding = null): string|false
    {
        return static::beforeOf(
            static::afterOf($haystack, $from),
            $to,
            $encoding
        );
    }
    /**
     * Convierte el texto a camelCase
     *
     * Permite 3 modos o estilos para retornar el formato, puede ser:
     * + *CamelCase* Modo `Str::UPPER_CASE`
     * + *camelCase* Modo `Str::LOWER_CASE`
     * 
     * @param  string  $value La cadena
     * @param int $mode El estilo de camelCase obtenido
     * @return string
     */
    public static function camel(string $value, int $mode = self::LOWER_CASE): string
    {
        $string = preg_replace(
            "/\s+/u",
            "",
            self::convertCase($value, MB_CASE_TITLE, "UTF-8")
        );

        return $mode === self::LOWER_CASE ? self::lcfirst( $string ) : self::ucfirst($string);
    }
    /**
     * Convierte en Mayúscula la primera letra del texto
     * @param  string $value      La cadena
     * @param  string $encoding La codificación
     * @return string   
     */
    public static function capitalize(string $value, string $encoding = "UTF-8"): string
    {
        return self::convertCase($value, MB_CASE_TITLE, $encoding);
    }
    /**
     * Retorna el carácter unicode dada
     * @param  int          $codepoint El código
     * @param  string       $encoding  La codificación
     * @return string
     */
    public static function chr(int $codepoint, ?string $encoding = null): string|false
    {
        $encoding = is_null($encoding) ? mb_internal_encoding() : $encoding;
        return mb_chr($codepoint, $encoding);
    }
    /**
     * Verifica si la cadena tiene el valor dado
     * @param  string       $haystack   La cadena
     * @param  string       $needle     El valor a buscar
     * @param  bool|boolean $ignoreCase Indica si ignora mayúscula de minúsculas
     * @return bool
     */
    public static function contains(string $haystack, string $needle, bool $ignoreCase = false): bool
    {  
        $needle = $ignoreCase ? self::lower($needle) : $needle;

        return str_contains($haystack, $needle);
    }
    /**
     * Realiza una conversión a mayúsculas/minúsculas de una cadena
     * @param string $value El Valor
     * @param int $mode El modo de transformación. Para conocer los modos visite [`mb_convert_case`](https://www.php.net/manual/en/function.mb-convert-case.html)
     * @param  string $encoding  La codificación
     * @return string
     */
    public static function convertCase(string $value, int $mode = MB_CASE_TITLE, string $encoding = 'UTF-8'): string
    {
        if ($mode < 0 || $mode > 7) {
            throw new StringException("Modo [$mode] no es valido");
        }
        return mb_convert_case($value, $mode, $encoding);
    }
    /**
     * Verifica si la cadena termina en un valor dado
     * @param  string $haystack La cadena
     * @param  string $needle   El valor a buscar
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }
    /**
     * Compara 2 cadenas y retorna true si son idénticas.
     * @param  string       $string1 La cadena 1
     * @param  string       $string2 La cadena a comparar
     * @param  bool|boolean $strict  Indica si debe ser sensibles a mayúsculas o minúsculas
     * @return bool
     */
    public static function equal(string $string1, string $string2, bool $strict = false): bool
    {
        $callback = $strict ? "strcmp" : "strcasecmp";
        return $callback($string1, $string2) === 0;
    }
    /**
     * Dividir una cadena por una cadena
     * @param  string $separator La cadena límite.
     * @param  string $value     La cadena de entrada.
     * @return array
     */
    public static function explode(string $separator, string $value): array
    {
        return explode(
            $separator,
            $value,
            PHP_INT_MAX);
    }
    /**
     * Verifica si la cadena dada es un `Ascii` Valido
     * @param  string  $char La cadena
     * @return boolean 
     */
    public static function isAscii(string $char): bool
    {
        return ASCII::is_ascii($char);
    }
    /**
     * Verifica si la cadena dada es un `Json`
     * @param  string  $string La cadena
     * @return boolean
     */
    public static function isJson(string $string): bool
    {
       return Json::valid($string);
    }
    /**
     * Convierte el primer carácter de una cadena en minúscula
     * @param  string $string La cadena
     * @return string
     */
    public static function lcfirst(string $string): string
    {
        return lcfirst($string);
    }
    /**
     * Obtiene la longitud de una cadena de caracteres
     * @param  string $value La cadena
     * @return int
     */
    public static function length(string $value, ?string $encoding = null): int
    {
        return mb_strlen($value, $encoding);
    }
    /**
     * Convierte una cadena a minúsculas.
     * @param  string  $value La cadena
     * @param  string  $encoding La codificación
     * @return string
     */
    public static function lower(string $value, string $encoding = 'UTF-8'): string
    {
        return mb_strtolower($value, $encoding);
    }
    /**
     * Mascara de caracteres para la cadena dada
     * @param  string      $string   La cadena
     * @param  string      $char     El carácter con el cual cubrir
     * @param  int|integer $index    La posición inicial
     * @param  int|integer $length   El Tamaño de la mascara
     * @param  string      $encoding La codificación
     * @return string
     */
    public static function mask(
        string $string, 
        string $char = "*", 
        int $index = 2, 
        int $length = -1, 
        string $encoding = "UTF-8"): string
    {
        if ($char === "") {
            return $string;
        }

        if ($length < 1) {
            $length = self::length($string);        
        }

        $segment = self::substr($string, $index, $length, $encoding);

        if ($segment === "") {
            return $string;
        }

        $strlen = self::length($string, $encoding);
        $startI = $index;
        if ($index < 0) {
            $startI = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = self::substr($string, 0, $startI, $encoding);
        $segmentLen = self::length($segment, $encoding);
        $end = self::substr($string, $startI + $segmentLen);

        return $start.self::repeat(
            self::substr($char, 0, 1, $encoding),
            $segmentLen
        ).$end;
    }
    /**
     * Obtiene el código de una carácter
     * @param  string $value    El carácter
     * @param  string $encoding La codificación
     * @return int
     */
    public static function ord(string $value, ?string $encoding = 'UTF-8'): int|false
    {
        return mb_ord($value, $encoding);
    }
    /**
     * Rellena una cadena
     * @param string $string La cadena
     * @param int $length Tamaño de la nueva cadena
     * @param string $pad_string El valor con el cual rellenar
     * @param int $pad_type Una contante que indica el modo de llenado por defecto STR_PAD_BOTH.
     * @return string
     */
    public static function pad(
        string $string,
        int $length = 1,
        string $pad_string = " ",
        int $pad_type = STR_PAD_BOTH
    ): string
    {
        return str_pad($string, $length, $pad_string, $pad_type);
    }
    /**
     * Obtiene el plural de la palabra.
     *
     * @param  string  $value
     * @param  int|array|\Countable  $count
     * @return string
     */
    public static function plural(string $value, $count = 2): string
    {
        return Pluralizer::plural($value, $count);
    }
    /**
     * Remueve parte de una cadena
     * @param  array        $search      Las palabras a borrar
     * @param  string       $string      La cadena
     * @param  bool|boolean $insensitive Indica si es sensible a mayúsculas o minúsculas
     * @return string
     */
    public static function remove(
        string|array $search, 
        string $string,
        bool $insensitive = true
    ): string
    {
        return self::replace($search, "", $string, $insensitive);
    }
    /**
     * Repite la cadena tantas veces indique
     * @param  string      $string La cadena
     * @param  int|integer $count  La cantidad de veces a repetir
     * @return string
     */
    public static function repeat(string $string, int $count = 2): string
    {
        return $count < 2 ? $string : str_repeat($string, $count);
    }
    /**
     * Reemplaza un cadena por otra
     * @param  string       $search      El valor a buscar para reemplazar
     * @param  string       $replace     El valor por el cual reemplazar
     * @param  array        $subject     La cadena
     * @param  bool|boolean $insensitive Indica si debe ser sensible a mayúsculas y minúsculas
     * @return string
     */
    public static function replace(array|string $search,  array|string $replace, string|array $subject, bool $insensitive = false): string|array
    {
        $callback = $insensitive ? "str_ireplace" : "str_replace";

        return $callback($search, $replace, $subject);
    }
    /**
     * Revierte la cadena
     * @param string $string La cadena
     * @param int $mode El valor resultante, por defecto Original Str::ORIGIN_CASE, pero puede ser self::ORIGIN_CASE, self::LOWER_CASE, self::UPPER_CASE
     * @return string
     */
    public static function reverse(string $string, int $mode = self::ORIGIN_CASE): string
    {
        self::validMode($mode, [self::ORIGIN_CASE, self::LOWER_CASE, self::UPPER_CASE]);
        if ($mode === self::ORIGIN_CASE) {
            return strrev($string);
        }
        return $mode === self::LOWER_CASE ? self::lower(strrev($string)) : self::upper(strrev($string));
    }
    /**
     * Alegoriza una cadena
     * @param  string   $string La cadena
     * @param  int $seed   Una semilla para la cadena o null para ignorar
     * @return string
     */
    public static function shuffle(string $string, ?int $seed = null): string
    {
        if (!is_null($seed)) {
            srand($seed);
        }
        return str_shuffle($string);
    }
    /**
     * Obtiene el singular de una palabra
     *
     * @param  string  $value La cadena
     * @return string
     */
    public static function singular(string $value): ?string
    {
        return Pluralizer::singular($value);
    }
    /**
     * Genera una URI valida
     * @param  string $value La cadena
     * @return string
     */
    public static function slug(string $value): string
    {
       return slug($value);
    }
    /**
     * La función snake separa las palabras basado por su delimitador comúnmente por un guión bajo (_)
     * @param string $string La cadena
     * @param string $delimiter El delimitador
     * @param int $mode El modo screaming snake case a devolver
     */
    public static function snake(string $string, string $delimiter = "_", int $mode = self::SNAKE_LOWER_CASE): string
    {
        self::validMode($mode, [self::SNAKE_LOWER_CASE, self::SNAKE_UPPER_CASE, self::SNAKE_CAPITAL_CASE]);
        $string = preg_replace('/\s+/u', '', ucwords($string));
        $string = preg_replace('~(?<=\\w)([A-Z])~u', $delimiter."$1", $string);
        
        switch ($mode) {
            case self::SNAKE_LOWER_CASE:
                $string = self::lower($string);
            break;
            case self::SNAKE_UPPER_CASE:
                $string = self::upper($string);
            break;
            case self::SNAKE_CAPITAL_CASE:
                $string = self::capitalize($string);
            break;
        }
        
        return $string;
    }
    /**
     * Separa una cadena.
     * @param  string      $string  La cadena
     * @param  string      $pattern El patrón para separar
     * @param  int|integer $limit   La limitación de separación
     * @return array
     */
    public static function split(string $string, string $pattern, int $limit = -1): array
    {
        return mb_split($pattern, $string, $limit);
    }
    /**
     * Determina si una cadena inicia por el valor dado
     * @param  string $haystack La cadena
     * @param  string $needle   El valor
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }
    /**
     * Encuentra la primera aparición de un valor dado
     * @param  string      $haystack La cadena
     * @param  string      $needle   EL valor a buscar
     * @param  int|integer $offset   La posición inicial
     * @param  string|null $encoding La codificación
     * @return int|false
     */
    public static function stripos(string $haystack, string $needle, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_stripos($haystack, $needle, $offset, $encoding);
    }
    /**
     * Cuenta la cantidad de veces que un texto se encuentra dentro de la cadena
     * @param  array  $haystack La cadena
     * @param  string $needle   El Texto
     * @return int
     */
    public static function subCount(string|array $haystack, string $needle): int
    {
        if (is_array($haystack)) {
           $haystack = Arr::from($haystack)->join(" ");
        }

        if ($haystack === $needle) {
            return 1;
        }

        $length = self::length($haystack);

        return substr_count($haystack, $needle, 0, $length);
    }
    /**
     * Obtiene partes de una cadena
     * @param  string      $string   La cadena
     * @param  int|integer $start    El inicio
     * @param  int|null    $length   El tamaño
     * @param  string      $encoding La codificación
     * @return string
     */
    public static function substr(string $string, int $start = 0, ?int $length = null, ?string $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
    }
    /**
     * Reemplaza y recorta una cadena
     * @param  string $string  La cadena
     * @param  string $replace Los parámetros a reemplazar
     * @param  int    $offset  Inicio
     * @param  null   $length  Tamaño
     * @return string|array 
     */
    public static function substrReplace(
        array|string $string, 
        array|string $replace, 
        array|int $offset, 
        array|int|null $length = null
    ): string|array
    {
        $length = is_null($length) ? self::length($string) : $length;
        return substr_replace($string, $replace, $offset, $length);
    }
    /**
     * Reemplaza partes de una cadena
     * @param  string $string        La cadena
     * @param  array  $replace_pairs Las partes para reemplazar
     * @return string
     */
    public static function strtr(string $string, array $replace_pairs): string
    {
        return strtr($string, $replace_pairs);
    }
    /**
     * Traduce las cadenas dada
     * @param  string $values La cadena
     * @return string
     */
    public static function transliterate(string $values): string
    {
       return ASCII::to_transliterate($values);
    }
    /**
     * Limita la cadena al ancho especificado
     * @param  string      $value      La cadena
     * @param  int|integer $width      El ancho
     * @param  string      $trimMarker El texto al final
     * @param  string|null $encoding   La codificación
     * @return string
     */
    public static function truncated(string $value, int $width = 100, string $trimMarker = "...", ?string $encoding = null): string
    {
        return mb_strwidth($value, $encoding) <= $width ? 
            $value : rtrim(mb_strimwidth($value, 0, $width, $trimMarker, $encoding));
    }
    /**
     * Coloca el primer carácter en mayúscula
     * @param  string $value El carácter
     * @return string
     */
    public static function ucfirst(string $value): string
    {
        return ucfirst($value);
    }
    /**
     * Coloca en mayúscula las palabras de una cadena
     * @param  string $value     La cadena
     * @param  string $delimiter El delimitador
     * @return string
     */
    public static function ucwords(string $value, string $delimiter = " \t\r\n\f\v"): string
    {
        return ucwords($value, $delimiter);
    }
    /**
     * Elimina los acentos y tildes de la cadena
     * @param  string $value La cadena
     * @return string
     */
    public static function unaccent(string $value): string
    {
        return Pluralizer::unaccent($value);
    }
    /**
     * Regresa una cadena CamelCase a su estado original
     * @param  string       $value      La cadena
     * @param  string       $delimiters Delimitadores
     * @param  bool|boolean $lower      Indica si debe ser en minúsculas
     * @return string
     */
    public static function uncamel(string $value, string $delimiters = "_", bool $lower = false): string
    {
        $tableized = preg_replace('~(?<=\\w)([A-Z])~u', $delimiters.'$1', $value);

        if ($tableized === null) {
            // @codeCoverageIgnoreStart
            throw new StringException(sprintf(
                'preg_replace returned null for value "%s"',
                $value
            ));
            // @codeCoverageIgnoreEnd
        }

        return $lower ? self::lower($tableized) : $tableized;
    }
    /**
     * Transforma en mayúscula una cadena
     * @param  string $value    La cadena
     * @param  string $encoding La codificación
     * @return string
     */
    public static function upper(string $value, string $encoding = "UTF-8"): string
    {
        return mb_strtoupper($value, $encoding);
    }
    /**
     * Cuenta la cantidad de palabras que hay en una cadena
     * @param  string      $string     La cadena
     * @param  string|null $characters Lista adicional de caracteres, para tomar en cuenta como palabras
     * @return array|int
     */
    public static function wordCount(string $string, ?string $characters = null): array|int
    {
        return str_word_count($string, 0, $characters);
    }
    /**
     * Limita una cadena por sus palabras
     * @param  string      $string La cadena
     * @param  int|integer $words  La cantidad de Palabras
     * @param  string      $end    Fin de la cadena nueva
     * @return string
     */
    public static function wordTruncate(string $string, int $words= 100, string $end = "..." ): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $string, $matches);
        if (! isset($matches[0]) || static::length($string) === static::length($matches[0])) {
            return $string;
        }

        return rtrim($matches[0]).$end;
    }
    /**
     * Recorta palabras y separa en linea
     * @param  string       $string         La cadena
     * @param  int|integer  $width          El ancho
     * @param  string       $break          El separador
     * @param  bool|boolean $cut_long_words Indica si se corta palabras largas
     * @return string
     */
    public static function wordWrap(
        string $string,
        int $width = 75,
        string $break = "\n",
        bool $cut_long_words = false
    ): string
    {
        if ($width >= self::length($string)) {
            return $string;
        }

        return wordwrap($string, $width, $break, $cut_long_words);
    } 

    protected static function validMode(int $mode, array $listMode): bool|StringException
     {
        if (in_array($mode, $listMode)) {
            return true;
        }
        throw new StringException("Mode invalid", 10);        
     } 
}
?>