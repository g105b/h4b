<?php
use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Gt\Dom\HTMLElement\HTMLElement;
use Gt\Dom\HTMLElement\HTMLFormElement;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use H4B\Content\NewsFactory;

function go(HTMLDocument $document, DynamicPath $path, DocumentBinder $binder, Uri $uri):void {
	if($uri->getPath() === "/news/editor") {
		return;
	}

	$id = $path->get();
	output(
		$id,
		$document->querySelector("form"),
		$document->querySelector("form.photos"),
		$binder
	);
}

function do_save(Input $input, DynamicPath $path):void {
	$id = $path->get();
	$factory = new NewsFactory();
	$savedId = $factory->save(
		$id,
		$input->getString("content"),
		$input->getDateTime("publishDate"),
		$input->getMultipleFile("photo"),
	);

	header("Location: /news/editor/$savedId");
	exit;
}

function do_deletePhoto(Input $input, Uri $uri):void {
	$photoId = $input->getString("photoId");
	$factory = new NewsFactory();
	$factory->deletePhoto($photoId);
	header("Location: " . $uri->getPath());
	exit;
}

function output(
	string $id,
	HTMLElement $form,
	HTMLElement $photoForm,
	DocumentBinder $binder
):void {
	if($id === "_new") {
		$binder->bindKeyValue("dateString", date("Y-m-d"));
		return;
	}

	$form->querySelector("[name='publishDate']")->readonly = true;
	$factory = new NewsFactory();
	$newsItem = $factory->createFromDateString($id);

	$binder->bindData($newsItem, $form);
	$binder->bindList($newsItem->getPhotoList(), $photoForm);
}
