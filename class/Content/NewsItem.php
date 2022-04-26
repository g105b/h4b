<?php
namespace H4B\Content;

use DateTime;
use Gt\DomTemplate\BindGetter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class NewsItem {
	public function __construct(
		private \DateTime $publishDate,
		private string $markdown
	) {}

	#[BindGetter]
	public function getDateString():string {
		return $this->publishDate->format("Y-m-d");
	}

	#[BindGetter]
	public function getFriendlyDateString():string {
		return $this->publishDate->format("jS M Y");
	}

	#[BindGetter]
	public function getSourceContent():string {
		return $this->markdown;
	}

	#[BindGetter]
	public function getContent():string {
		return $this->getHTML();
	}

	#[BindGetter]
	public function getPreview():string {
		$content = $this->getContent();
		preg_match("/<p>(.+)<\/p>/", $content, $matches);
		$firstParagraphText = $matches[1];
		return mb_strimwidth($firstParagraphText, 0, 150, "...");
	}

	#[BindGetter]
	public function getEditable():bool {
		return $this->publishDate > new DateTime("2021-11-08");
	}

	/** @return array<string> URI paths of images */
	public function getImages():array {
		if(!$this->getEditable()) {
			return [];
		}

		$dateString = $this->getDateString();
		$fileArray = glob("asset/photo/news/$dateString*");

		$imageList = [];
		foreach($fileArray as $file) {
			$baseName = pathinfo($file, PATHINFO_BASENAME);
			$baseName = rawurlencode($baseName);
			$fileName = pathinfo($file, PATHINFO_FILENAME);

			$fileNameNoDate = substr($fileName, strlen($dateString));
			$fileNameNoDate = trim($fileNameNoDate, "-");

			$uri = "/asset/photo/news/$baseName";
			$imageList[$fileNameNoDate] = $uri;
		}

		return $imageList;
	}

	private function getHTML():string {
		$markdown = $this->markdown;

		$images = $this->getImages();
		if($images) {
			$markdown .= "\n\n";
		}

		foreach($images as $title => $uri) {
			$markdown .= "![$title]($uri) ";
		}

		if($images) {
			$markdown .= "\n";
		}

		$markdown = $this->expandSpecialImages($markdown);

		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		return $converter->convertToHtml($markdown);
	}

	/** @return array<string> List of public paths */
	public function getPhotoList():array {
		$dateString = $this->getDateString();
		$glob = glob("asset/photo/news/$dateString-*.jpg");
		return array_map(fn($str) => "/$str", $glob);
	}

	private function expandSpecialImages(string $markdown):string {
		preg_match_all(
			"/{{images\((?P<DIRNAME>[^)]*)\): (?P<RANGE>[\d\-]+)}}/",
			$markdown,
			$matches
		);
		if(!empty($matches)) {
			$imgText = "";

			foreach($matches[0] as $i => $fullMatch) {
				$dirname = $matches["DIRNAME"][$i];
				$dirPath = "asset/photo/news/$dirname";
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
