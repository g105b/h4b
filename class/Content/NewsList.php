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
	private string $nowDateString;

	public function __construct() {
		$this->filePathArray = array_reverse(glob("data/news/*.md"));
		$this->iteratorKey = 0;
		$this->factory = new NewsFactory();
		$this->nowDateString = date("Y-m-d");
	}

	public function current():NewsItem {
		$filePath = $this->filePathArray[$this->iteratorKey];
		return $this->factory->createFromFile($filePath);
	}

	public function next():void {
		$this->iteratorKey++;
	}

	public function key():int {
		return $this->iteratorKey;
	}

	public function valid():bool {
// TODO: Test future dates do not show.
		$isset = isset($this->filePathArray[$this->iteratorKey]);
		if(!$isset) {
			return false;
		}

		$dateString = pathinfo($this->filePathArray[$this->iteratorKey], PATHINFO_FILENAME);
		if($dateString > $this->nowDateString) {
			return false;
		}

		return true;
	}

	public function rewind():void {
		$this->iteratorKey = 0;
	}
}
