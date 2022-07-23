<?php
use Gt\Config\Config;
use Gt\Dom\Element;
use Gt\Dom\ElementType;
use Gt\Dom\HTMLDocument;
use Gt\Dom\HTMLElement;
use Gt\Dom\NodeList;
use Gt\Dom\Text;
use Gt\Http\ServerInfo;
use Gt\Http\Uri;
use H4B\Content\ContentRepository;

function go(Uri $uri, ServerInfo $server, Config $config):void {
	if(str_starts_with($uri->getPath(), "/news/editor")) {
		$username = $server->getAuthUser();
		$password = $server->getAuthPassword();

		if(is_null($username) || ($username !== $config->getString("editor.username") || $password !== $config->getString("editor.password"))) {
			header("WWW-Authenticate: Basic realm='h4b'");
			header("HTTP/1.0 401 Unauthorized");
			echo "Unauthorised!";
			exit;
		}
	}
}

function go_after(
	Uri $requestUri,
	HTMLDocument $document,
	ContentRepository $contentRepo
):void {
	selectMenu($requestUri, $document);
	outputContent(
		$document->querySelectorAll("[data-content]"),
		$contentRepo
	);
	$imageContainerList = imageContainers($document);
	imageSourceSet($imageContainerList, $document);
	imageCaptions($imageContainerList, $document);
}

function selectMenu(Uri $uri, HTMLDocument $document):void {
	$uriPath = $uri->getPath();
	foreach($document->querySelectorAll("body>header nav li a") as $anchor) {
		if(str_starts_with($uriPath, $anchor->href)) {
			if($anchor->href === "/") {
				if($anchor->href === $uriPath) {
					$anchor->parentElement->classList->add("selected");
				}
			}
			else {
				$anchor->parentElement->classList->add("selected");
			}
		}
	}
}

function outputContent(NodeList $contentElementCollection, ContentRepository $contentRepo):void {
	foreach($contentElementCollection as $contentElement) {
		/** @var Element $contentElement */
		$name = $contentElement->dataset->content;
		$html = $contentRepo->getHTML($name);
		$contentElement->innerHTML = $html;
	}
}

/** @return HTMLElement[] */
function imageContainers(HTMLDocument $document):array {
	$imageContainerList = [];

	foreach($document->querySelectorAll("article p") as $paragraph) {
		if($paragraph->classList->contains("float")) {
			continue;
		}

		$skip = false;
		foreach($paragraph->childNodes as $child) {
			if($child instanceof Text) {
				if(trim($child->nodeValue) !== "") {
					$skip = true;
				}
			}
			elseif($child->elementType !== ElementType::HTMLImageElement) {
				$skip = true;
			}
		}

		if(!$skip) {
			array_push($imageContainerList, $paragraph);
		}
	}

	foreach($imageContainerList as $container) {
		$container->classList->add("image-container");
		$imageList = $container->querySelectorAll("img");
		if($imageList->length === 1) {
			$container->classList->add("individual");
		}
	}

	return $imageContainerList;
}

function imageSourceSet(array $imageContainerList, HTMLDocument $document):void {
	$fullImageList = array_merge(
		$imageContainerList,
		iterator_to_array($document->querySelectorAll("p.float"))
	);

	$totalCreatedCount = 0;

	foreach($fullImageList as $imgContainer) {
		foreach($imgContainer->querySelectorAll("img") as $img) {
			$totalCreatedCount += imageSourceSetApply($img);
		}
	}

	if($totalCreatedCount > 0) {
		header("Location: ./?srcset=$totalCreatedCount");
		exit;
	}
}

function imageSourceSetApply(Element $img):int {
	$src = urldecode($img->src);
	$sizeList = [200, 800];

	$srcsetArray = [];
	$sizesArray = [];
	$createdCount = 0;

	foreach($sizeList as $size) {
		$doubleSize = ($size * 2) + 100;

		$sizeSrc = str_replace(
			"/photo/",
			"/photo/thumb-$size/",
			$src
		);

		$fullPathSrc = ltrim($src, "/");
		$fullPathSizeSrc = ltrim($sizeSrc, "/");

		if(!is_file($fullPathSrc)) {
			continue;
		}

		if(!file_exists($fullPathSizeSrc)) {
			createImageSource($size, $fullPathSrc, $fullPathSizeSrc);
			$createdCount++;
		}

		$sizeSrcUrlEncoded = str_replace(" ", "%20", $sizeSrc);

		array_push($srcsetArray, "${sizeSrcUrlEncoded} ${size}w");
		array_push($sizesArray, "(max-width: ${doubleSize}px) ${size}px");
	}

	$img->setAttribute("srcset", implode(",", $srcsetArray));
	$img->setAttribute("sizes", implode(",", $sizesArray));
	$img->setAttribute("loading", "lazy");

	return $createdCount;
}

function createImageSource(
	int $size,
	string $fullPathSrc,
	string $fullPathSizeSrc
):void {
	[$widthOriginal, $heightOriginal] = getimagesize($fullPathSrc);
	$ratio = $size / $widthOriginal;
	$widthNew = round($widthOriginal * $ratio);
	$heightNew = round($heightOriginal * $ratio);

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
		$heightOriginal,
	);

	if(!is_dir(dirname($fullPathSizeSrc))) {
		mkdir(dirname($fullPathSizeSrc), 0775, true);
	}
	imagejpeg($imageResized, $fullPathSizeSrc, 50);
}

function imageCaptions(array $imageContainerList, HTMLDocument $document):void {
	foreach($imageContainerList as $imageContainer) {
		foreach($imageContainer->querySelectorAll("img") as $img) {
			$spanContainer = $document->createElement("span");
			$spanContainer->classList->add("image-with-caption");

			$spanCaption = $document->createElement("span");
			$spanCaption->classList->add("caption");
			$spanCaption->innerText = $img->alt;

			$originalParent = $img->parentNode;
			$spanContainer->appendChild($img);
			$spanContainer->appendChild($spanCaption);

			$originalParent->appendChild($spanContainer);
		}
	}
}
