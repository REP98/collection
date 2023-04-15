<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use ArgumentCountError;
use ArrayAccess;
use Rep98\Collection\Exceptions\FileNotFound;

/**
 * Arr
 * Manejador de Matrices PHP
 */
class Arr implements ArrayAccess
{
	const DIFF = "diff";
	const DIFF_ASSOC = "assoc";
	const DIFF_KEY = "key";
	const DIFF_UASSOC = "uassoc";
	const DIFF_UKEY = "ukey";

	const UNDEFINED = "undefined";

	/**
	 * Matriz interna
	 * @var array
	 */
	protected array $items = [];

	// STATIC METHOD
	/**
	 * Singleton de Arr
	 * @static
	 * @param  mixed|array $array Matriz de Datos
	 * @return \Rep98\Collection\Lib\Helpers\Arr
	 */
	public static function from(mixed $array = []): static
	{
		if ($array instanceof Arr) {
			return $array;
		}
		return new static($array);
	}
	/**
	 * Llenar una matriz con valores
	 * @static
	 * @param  array|int    $start_index numero de inicio o matrix con claves de incio
	 * @param  mixed        $value       Valor a rellenar
	 * @param  init|integer $count       cantidad de datos a rellenar si $start_index es un número
	 * @return array
	 */
	public static function fill(int|array $start_index,  mixed $value, int $count=0): array
	{
		return is_array($start_index) ? 
			array_fill_keys($start_index, $value) :
			array_fill($start_index, $count, $value);
	}
	/**
	 * Encierra los elementos en un array
	 * @param  mixed $value Valores
	 * @return array
	 */
	public static function wrap($value = null): array
    {
        if (is_null($value)) {
            return [];
        }
        if ($value instanceof Arr) {
        	return $value->all();
        }
        return is_array($value) ? $value : [$value];
    }
    /**
     * Intenta cargar una matriz desde un array
     * @param  string $file ruta del archivo
     * @return Arr|False
     */
    public static function loadPath(string $file): Arr|false
    {
    	if (!file_exists($file)) {
    		throw new FileNotFound("File {$file} does not exist or cannot be loaded.", 1);
    	}
    	$arr = include $file;

    	if (is_array($arr)) {
    		return self::from($arr);
    	}
    	return false;
    }
    /**
     * Determina si una matriz es asociativa.
     *
     * Una matriz es "asociativa" si no tiene claves numéricas secuenciales que comiencen con cero.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Determina si una matriz es una lista.
     *
     * Una matriz es una "lista" si todas las claves de la matriz son números enteros secuenciales que comienzan desde 0 sin espacios intermedios.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isList(array $array): bool
    {
        return !self::isAssoc($array);
    }
    // OBJECT METHOD
	/**
	 * Crea una nueva instancia con la matriz dada
	 * @param mixed|array $array Matriz de datos
	 */
	public function __construct(mixed $array = [])
	{
		$this->items = (array) $array;
	}
	/**
	 * Añade una elemento si no existe
	 * @param string $key   la clave
	 * @param mixed $value la value
	 * @return \Rep98\Collection\Helpers\Arr
	 */
	public function add($key, $value): Arr
	{
		if (is_null($this->get($key, null))) {
			$this->set($key, $value);
		}
		return $this;
	}
	/**
	 * Establece items a la matriz
	 * @param mixed $key   La clave
	 * @param mixed $value El valor
	 * @return \Rep98\EMW\Lib\Helpers\Arr
	 */
	public function set(mixed $key, mixed $value): Arr
	{
		if (is_null($key)) {
			$this->items[] = $value;
			return $this;
		}

		$keys = $this->dots($key);
		$array = &$this->items;
		foreach ($keys as $ix => $key) {
			if (count($keys) === 1) {
                break;
            }

            unset($keys[$ix]);

            if (empty($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];           
		}

		$array[array_shift($keys)] = $value;

		return $this;
	}

	/**
	 * Obtiene un items o todos los items de la matriz
	 * @param  mixed|null $key     la clave a buscar, admite notación de puntos
	 * @param  mixed|null $default Valor por defecto en caso de no encotrar
	 * @return mixed
	 */
	public function get(mixed $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
        	return $this->items;
        }

        if (!is_array($key) && $this->exists($key)) {
        	return $this->items[$key];
        }
        $array = $this->items;
        $keys = $this->dots($key);
        $array = new static($array);
        foreach ($keys as $segment) {
        	if ($array->exists($segment)) {
        		$array = new static($array[$segment]);
            } else {
                return $default;
            }
        }
        
        return $array->count() == 0 ? $default : 
        	($array->count() > 1 ? $array->all() : $array->all()[0]);
    }    	
	/**
	 * Verifica si existe una clave en la matriz
	 * @param  mixed  $key    la clave a validar
	 * @param  boolean $strict indica si se debe se extripto en la conparacion por defecto false
	 * @return bool
	 */
	public function exists($key, $strict = false): bool
	{
		if (is_float($key)) {
			$key = (string) $key;
		}

		return $strict ? 
				in_array($key, array_keys($this->items), true) :
				array_key_exists($key, $this->items);
	}
	/**
	 * Verifica si la matriz contiene un valor
	 * @param  mixed        $value
	 * @param  bool|boolean $strict
	 * @return bool
	 */
	public function contains(mixed $value, bool $strict = false): bool
	{
		return in_array($value, $this->items, $strict);
	}
	
