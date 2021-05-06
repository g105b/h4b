<?php
namespace H4B\Content;

use Gt\WebEngine\FileSystem\Path;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\MarkdownConverter;

class ContentRepository {
	public function get(string $name):string {
		$dir = Path::getDataDirectory() . "/content";
		$file = "$dir/$name.md";
		if(!is_file($file)) {
			throw new ContentNotFoundException($name);
		}

		return file_get_contents($file);
	}

	public function getHTML(string $name):string {
		$markdown = $this->get($name);

		$environment = Environment::createCommonMarkEnvironment();
		$environment->addExtension(new AttributesExtension());
		$environment->mergeConfig([
			"allow_unsafe_links" => true
		]);

		$converter = new MarkdownConverter($environment);
		return $converter->convertToHtml($markdown);
	}
}
