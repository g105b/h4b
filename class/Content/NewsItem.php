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
		return $this->publishDate->format("l jS M Y");
	}

	#[BindGetter]
	public function getContent():string {
		return $this->html;
	}
}
