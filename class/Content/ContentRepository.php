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
		$markdown = $this->expandSpecialImages($markdown);

		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		return $converter->convertToHtml($markdown);
	}

	private function expandSpecialImages(string $markdown):string {
		preg_match_all(
			"/{{images\((?P<DIRNAME>[^)]*)\): (?P<RANGE>[\d\-]+)}}/",
			$markdown,
			$matches
		);
		if(!empty($matches)) {
			foreach($matches[0] as $i => $fullMatch) {
				$imgText = "";

				$dirname = $matches["DIRNAME"][$i];
				$dirPath = "asset/photo/$dirname";
				$rangeParts = explode("-", $matches["RANGE"][$i]);
				$minRange = $rangeParts[0];
				$maxRange = $rangeParts[1] ?? $minRange;

				for($imgIndex = $minRange; $imgIndex <= $maxRange; $imgIndex++) {
					$filePath = glob("$dirPath/$imgIndex.*.jpg")[0] ?? null;
					if(!$filePath) {
						continue;
					}

					$title = pathinfo($filePath, PATHINFO_FILENAME);
					$title = substr($title, strpos($title, ".") + 1);
					$title = str_replace("_", " ", $title);
					$title = trim($title);

					$imgText .= "![$title](/$filePath) ";
				}
				$imgText .= "\n\n";

				$markdown = str_replace($fullMatch, $imgText, $markdown);
			}
		}

		return $markdown;
	}
}
