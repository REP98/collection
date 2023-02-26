<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use ArgumentCountError;
use ArrayAccess;

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

	/**
	 * Matriz interna
	 * @var array
	 */
	protected $items = [];

	// STATIC METHOD
	/**
	 * Singleton de Arr
	 * @static
	 * @param  mixed|array $array Matriz de Datos
	 * @return \Rep98\EMW\Lib\Helpers\Arr
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
	public static function wrap($value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
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
    public static function isList($array): bool
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
		$this->addItem($array);
	}
	
	/**
	 * Añade o reemplaza el array actual
	 * @example 
	 *  $a = Array::from(['Dog', 'Cat'])->addItem(['MyKey' => 'MyValue']);
	 *  print($a->all()) // Print [MyKey => 'MyValue']
	 * @param mixed|array $array Matriz de reemplazo
	 * @return \Rep98\EMW\Lib\Helpers\Arr
	 */
	public function addItem(mixed $array = []): Arr
	{
		$this->items = (array) $array;
		return $this;
	}
	/**
	 * Añade una elemento si no existe
	 * @param string $key   la clave
	 * @param mixed $value la value
	 * @return \Rep98\EMW\Lib\Helpers\Arr
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
        	($array->count() > 1 ? 
        	$array->all() : 
        	$array->all()[0]);
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
	
	public function shuffle($seed = null): array
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

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->set($offset, $value);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	public function offsetUnset(mixed $offset): void
	{
		if ($this->offsetExists($offset)) {
			$this->remove($offset);
		}
	}

	public function has(mixed $offset): bool
	{
		return $this->offsetExists($offset);
	}

	public function except($keys): array
	{
		return $this->remove($keys)->all();
	}

	public function chunk(int $length, bool $preserve_keys = false): array
	{
		return array_chunk($this->items, $length, $preserve_keys);
	}

	public function column(int|string|null $column_key, int|string|null $index_key = null): array
	{
		return array_column($this->items, $column_key, $index_key);
	}

	public function combine(array $values): array
	{
		return array_combine($this->items, $values);
	}

	public function divide()
	{
		return [array_keys($this->items), array_values($this->items)];
	}

	public function count(bool $countValue = false): int|array
	{
		return $countValue ? array_count_values($this->items) : count($this->items);
	}

	public function diff(array|string $flat = self::DIFF, callable|null|array $callback = null, ...$array): array
	{
		if (is_array(func_get_arg(0))) {
			$array = func_get_args();
			$flat = self::DIFF;
			$callback = null;
		}
		
		if (is_callable(func_get_arg(0))) {
			$flat = self::DIFF;
			$array = func_get_args();
			$callback = $array[0];
			unset($array[0]);
		}

		if (is_callable(func_get_arg(1))) {
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

	public function merge(...$array): array
	{
		return array_merge($this->items, ...$array);
	}

	public function mergeRecursive(...$array): array
	{
		return array_merge_recursive($this->items, ...$array);
	}

	public function extend($array)
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

	public function filter(callable $callback)
	{
		return array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
	}

	public function filterNotNull()
	{
		return $this->filter(function ($value) {
			return ! is_null($value);
		});
	}

	public function flip()
	{
		return array_flip($this->items);
	}

	public function first()
	{
		return $this->items[array_key_first($this->items)];
	}

	public function last()
	{
		return $this->items[
			array_key_last(
				$this->items
			)
		];
	}

	public function keys(): array
	{
		return array_keys($this->items);
	}

	public function map(?callable $callback): array
	{
		$keys = $this->keys();

		try {
			$items = array_map($callback, $this->items, $keys);
		} catch (ArgumentCountError) {
			$items = array_map($callback, $this->items);
		}

		return array_combine($keys,$items);
	}


	public function searchKeyByValue(mixed $search_value, bool $strict = false): array
	{
		return array_keys($this->items, $search_value, $strict);
	}

	public function search(mixed $needle, bool $strict = false): int|string|false
	{
		return array_search($needle, $this->items, $strict);
	}

	public function only(mixed $keys)
	{
		return array_intersect_key(
			$this->items,
			array_flip( (array) $keys)
		);
	}

	public function intersect($items)
	{
		return new static(array_intersect($this->items, $items));
	}

	public function intersectByKeys($items)
	{
		return new static(array_intersect_key($this->items, $items));
	}

	public function all(): array
	{
		return $this->items;
	}

	public function toJson(int $flags = 0)
	{
		return json_encode($this->items, $flags);
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function join($glue, $finalGlue = '')
	{
		if ($finalGlue === '') {
            return implode($glue, $this->items);
        }

        if ($this->count() === 0) {
            return '';
        }

        if ($this->count() === 1) {
            return end($this->items);
        }

        $finalItem = array_pop($this->items);

        return implode($glue, $this->items).$finalGlue.$finalItem;
	}

	public function pad(int $length, mixed $value): array
	{
		return array_pad($this->items, $length, $value);
	}

	public function push(mixed ...$values): int
	{
		return array_push($this->items, ...$values);
	}

	public function pull($key, $default = null)
	{
		$val = $this->get($key, $default);
		$this->remove($key);
		return $val;
	}

	public function prepend($value, $key = null): Arr
	{
		if (func_num_args() == 1) {
            array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }

        return $this;
	}

	public function remove($key = null): Arr
	{
		if (is_null($key)) {
			$this->items = [];
			return $this;
		} 
		$keys = (array) $key;
		
		if (count($keys) === 0) {
			return $this;
		} 

		$original = &$this->items;
		$array = $this->items;
		
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
					continue 2;
				}
			}

			unset($array[array_shift($parts)]);
		}
		$this->items = $array;
		return $this;
	}

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

	public function reverse(bool $preserve_keys = true): array
	{
		return array_reverse($this->items, $preserve_keys);
	}

	public function unique(int $flags = SORT_STRING): Arr
	{
		$this->items = array_unique($this->items, $flags);
		return $this;
	}

	public function union($items)
	{
		return new static($this->items + $items);
	}

	public function values(): Arr
	{
		return array_values($this->items);
	}

	public function query(): string
	{
		return http_build_query($this->items, "", "&", PHP_QUERY_RFC3986);
	}

	public function sort($callback = null)
	{
		$items = $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback ?? SORT_REGULAR);

        return new static($items);
	}

	public function sortDesc($options)
	{
		$items = $this->items;

        arsort($items, $options);

        return new static($items);
	}

	public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->items;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys in descending order.
     *
     * @param  int  $options
     * @return static
     */
    public function sortKeysDesc(init $options = SORT_REGULAR)
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Sort the collection keys using a callback.
     *
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function sortKeysUsing(callable $callback)
    {
        $items = $this->items;

        uksort($items, $callback);

        return new static($items);
    }

    public static function loadPath(string $file): Arr|bool
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

    // Maggic

    public function __set($key, $value)
    {
    	$this->set($key, $value);
    }

    public function __get($key)
    {
    	return $this->get($key);
    }

	public function __serialize(): array
	{
		return $this->items;
	}

	public function __unserialize(array $serialized): void
	{
		$this->items = $this->merge($serialized);
	}

	public function __destruct()
	{
		$this->items = [];
	}

	// Protected

	protected function dots($key): array
	{
		$keys = (array) $key;
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
}

?>