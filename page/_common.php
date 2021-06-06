<?php
namespace H4B\Page;

use Gt\Dom\Text;
use Gt\DomTemplate\Element;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\Logic\Page;
use H4B\Content\ContentRepository;
use League\CommonMark\HtmlElement;

class _CommonPage extends Page {
	public function go():void {
		$this->selectMenu();
		$this->outputContent();
		$imageContainerList = $this->imageContainers();
		$this->imageSourceSet($imageContainerList);
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

	/** @return HtmlElement[] HTMLParagraphElement[] */
	private function imageContainers():array {
		$imageContainerList = [];

		foreach($this->document->querySelectorAll("article>p") as $paragraph) {
			if($paragraph->classList->contains("float")) {
				continue;
			}
			foreach($paragraph->childNodes as $child) {
				if($child instanceof Text) {
					if(trim($child->nodeValue) !== "") {
						continue(2);
					}
				}
				elseif($child->tagName !== "img") {
					continue(2);
				}
			}

			array_push($imageContainerList, $paragraph);
		}

		foreach($imageContainerList as $container) {
			$container->classList->add("image-container");
		}

		return $imageContainerList;
	}

	private function imageSourceSet(array $imageContainerList):void {
		foreach($imageContainerList as $container) {
			foreach($container->querySelectorAll("img") as $img) {
				$this->imageSourceSetApply($img);
			}
		}
	}

	private function imageSourceSetApply(Element $img):void {
		$src = urldecode($img->src);
		$sizeList = [200, 800];

		$srcsetArray = [];
		$sizesArray = [];

		foreach($sizeList as $size) {
			$doubleSize = $size * 2;

			$sizeSrc = str_replace(
				"/photo/",
				"/photo/thumb-$size/",
				$src
			);

			$fullPathSrc = Path::getApplicationRootDirectory(__DIR__) . $src;
			$fullPathSizeSrc = Path::getApplicationRootDirectory(__DIR__) . $sizeSrc;

			if(!file_exists($fullPathSizeSrc)) {
				$this->createImageSource($size, $fullPathSrc, $fullPathSizeSrc);
			}

			array_push($srcsetArray, "${sizeSrc} ${size}w");
			array_push($sizesArray, "(max-width: ${doubleSize}px) ${size}px");
		}

		$img->setAttribute("srcset", implode(",", $srcsetArray));
		$img->setAttribute("sizes", implode(",", $sizesArray));
	}

	private function createImageSource(
		int $size,
		string $fullPathSrc,
		string $fullPathSizeSrc
	):void {
		[$widthOriginal, $heightOriginal] = getimagesize($fullPathSrc);
		$ratio = $size / $widthOriginal;
		$widthNew = $widthOriginal * $ratio;
		$heightNew = $heightOriginal * $ratio;

		$image = imagecreatefromjpeg($fullPathSrc);
		$imageResized = imagecreatetruecolor($widthNew, $heightNew);
		imagecopyresampled(
			$imageResized,
			$image,
			0,
			0,
			0,
			0,
			$widthNew,
			$heightNew,
			$widthOriginal,
			$heightOriginal
		);

		if(!is_dir(dirname($fullPathSizeSrc))) {
			mkdir(dirname($fullPathSizeSrc), 0775, true);
		}
		imagejpeg($imageResized, $fullPathSizeSrc, 50);
	}
}
