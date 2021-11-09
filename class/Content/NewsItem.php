<?php
namespace H4B\Content;

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

	private function getHTML():string {
		$environment = new Environment([
			"allow_unsafe_links" => true
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		return $converter->convertToHtml($this->markdown);
	}

	/** @return array<string> List of public paths */
	public function getPhotoList():array {
		$dateString = $this->getDateString();
		$glob = glob("asset/photo/news/$dateString-*.jpg");
		return array_map(fn($str) => "/$str", $glob);
	}
}
