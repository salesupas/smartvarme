import {toggleHeight, sendDataByXMLReq2} from "./helpers/functions";
import './parts/popup';


const maksimerGravifyForms = () => {
	const btn = document.getElementById('maksimer_gf_button');
	const form = document.querySelector('.gform_wrapper');

	if (!btn || !form) return;

	form.style.height = '0px';
	form.style.visibility = 'visible';
	toggleHeight(btn, form);
}

maksimerGravifyForms();

const maksimer_filter_products = () => {
	const button = document.querySelector('.maksimer-filter-products-by-stock');
	const productsContainer = document.querySelector('body.ast-woo-shop-archive ul.products.columns-4');
	const pagination = document.querySelector('.woocommerce-pagination');
	const numberOfProducts = document.querySelector('.woocommerce-result-count');

	if (!button) return;
	let isActive = false;
	button.addEventListener('click', (e) => {
		e.preventDefault();

		if (isActive && localStorage.getItem('mks_previous_html')) {
			productsContainer.innerHTML = localStorage.getItem('mks_previous_html');
			button.classList.remove('active');
			pagination.classList.remove('hidden');
			numberOfProducts.classList.remove('hidden');
			isActive = !isActive;
			return;
		}
		const formData = new FormData();

		formData.append('wp_nonce', translation.wp_nonce);
		formData.append('page_id', translation.page_id);
		formData.append('action', 'filter_products_by_stock');
		formData.append('query_arg', translation.query_arg);


		sendDataByXMLReq2(formData, translation.ajaxurl).then((res) => {

			const obj = JSON.parse(res);

			if (productsContainer) {
				localStorage.setItem('mks_previous_html', productsContainer.innerHTML);
				productsContainer.innerHTML = obj.data;
			}

			isActive = !isActive;
			button.classList.add('active');
			// pagination.classList.add('hidden');
			numberOfProducts.classList.add('hidden');
		}).catch((error) => {
			console.log('error', error);
		});
	});
}

maksimer_filter_products();
