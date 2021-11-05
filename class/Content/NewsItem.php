<?php
namespace H4B\Content;

use Gt\DomTemplate\BindGetter;

class NewsItem {
	public function __construct(
		private \DateTime $publishDate,
		private string $html
	) {}

	#[BindGetter]
	public function getDateString():string {
		return $this->publishDate->format("jS M Y");
	}

	#[BindGetter]
	public function getContent():string {
		return $this->html;
	}

	#[BindGetter]
	public function getPreview():string {
		$content = $this->getContent();
		preg_match("/<p>(.+)<\/p>/", $content, $matches);
		$firstParagraphText = $matches[1];
		return mb_strimwidth($firstParagraphText, 0, 150, "...");
	}

	#[BindGetter]
	public function getDateID():string {
		return $this->publishDate->format("Y-m-d");
	}
}
