document.querySelectorAll("img[srcset]").forEach(function(img) {
	img.addEventListener("click", openPopup);
});

let currentPopup = null;

function openPopup() {
	let popup = document.createElement("div");
	popup.classList.add("popup");
	let img = this.cloneNode();
	img.removeAttribute("srcset");
	location.hash = img.src;

	popup.addEventListener("click", closePopup);
	window.addEventListener("keydown", closePopup);
	window.addEventListener("hashchange", checkHash);

	currentPopup = popup;
	popup.appendChild(img);
	document.body.appendChild(popup);
}

function checkHash(e) {
	if(location.hash.length <= 2) {
		closePopup();
	}
}

function closePopup(e) {
	if(e
	&& e.key
	&& e.key !== "Escape") {
		return;
	}

	window.removeEventListener("keypress", closePopup);
	window.removeEventListener("hashchange", closePopup)
	currentPopup && currentPopup.remove();
	currentPopup = null;
	location.hash = "_";
}
