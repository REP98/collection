<?php 
namespace Rep98\Collection\Interface;

use Nette\Schema\Schema;

interface Configurable {
	/**
	 * Establece el Esquema de configuración
	 * @return \Nette\Schema\Schema
	 */
	public static function getSchema(): Schema;
	/**
	 * Estabece el namespace de la configuración
	 * @return string
	 */
	public static function getNameSchema(): string;
}
?>