	/**
	 * Mezcla una Matriz
	 * @param  int|null $seed Semilla de la Mezcla
	 * @return array
	 */
	public function shuffle(int|null $seed = null): array
	{
		if (is_null($seed)) {
			shuffle($this->items);
		} else {
			mt_srand($seed);
            shuffle($this->items);
            mt_srand();
		}

		return $this->items;
	}
	/**
	 * Comprobar si existe un índice
	 * @param  mixed  $offset El índice a comprobar.
	 * @return bool         
	 */
	public function offsetExists(mixed $offset): bool
	{
		$keys = (array) $offset;

		if (empty($this->items) || $offset === []) {
			return false;
		}		

		$array = $this->items;

		foreach ($keys as $key) {
			$subArr = new static($array);
			if ($subArr->exists($key)) {
			 	continue;
			} 
			foreach ($this->dots($key) as $ix => $segment) {
				if($subArr->exists($segment)) {
					$subArr = new static($subArr[$segment]);
				} else {
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * Asignar un valor al índice esepecificado
	 * @param  mixed  $offset El offset al que se asigna el valor.
	 * @param  mixed  $value  El valor a asignar.
	 * @return void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->set($offset, $value);
	}
	/**
	 * @see \Rep98\Collection\Helpers\Arr::get
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}
	/**
	 * Destruye un offset
	 * @param  mixed  $offset El offset a destruir.
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void
	{
		if ($this->offsetExists($offset)) {
			$this->remove($offset);
		}
	}
	/**
	 * @see self::offsetExists
	 */
	public function has(mixed $offset): bool
	{
		return $this->offsetExists($offset);
	}
	/**
	 * Extrae parte de una Array identificada por su claves
	 * @param  mixed $keys La clave a identificar
	 * @return array
	 */
	public function except($keys): array
	{
		return $this->remove($keys)->all();
	}
	/**
	 * Divide un array en fragmentos
	 * @param  int          $length        El tamaño de cada fragmento.
	 * @param  bool $preserve_keys Cuando se establece en true las keys serán preservadas
	 * @return array
	 */
	public function chunk(int $length, bool $preserve_keys = false): array
	{
		return array_chunk($this->items, $length, $preserve_keys);
	}
	/**
	 * Devuelve los valores de una sola columna del array de entrada
	 * @param  mixed   $column_key La columna de valores a devolver
	 * @param  mixed   $index_key  La columna a usar como los índices/claves para el array devuelto
	 * @return array
	 */
	public function column(mixed $column_key, mixed $index_key = null): array
	{
		return array_column($this->items, $column_key, $index_key);
	}
	/**
	 * Crea un nuevo array, usando una matriz para las claves y otra para sus valores
	 * @param  array  $values Array de valores a usar
	 * @return array
	 */
	public function combine(array $values): array|false
	{
		return array_combine($this->items, $values);
	}
	/**
	 * Divide la matriz en una matriz multidimención compuesto de claves y valores
	 * @return array
	 */
	public function divide(): array
	{
		return [array_keys($this->items), array_values($this->items)];
	}
	/**
	 * Cuenta los items de una matriz o sus valores
	 * @param  bool $countValue si es true contara los valores de la matriz
	 * @return int|array
	 */
	public function count(bool $countValue = false): int|array
	{
		return $countValue ? array_count_values($this->items) : count($this->items);
	}

	/**
	 * Calcula la diferencia entre arrays
	 * @param array|string $flat Tipo de Calculo
	 * @param callable|null|array $callback  Funciones o demas Matrices
	 * @param ...array $array demas matrices
	 * @return array
	 */
	public function diff(array|string $flat = self::DIFF, callable|null|array $callback = null, ...$array): array
	{
		if (is_array($flat)) {
			$array = func_get_args();
			$flat = self::DIFF;
			$callback = null;
		}
		
		if (is_callable($callback)) {
			$array = func_get_args();
			if (is_string($array[0])) {
				$flat = $array[0];
				unset($array[0]);
			}

			$callback = $array[1];
			unset($array[1]);
		}

		switch ($flat) {
			case self::DIFF:
				return array_diff($this->items, ...$array);
			break;
			case self::DIFF_ASSOC:
				return array_diff_assoc($this->items, ...$array);
			break;
			case self::DIFF_KEY:
				return array_diff_key($this->items, ...$array);
			break;
			case self::DIFF_UKEY:
				return array_diff_ukey($this->items, $array[array_key_first($array)], $callback);
			break;
			case self::DIFF_UASSOC:
				return array_diff_uassoc($this->items, $array[array_key_first($array)], $callback);
			break;
		}

		return [];		
	}
	/**
	 * Añade y mescla matrices
	 * @param  array $array Matrices a combinar con la matriz actual
	 * @return array 
	 */
	public function merge(...$array): array
	{
		return array_merge($this->items, ...$array);
	}
	/**
	 * Anade y mescla matrices de forma recursiva
	 * @param  array $array Matrices a combinar
	 * @return array
	 */
	public function mergeRecursive(...$array): array
	{
		return array_merge_recursive($this->items, ...$array);
	}
	/**
	 * Extiende una matriz
	 * @param  array $array Matriz a combinar
	 * @return array
	 */
	public function extend(array $array): array
	{
		foreach ($array as $key => $value) {
			if ($this->exists($key)) {
				if (is_array($value)) {
					$this->items[$key] = Arr::from($this->items[$key])->extend($value);
				} else {
					$this->items[$key] = $value;
				}
			}
		}

		return $this->items;
	}
	/**
	 * Filtra datos de una matriz 
	 * @param  callable $callback Función de filtrado
	 * @return array
	 */
	public function filter(callable $callback = null): array
	{
		return array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
	}
	/**
	 * Filtra una matriz y elimina los valores nulos
	 * @return array
	 */
	public function filterNotNull(): array
	{
		return $this->filter(function ($value, $key) {
			return ! is_null($value);
		});
	} 
	/**
	 * Intercambia todas la claves por sus valores conviritendolo en una matriz asociativa
	 * @param bool  $returnClass  Si  true retornara una instancia de `Arr`
	 * @return array
	 */
	public function flip(bool $returnClass = false): Arr|array
	{
		$newItems = array_flip($this->items);
		return $returnClass ? new static($newItems) : $newItems;
	}
	/**
	 * Obtiene el valor del primer elemento
	 * @return mixed
	 */
	public function first(): mixed
	{
		return $this->items[array_key_first($this->items)];
	}
	/**
	 * Obtiene el valor del Ultimo elemento
	 * @return mixed
	 */
	public function last(): mixed
	{
		return $this->items[
			array_key_last(
				$this->items
			)
		];
	}
	/**
	 * Obtiene las claves de la matriz
	 * @return array
	 */
	public function keys(): array
	{	
		return array_keys($this->items);
	}
	/**
	 * Mapea la matriz y retorna su valores por una funcion dada
	 * @param  callable|null $callback la función a pasar o null para usar el mapeo natura
	 * @return array
	 */
	public function map(?callable $callback = null): array
	{
		$keys = $this->keys();

		try {
			$items = array_map($callback, $this->items, $keys);
		} // @codeCoverageIgnoreStart
		catch (ArgumentCountError) {
			$items = array_map($callback, $this->items);
		}
		// @codeCoverageIgnoreEnd

		return array_combine($keys,$items);
	}

	/**
	 * Busca una clave por su valor
	 * @param  mixed        $search_value el valor a buscar
	 * @param  bool $strict       Indica si la busquedad debe ser estricta
	 * @return array
	 */
	public function searchKeyByValue(mixed $search_value, bool $strict = false): array
	{
		return array_keys($this->items, $search_value, $strict);
	}
	/**
	 * Busca un valor en la matriz y retorna su clave
	 * @param  mixed        $needle El valor a Buscar
	 * @param  bool $strict Indica si es stricto
	 * @return int|string|false
	 */
	public function search(mixed $needle, bool $strict = false): int|string|false
	{
		return array_search($needle, $this->items, $strict);
	}
	/**
	 * Retorna una matriz solo con las claves dadas
	 * @param  mixed  $keys Lista de Claves
	 * @return array
	 */
	public function only(mixed $keys): array
	{
		return $this->intersectByKeys(
			array_flip( (array) $keys)
		)->get();
	}
	/**
	 * Calcula la intersección de arrays
	 * @param  arrat $items Un array con el que comparar los valores.
	 * @return Arr
	 */
	public function intersect(array $items): Arr
	{
		return new static(array_intersect($this->items, $items));
	}
	/**
	 * Calcula la intersección de arrays usando sus claves para la comparación
	 * @param  array $items Un array con el que comparar las claves.
	 * @return Arr
	 */
	public function intersectByKeys(array $items): Arr
	{
		return new static(array_intersect_key($this->items, $items));
	}
	/**
	 * Retorna toda la Matriz
	 * @return array
	 */
	public function all(): array
	{
		return $this->items;
	}
	/**
	 * Retorna la matriz en formato JSON
	 * @param  int $flags Máscara de bits, para conocerlas visite [Constantes de JSON.](https://www.php.net/manual/es/json.constants.php)
	 * @return string
	 */
	public function toJson(int $flags = 0): string
	{
		return json_encode($this->items, $flags);
	}
	/**
	 * Verifica si el Elemento esta Vacia
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->items);
	}
	/**
	 * Une elementos de un array en un string
	 * @param  string $glue      Separador de los elementos
	 * @param  string $finalGlue Separador final de los elementos
	 * @return string
	 */
	public function join(string $glue, string $finalGlue = ''): string
	{
		if ($this->count() === 0) {
            return '';
        }

        if ($this->count() === 1) {
            return end($this->items);
        }

		if ($finalGlue === '') {
            return implode($glue, $this->items);
        }

        $finalItem = array_pop($this->items);

        return implode($glue, $this->items).$finalGlue.$finalItem;
	}
	/**
	 * Rellena un array a la longitud especificada con un valor
	 * @param  int    $length Nuevo tamaño del array.
	 * @param  mixed  $value  Valor a rellenar si array es menor que `$length`.
	 * @return array
	 */
	public function pad(int $length, mixed $value): array
	{
		return array_pad($this->items, $length, $value);
	}
	/**
	 * Inserta uno o más elementos al final de un array
	 * @param  mixed $values Valores a añadir
	 * @return int
	 */
	public function push(mixed ...$values): int
	{
		return array_push($this->items, ...$values);
	}
	/**
	 * Remueve un elemento y retora su valor
	 * @param  mixed $key     La clave
	 * @param  mixed $default El Valor por defecto a retornar si la clave no existe
	 * @return mixed
	 */
	public function pull($key, $default = null)
	{
		$val = $this->get($key, $default);
		$this->remove($key);
		return $val;
	}
	/**
	 * Añade un elemento al principio de la matriz
	 * @param  mixed $value Los valores
	 * @param  mixed $key   La nueva clave del valor
	 * @return Arr
	 */
	public function prepend($value, $key = null): Arr
	{
		if (func_num_args() == 1) {
            array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }

        return $this;
	}

	/**
	 * Remueve uno o varias elementos de la matriz por su claves
	 * @param  mixed $keys La claves
	 * @return Arr
	 */
	public function remove($keys = null): Arr
	{
		if (is_null($keys)) {
			$this->items = [];
			return $this;
		}

		$keys = (array) $keys;
		
		if (count($keys) === 0) {
			return $this;
		}

		$original = &$this->items;

		$this->forget($this->items, $keys, $original);
		

		return $this;
	}
	/**
	 * Reemplaza los elementos del array original con elementos de array adicionales
	 * @param  array|bool $recursive    Indica si el reemplazo es recursivo
	 * @param  array      ...$replacements Arrays de los cuales se extraerán los elementos
	 * @return array   
	 */
	public function replace(array|bool $recursive = false, ...$replacements): array
	{
		if (is_array(func_get_arg(0))) {
			$replacements = func_get_args();
			$recursive = false;
		}

		return $replacements ?
			array_replace_recursive($this->items, ...$replacements) :
			array_replace($this->items, ...$replacements);
	}
	/**
	 * Devuelve un array con los elementos en orden inverso
	 * @param  bool $preserve_keys Indica si se desea preservar las claves
	 * @return array
	 */
	public function reverse(bool $preserve_keys = true): array
	{
		return array_reverse($this->items, $preserve_keys);
	}
	/**
	 * Elimina valores duplicados de un array
	 * @param int  $flags Se utiliza para modificar el tipo de orden usando, para saber más visite [`array-unique PHP`](https://www.php.net/manual/es/function.array-unique.php)
	 * @return Arr
	 */
	public function unique(int $flags = SORT_STRING): Arr
	{
		$this->items = array_unique($this->items, $flags);
		return $this;
	}
	/**
	 * Permite Unir la matriz actual con otra matriz similar a merge
	 * @param  array $items la matriz para unificar
	 * @return Arr
	 */
	public function union(array $items): Arr
	{
		return new static($this->items + $items);
	}
	/**
	 * Retorna los valores de la matriz
	 * @return arrays
	 */
	public function values(): array
	{
		return array_values($this->items);
	}
	/**
	 * Generar una cadena de consulta codificada estilo URL a partir de la matriz actual
	 * @return string
	 */
	public function query(): string
	{
		return http_build_query($this->items, "", "&", PHP_QUERY_RFC3986);
	}
	/**
	 * Ordena la matriz actual
	 * @param  mixed $callback función o nulo
	 * @return Arr
	 */
	public function sort($callback = null): Arr
	{
		$items = $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback ?? SORT_REGULAR);

        return new static($items);
	}
	/**
	 * Ordena una matriz de forma decendente
	 * @param  int $options Se utiliza para modificar el tipo de orden usando 
	 * @return Arr
	 */
	public function sortDesc(int $options = SORT_REGULAR): Arr
	{
		$items = $this->items;

        arsort($items, $options);

        return new static($items);
	}
	/**
	 * Orderna la matriz por las claves
	 * @param int $options Se utiliza para modificar el tipo de orden usando 
	 * @param bool $descending Indica si es asedente o desendiente
	 * @return Arr
	 */
	public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): Arr
    {
        $items = $this->items;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Orderna la matriz por las claves de forma desendente
     *
     * @param  int  $options Se utiliza para modificar el tipo de orden usando 
     * @return Arr
     */
    public function sortKeysDesc(int $options = SORT_REGULAR): Arr
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Ordene las claves de colección mediante una devolución de llamada.
     *
     * @param  callable(TKey, TKey): int  $callback La función de comparación
     * @return static
     */
    public function sortKeysUsing(callable $callback): Arr
    {
        $items = $this->items;

        uksort($items, $callback);

        return new static($items);
    }

    // Maggic

    public function __set($key, $value)
    {
    	$this->set($key, $value);
    }

    public function __get($key)
    {
    	return $this->get($key);
    }

	public function __toString()
	{
		return $this->toJson();
	}
	
	public function __destruct()
	{
		$this->items = [];
	}

	// Protected

	protected function dots($key): array
	{
		$keys = self::wrap($key);
		
		if (is_array($key)) {
			$keys = $key;
		}

		if (is_float($key)) {
			$keys = [(string) $key];
		}

		if (is_string($key)) {
			if (str_contains($key, ".")) {
				$keys = explode('.', $key);
			}
		}

		return $keys;
	}

	public function forget(&$array, $keys, &$original)
	{
		foreach ($keys as $key) {
			if ($this->exists($key)) {
				unset($array[$key]);
				continue;
			}

			$parts = $this->dots($key);

			$array = &$original;

			while (count($parts) > 1) {
				$part = array_shift($parts);

				if (isset($array[$part])) {
					$array = &$array[$part];
				} else {
					// @codeCoverageIgnoreStart
					continue 2;
					// @codeCoverageIgnoreEnd
				}
			}
				
			unset($array[array_shift($parts)]);
		}
	}
}

?>