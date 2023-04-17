<?php 
declare(strict_types=1);
namespace Rep98\Collection\Traits;
/**
 * Singleton
 */
trait Instances {
	private static $instance;

	public static function I()
	{
		if (empty(self::$instance)) {
			$args = func_get_args();
			self::$instance = new static(...$args);
		}
		return self::$instance;
	}
}
?>