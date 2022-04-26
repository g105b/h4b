document.querySelectorAll("body.dir--news article").forEach(article => {
	const maxHeight = innerHeight * 2;

	let height = article.getBoundingClientRect().height;
	if(height <= maxHeight) {
		return;
	}

	let readMore = document.createElement("div");
	readMore.classList.add("long-news-read-more");
	readMore.innerHTML = "<span>Read full article...</span>";
	readMore.addEventListener("click", e => {
		readMore.remove();
		article.classList.remove("long-news");
		window.scrollTo({
			top: article.offsetTop,
			left: 0,
			behavior: "smooth"
		});
	});

	article.classList.add("long-news");
	article.style.setProperty("--long-news-height", maxHeight + "px");
	article.appendChild(readMore);
});
