<?php
/**
 * Title: Blogg-rutenett
 * Slug: smartvarme/blog-card-grid
 * Categories: smartvarme, posts
 * Keywords: blogg, artikler, innlegg
 * Description: Et rutenett av blogginnlegg med bilde og utdrag
 */
?>
<!-- wp:query {"queryId":1,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"flex","columns":3}} -->
<div class="wp-block-query">
	<!-- wp:post-template -->
		<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->

		<!-- wp:post-title {"isLink":true,"fontSize":"large"} /-->

		<!-- wp:post-excerpt {"moreText":"Les mer"} /-->

		<!-- wp:post-date {"format":"j. F Y","fontSize":"small"} /-->
	<!-- /wp:post-template -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->

	<!-- wp:query-no-results -->
		<!-- wp:paragraph {"placeholder":"Legg til tekst eller blokker som vil vises når spørringen ikke returnerer noen resultater."} -->
		<p>Ingen innlegg funnet.</p>
		<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
