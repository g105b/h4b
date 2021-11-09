<?php
namespace H4B\Content;

use DateTime;
use DateTimeInterface;
use Gt\Input\InputData\Datum\FailedFileUpload;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Input\InputData\Datum\MultipleInputDatum;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class NewsFactory {
	public function createFromFile(string $filePath):NewsItem {
		$dateString = pathinfo($filePath, PATHINFO_FILENAME);
		$publishDate = new DateTime($dateString);

		return new NewsItem(
			$publishDate,
			trim(file_get_contents($filePath))
		);
	}

	public function createFromDateString(string $dateString):NewsItem {
		$filePath = "data/news/$dateString.md";
		return $this->createFromFile($filePath);
	}

	public function save(
		string $id,
		string $content,
		DateTimeInterface $publishDate,
		MultipleInputDatum $photoUploads,
	):string {
		$savedDateString = $publishDate->format("Y-m-d");
		$fileName = "data/news/$savedDateString.md";

		if($id !== "_new") {
			if($id !== $savedDateString) {
				$oldFileName = "data/news/$id.md";
				rename($oldFileName, $fileName);

				// TODO: Move all photos.
			}
		}


		foreach($photoUploads as $photo) {
			/** @var FileUpload $photo */
			if($photo instanceof FailedFileUpload) {
				continue;
			}

			$originalFilename = $photo->getClientFilename();
			$title = pathinfo($originalFilename, PATHINFO_FILENAME);
			echo "<pre>";
			$newPath = "asset/photo/news/$savedDateString-$title.jpg";
			$photo->moveTo($newPath);
		}

		file_put_contents($fileName, $content);
		return $savedDateString;
	}

	public function delete(string $id):void {
		$fileName = "data/news/$id.md";
		if(is_file($fileName)) {
			if(!is_dir("data/news/deleted")) {
				mkdir("data/news/deleted");
			}

			rename($fileName, "data/news/deleted/$id.md");
		}
	}

	public function deletePhoto(string $photoId):void {
		unlink($photoId);
	}
}
