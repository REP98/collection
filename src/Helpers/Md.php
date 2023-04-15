<?php 
declare(strict_types=1);
namespace Rep98\Collection\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
use Rep98\Collection\Exceptions\FileNotFound;

/**
 * Md
 */
class Md
{
	/**
	 * Renderiza un texto markdown a `Html`
	 * @param  string $text    El texto markdown
	 * @param  array  $options Opciones del Environment
	 * @return `string`
	 */
	public static function render(string $text, array $options = []): string
	{
		$gfmc = new GithubFlavoredMarkdownConverter($options);
		return (string) $gfmc->convert($text);
	}
	/**
	 * Imprime un bloque de codigo Markdown en `Html`
	 * @param  string $text    El texto markdown
	 * @param  array  $options Opciones del Environment
	 * @return `string`
	 */
	public static function inline(string $text, array $options = []): string
	{
		$environment = new Environment($options);

        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new InlinesOnlyExtension());

        $converter = new MarkdownConverter($environment);

        return (string) $converter->convert($text);
	}
	/**
	 * Carga un archivo Markdown e imprime un `Html`
	 * @param  string $file    Ruta del archivo
	 * @param  array  $options Opciones del Environment
	 * @return `string`
	 */
	public static function load(string $file, array $options = []): string
	{
		if (!file_exists($file)) {
			throw new FileNotFound("File {$file} does not exist or cannot be loaded.", 1);
		}
		$md = file_get_contents($file);
		
		return self::render($md);
	}
}
?>