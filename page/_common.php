<?php
namespace H4B\Page;

use Gt\DomTemplate\Element;
use Gt\WebEngine\Logic\Page;
use H4B\Content\ContentRepository;

class _CommonPage extends Page {
	public function go():void {
		$this->outputContent();
	}

	private function outputContent():void {
		$contentRepo = new ContentRepository();

		foreach($this->document->querySelectorAll("[data-content]") as $contentElement) {
			/** @var Element $contentElement */
			$name = $contentElement->dataset["content"];
			$html = $contentRepo->getHTML($name);
			$contentElement->innerHTML = $html;
		}
	}
}
