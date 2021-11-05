<?php
use Gt\DomTemplate\DocumentBinder;
use H4B\Content\NewsList;

function go(NewsList $newsList, DocumentBinder $binder):void {
	$binder->bindList($newsList);
}
