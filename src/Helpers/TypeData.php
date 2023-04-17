<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
/**
 * TypeData
 */
class TypeData
{
	/**
	 * Almacena los squemas
	 */
	public Schema $schema;

	function __construct(
		public array $data = []
	)
	{
		$this->schema = $this->getStructure($data);
	}
	/**
	 * Retorna los resultados del squema y los datos
	 * @return array
	 */
	public function get(): array
	{
		return [
			"schema" => $this->schema,
			"data" => $this->data
		];
	}

	private function getStructure($data): Schema
	{
		$d = [];
		foreach ($data as $key => $value) {
			if (is_array($value) && count($value) > 0) {
				$d[$key] = $this->getStructure($value);
			} else {
				$d[$key] = $this->getType($value);
			}
		}
		return Expect::structure($d);
	}

	private function getType($value)
	{
		$type = gettype($value);
		if ($type == "boolean") {
			$type = "bool";
		}
		return Expect::{$type}($value);
	}
}
?>