<?php
namespace H4B\Content;

use DateTime;
use Iterator;

/** @implements Iterator<NewsItem> */
class NewsList implements Iterator {
	/** @var array<string> Absolute file paths of markdown files */
	private array $filePathArray;
	private int $iteratorKey;
	private NewsFactory $factory;

	public function __construct() {
		$this->filePathArray = array_reverse(glob("data/news/*.md"));
		$this->iteratorKey = 0;
		$this->factory = new NewsFactory();
	}

	public function current():NewsItem {
		return $this->factory->createFromFile($this->filePathArray[$this->iteratorKey]);
	}

	public function next():void {
		$this->iteratorKey++;
	}

	public function key():int {
		return $this->iteratorKey;
	}

	public function valid():bool {
		return isset($this->filePathArray[$this->iteratorKey]);
	}

	public function rewind():void {
		$this->iteratorKey = 0;
	}
}
