<?php
if (!defined("DS")) {
	define("DS", DIRECTORY_SEPARATOR);
}

if (!defined("ROOT_TEST")) {
	define("ROOT_TEST", __DIR__.DS);
}

if (!defined("PATH_FILE")) {
	define("PATH_FILE", ROOT_TEST."files".DS);
}

if (!defined("CONFIG_PATH")) {
	define("CONFIG_PATH", PATH_FILE."config");
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Rep98\Collection\Helpers\Config;

Config::from([
	"logging" => [
		"path" => ROOT_TEST."log".DS
	]
]);
?>