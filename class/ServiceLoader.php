<?php
namespace H4B;

use Gt\ServiceContainer\LazyLoad;
use H4B\Content\ContentRepository;
use H4B\Content\NewsList;

class ServiceLoader {
	#[LazyLoad(ContentRepository::class)]
	public function getContentRepository():ContentRepository {
		return new ContentRepository();
	}

	#[LazyLoad(NewsList::class)]
	public function getNewsList():NewsList {
		return new NewsList();
	}
}
