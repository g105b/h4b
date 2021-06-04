<?php
namespace H4B\Page;

use Gt\DomTemplate\Element;
use Gt\WebEngine\Logic\Page;
use H4B\Content\ContentRepository;

class _CommonPage extends Page {
	public function go():void {
		$this->selectMenu();
		$this->outputContent();
	}

	private function selectMenu():void {
		$uriPath = $this->server->getRequestUri()->getPath();
		foreach($this->document->querySelectorAll("body>header nav li a") as $anchor) {
			if(str_starts_with($uriPath, $anchor->href)) {
				if($anchor->href === "/") {
					if($anchor->href === $uriPath) {
						$anchor->parentNode->classList->add("selected");
					}
				}
				else {
					if(str_starts_with($uriPath, $anchor->href)) {
						$anchor->parentNode->classList->add("selected");
					}
				}
			}
		}
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
