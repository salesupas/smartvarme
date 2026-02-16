/* globals translation */

/**
 * Closing popup
 */
function popupToggle() {
	Array.from(document.querySelectorAll('.popup')).forEach(
		(popup) => {
			const insertedCloseIcon = popup.querySelector('.close-popup');
			insertedCloseIcon.addEventListener('click', (event) => {
					event.preventDefault();
					popup.classList.remove('open');
				}
			);
			popup.querySelector('.bg').addEventListener('click', () => {
					popup.classList.remove('open');
				}
			);
		}
	);

	Array.from(document.querySelectorAll('.toggle-popup-window')).forEach((link) => {
			link.addEventListener('click', (event) => {
					event.preventDefault();
					// Get the popup target
					let popupID = link.getAttribute('href');
					// Remove the # from the ID
					popupID = popupID.replace('#', '');
					// Fetch the popup
					const popup = document.getElementById(popupID);
					// Toggle open class on the popup
					popup.classList.remove('open');
				}
			);
		}
	);
}

popupToggle();

/**
 * Getting product id and quantity ater ajax add to cart ( used in popup )
 */
function getProductInfoAfterAjaxAddToCart() {
	jQuery('.ajax_add_to_cart, .single_add_to_cart_button').on(
		'click',
		function () {
			let productID = jQuery(this).data('product_id'),
				product_value = jQuery(this).val(),
				variationID = jQuery('.variation_id').val(),
				productId;

			if (variationID) {
				productId = variationID;
			} else {
				productId = productID ? productID : (product_value ? product_value : '');
			}

			jQuery.ajax({
					type: 'POST',
					url: translation.ajaxurl,
					dataType: 'html',
					data: {
						action: 'maksimer_loop_added_to_cart_popup',
						productID: productId,
					},
					success(res) {
						jQuery('body').append(res);
					},
				}
			);
		}
	);
}

getProductInfoAfterAjaxAddToCart();

jQuery(document).ajaxComplete(
	function () {
		popupToggle();
	}
);
