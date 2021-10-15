<?php
namespace H4B;

use Gt\ServiceContainer\LazyLoad;
use H4B\Content\ContentRepository;

class ServiceLoader {
	#[LazyLoad(ContentRepository::class)]
	public function getContentRepository():ContentRepository {
		return new ContentRepository();
	}
}
