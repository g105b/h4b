<?php
namespace H4B\Content;

use DateTime;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class NewsFactory {
	public function createFromFile(string $filePath):NewsItem {
		$dateString = pathinfo($filePath, PATHINFO_FILENAME);
		$publishDate = new DateTime($dateString);

		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		$html = $converter->convertToHtml(file_get_contents($filePath));

		return new NewsItem(
			$publishDate,
			$html
		);
	}
}
