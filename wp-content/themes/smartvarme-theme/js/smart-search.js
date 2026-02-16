/**
 * Smart Search - Live search with categories and products
 */
(function() {
  const searchInput = document.querySelector('.smart-search-input');
  const searchResults = document.querySelector('.smart-search-results');
  const categoryResults = document.querySelector('.category-results');
  const brandResults = document.querySelector('.brand-results');
  const productResults = document.querySelector('.product-results');

  if (!searchInput) return;

  let searchTimeout;
  let currentRequest;

  // Show/hide results
  function showResults() {
    searchResults.style.display = 'block';
  }

  function hideResults() {
    searchResults.style.display = 'none';
  }

  // Perform search
  function performSearch(query) {
    if (query.length < 2) {
      hideResults();
      return;
    }

    // Cancel previous request
    if (currentRequest) {
      currentRequest.abort();
    }

    // Show loading state
    categoryResults.innerHTML = '<div class="search-loading">Søker...</div>';
    if (brandResults) brandResults.innerHTML = '<div class="search-loading">Søker...</div>';
    productResults.innerHTML = '<div class="search-loading">Søker...</div>';
    showResults();

    // Create AJAX request
    currentRequest = new XMLHttpRequest();
    currentRequest.open('POST', smartvarme_search.ajax_url, true);
    currentRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    currentRequest.onload = function() {
      if (currentRequest.status >= 200 && currentRequest.status < 400) {
        const data = JSON.parse(currentRequest.responseText);

        if (data.success) {
          displayResults(data.data);
        } else {
          displayError();
        }
      } else {
        displayError();
      }
    };

    currentRequest.onerror = function() {
      displayError();
    };

    const params = 'action=smartvarme_live_search&nonce=' + smartvarme_search.nonce + '&query=' + encodeURIComponent(query);
    currentRequest.send(params);
  }

  // Display results
  function displayResults(data) {
    // Display categories
    if (data.categories && data.categories.length > 0) {
      categoryResults.innerHTML = data.categories.map(cat => `
        <a href="${cat.url}" class="search-result-item category-item">
          <span class="result-icon">📁</span>
          <div class="result-info">
            <span class="result-name">${cat.name}</span>
            <span class="result-count">${cat.count} produkter</span>
          </div>
        </a>
      `).join('');
    } else {
      categoryResults.innerHTML = '<div class="no-results">Ingen kategorier funnet</div>';
    }

    // Display brands
    if (brandResults && data.brands && data.brands.length > 0) {
      brandResults.innerHTML = data.brands.map(brand => `
        <a href="${brand.url}" class="search-result-item brand-item">
          <span class="result-icon">🏷️</span>
          <div class="result-info">
            <span class="result-name">${brand.name}</span>
            <span class="result-count">${brand.count} produkter</span>
          </div>
        </a>
      `).join('');
    } else if (brandResults) {
      brandResults.innerHTML = '<div class="no-results">Ingen merker funnet</div>';
    }

    // Display products
    if (data.products && data.products.length > 0) {
      productResults.innerHTML = data.products.map(product => `
        <a href="${product.url}" class="search-result-item product-item">
          ${product.image ? `<img src="${product.image}" alt="${product.name}" class="result-image">` : '<div class="result-image-placeholder"></div>'}
          <div class="result-details">
            <span class="result-name">${product.name}</span>
            ${product.description ? `<span class="result-description">${product.description}</span>` : ''}
            <div class="result-meta">
              ${product.price ? `<span class="result-price">${product.price}</span>` : ''}
              ${product.in_stock ? '<span class="result-stock in-stock">På lager</span>' : '<span class="result-stock out-of-stock">Ikke på lager</span>'}
            </div>
          </div>
        </a>
      `).join('');
    } else {
      productResults.innerHTML = '<div class="no-results">Ingen produkter funnet</div>';
    }
  }

  // Display error
  function displayError() {
    categoryResults.innerHTML = '<div class="search-error">Noe gikk galt</div>';
    if (brandResults) brandResults.innerHTML = '<div class="search-error">Noe gikk galt</div>';
    productResults.innerHTML = '<div class="search-error">Noe gikk galt</div>';
  }

  // Event listeners
  searchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    searchTimeout = setTimeout(function() {
      performSearch(query);
    }, 300); // Wait 300ms after user stops typing
  });

  searchInput.addEventListener('focus', function() {
    if (searchInput.value.trim().length >= 2) {
      showResults();
    }
  });

  // Close results when clicking outside
  document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
      hideResults();
    }
  });

  // Prevent form submission if results are showing
  document.querySelector('.smart-search-form').addEventListener('submit', function(e) {
    if (searchResults.style.display === 'block') {
      e.preventDefault();
    }
  });
})();
