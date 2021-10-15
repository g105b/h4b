<?php
namespace H4B\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class ContentRepository {
	public function get(string $name):string {
		$dir = "data/content";
		$file = "$dir/$name.md";
		if(!is_file($file)) {
			throw new ContentNotFoundException($name);
		}

		return file_get_contents($file);
	}

	public function getHTML(string $name):string {
		$markdown = $this->get($name);

		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		return $converter->convertToHtml($markdown);
	}
}
