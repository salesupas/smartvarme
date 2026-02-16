/**
 * Toggles height of an element
 * @param button to click on
 * @param element whose height will be toggled
 * @param is_hidden on page load
 */
export function toggleHeight(button, element, is_hidden = true) {
	//get height of an element
	const getHeight = function (elem) {
		elem.style.display = 'block'; // Make it visible
		const height = elem.scrollHeight + 'px'; // Get it's height
		elem.style.display = ''; //  Hide it again
		return height;
	};

	// Show an element
	const show = function (elem) {
		// Get the natural height of the element
		const height = getHeight(elem); // Get the natural height
		console.log(height);
		elem.classList.add('--active'); // Make the element visible
		elem.style.height = height; // Update the max-height

		// Once the transition is complete, remove the inline max-height so the content can scale responsively
		window.setTimeout(function () {
			elem.style.height = '';
		}, 350);
	};

	// Hide an element
	const hide = function (elem) {
		// Give the element a height to change from
		elem.style.height = elem.scrollHeight + 'px';

		// Set the height back to 0
		window.setTimeout(function () {
			elem.style.height = '0';
		}, 5);

		// When the transition is complete, hide it
		window.setTimeout(function () {
			elem.classList.remove('--active');
		}, 350);
	};

	// Toggle element visibility
	const toggle = function (elem, timing) {
		if( is_hidden ) {
			// If the element is hidden, show it
			if (!elem.classList.contains('--active')) {
				show(elem);
				return;
			}
			// Otherwise, hide it
			hide(elem);


		} else {
			if (elem.lassList.contains('--active')) {
				hide(elem);
				return;
			}

			show(elem);

		}

	};

	button.addEventListener('click', (e) => {
		if (e.target.matches('a')) {
			e.preventDefault();
		}

		button.classList.toggle('--active');
		toggle(element);
	});
}

export async function sendDataByXMLReq2(data, url) {
	return new Promise((resolve, reject) => {
		const xmlHttpRequest = new XMLHttpRequest();
		xmlHttpRequest.open('POST', url, true);
		xmlHttpRequest.onload = (res) => {
			resolve(res.target.response);
		};
		xmlHttpRequest.onerror = (res) => {
			reject(res.target.response);
		};
		xmlHttpRequest.send(data);
	});
}
