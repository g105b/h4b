<?php
function go(\H4B\Content\NewsList $newsList, \Gt\DomTemplate\DocumentBinder $binder):void {
	$binder->bindList($newsList);
}

function do_delete():void {
	die("DELETE LOL");
}
