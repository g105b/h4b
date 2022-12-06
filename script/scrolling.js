(function() {
	let debounce = null;

	window.addEventListener("scroll", (e) => {
		if(debounce) {
			return;
		}

		debounce = setTimeout(() => onScroll(e), 250);
	});

	function onScroll(e) {
		debounce = null;
		console.log(e)
		if(window.scrollY > 250) {
			document.body.classList.add("scroll-fold");
		}
		else {
			document.body.classList.remove("scroll-fold");
		}
	}
})();
