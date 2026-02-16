import './style.scss';

// Make entire product cards clickable
document.addEventListener('DOMContentLoaded', function() {
	// Function to make product cards clickable
	function makeProductCardsClickable() {
		// Target WooCommerce product collection items
		const productCards = document.querySelectorAll('.wc-block-grid__product, .woocommerce ul.products li.product, .product-card');

		productCards.forEach(card => {
			// Don't add listener if already added
			if (card.dataset.clickable) return;
			card.dataset.clickable = 'true';

			// Find the first product link (image or title)
			const productLink = card.querySelector('.wp-block-post-featured-image a, .wp-block-post-title a, .woocommerce-loop-product__link, .wc-block-components-product-image a, a[href*="/produkt/"], a[href*="/peis/"]');

			if (productLink) {
				// Make card clickable
				card.style.cursor = 'pointer';

				// Add click handler to card
				card.addEventListener('click', function(e) {
					// Don't trigger if clicking on a link or button directly
					if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
						return;
					}

					// Navigate to product page
					window.location.href = productLink.href;
				});
			}
		});
	}

	// Run on page load
	makeProductCardsClickable();

	// Re-run when new content is loaded (for AJAX/infinite scroll)
	const observer = new MutationObserver(makeProductCardsClickable);
	observer.observe(document.body, { childList: true, subtree: true });
});
