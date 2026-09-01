<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Allow font file uploads.
 */
function astra_child_allow_font_uploads( $mimes ) {

	$mimes['woff']  = 'font/woff';
	$mimes['woff2'] = 'font/woff2';
	$mimes['ttf']   = 'font/ttf';
	$mimes['otf']   = 'font/otf';

	return $mimes;
}
add_filter( 'upload_mimes', 'astra_child_allow_font_uploads' );


/**
 * SHOP.CO Header Actions Shortcode
 *
 * Usage: [shopco_header_actions]
 */
function shopco_header_actions_shortcode() {

	ob_start();
	?>

	<div class="shopco-header-actions">

		<!-- Search -->
		<form
			class="shopco-header-search"
			role="search"
			method="get"
			action="<?php echo esc_url( home_url( '/' ) ); ?>"
		>

			<input
				type="search"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Search...', 'astra-child' ); ?>"
				aria-label="<?php esc_attr_e( 'Search products', 'astra-child' ); ?>"
			>

			<input type="hidden" name="post_type" value="product">

			<button
				type="submit"
				aria-label="<?php esc_attr_e( 'Search', 'astra-child' ); ?>"
			>
				<svg
					width="20"
					height="20"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>
					<circle
						cx="11"
						cy="11"
						r="7"
						stroke="currentColor"
						stroke-width="1.8"
					/>
					<path
						d="M16.5 16.5L21 21"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
					/>
				</svg>
			</button>

		</form>


		<?php if ( class_exists( 'WooCommerce' ) ) : ?>

			<!-- Cart -->
			<a
				class="shopco-header-cart"
				href="<?php echo esc_url( wc_get_cart_url() ); ?>"
				aria-label="<?php esc_attr_e( 'Shopping Cart', 'astra-child' ); ?>"
			>

				<svg
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>
					<path
						d="M3 4H5L7.4 15.2C7.6 16.2 8.5 17 9.5 17H18.5C19.5 17 20.4 16.3 20.7 15.3L22 9H6"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>

					<circle
						cx="9"
						cy="20"
						r="1.3"
						fill="currentColor"
					/>

					<circle
						cx="18"
						cy="20"
						r="1.3"
						fill="currentColor"
					/>
				</svg>

				<span class="shopco-cart-count">
					<?php echo WC()->cart ? esc_html( WC()->cart->get_cart_contents_count() ) : '0'; ?>
				</span>

			</a>


			<!-- My Account -->
			<a
				class="shopco-header-account"
				href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
				aria-label="<?php esc_attr_e( 'My Account', 'astra-child' ); ?>"
			>

				<svg
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>

					<circle
						cx="12"
						cy="8"
						r="3.5"
						stroke="currentColor"
						stroke-width="1.8"
					/>

					<path
						d="M5 20C5.7 16.8 8.3 15 12 15C15.7 15 18.3 16.8 19 20"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
					/>

				</svg>

			</a>

		<?php endif; ?>

	</div>

	<?php
	return ob_get_clean();
}

add_shortcode( 'shopco_header_actions', 'shopco_header_actions_shortcode' );


/**
 * SHOP.CO - Product Rating After Title
 *
 * Astra WooCommerce product loop.
 * Displays:
 * ★★★★★ 5.0/5
 */

function shopco_product_rating_after_title() {

	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_rating_count();

	// Don't show a rating when there are no reviews.
	if ( $rating <= 0 || $count <= 0 ) {
		return;
	}
	?>

	<div class="shopco-product-rating">

		<div class="shopco-rating-stars">
			<?php echo wc_get_rating_html( $rating ); ?>
		</div>

		<span class="shopco-rating-number">
			<?php echo esc_html( number_format( $rating, 1 ) ); ?>/5
		</span>

	</div>

	<?php
}

/**
 * Astra hook:
 * Product title → our rating → remaining product information.
 */
add_action(
	'astra_woo_shop_title_after',
	'shopco_product_rating_after_title',
	10
);


/**
 * SHOP.CO - Discount Badge
 *
 * Adds the discount percentage directly after
 * the WooCommerce sale price.
 *
 * Example:
 * $240  $260  -8%
 */
function shopco_add_discount_badge_to_price( $price_html, $product ) {

	if ( ! $product instanceof WC_Product ) {
		return $price_html;
	}

	// Only display the badge for products on sale.
	if ( ! $product->is_on_sale() ) {
		return $price_html;
	}

	$regular_price = (float) $product->get_regular_price();
	$sale_price    = (float) $product->get_sale_price();

	// Prevent invalid calculations.
	if ( $regular_price <= 0 || $sale_price <= 0 ) {
		return $price_html;
	}

	// Calculate discount percentage.
	$discount = round(
		( ( $regular_price - $sale_price ) / $regular_price ) * 100
	);

	// Add badge directly after the price HTML.
	$price_html .= sprintf(
		'<span class="shopco-discount-badge">-%s%%</span>',
		esc_html( $discount )
	);

	return $price_html;
}

add_filter(
	'woocommerce_get_price_html',
	'shopco_add_discount_badge_to_price',
	20,
	2
);



/**
 * SHOP.CO - Create Demo Reviews & Ratings
 *
 * IMPORTANT:
 * This is a ONE-TIME setup script for the local assignment.
 * Remove this code after it has run once.
 */
function shopco_create_demo_reviews_and_ratings() {

	// Only administrators can run this.
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	// Prevent running more than once.
	if ( get_option( 'shopco_demo_reviews_v2_done' ) ) {
		return;
	}

	/*
	 * Different ratings for different products.
	 *
	 * Two reviews are created for each product:
	 *
	 * 5.0 = 5 + 5
	 * 4.5 = 5 + 4
	 * 4.0 = 4 + 4
	 * 3.5 = 4 + 3
	 */
	$rating_sets = array(
		array( 5, 5 ),
		array( 5, 4 ),
		array( 4, 4 ),
		array( 4, 3 ),
	);

	$review_texts = array(
		'Great quality and very comfortable. Really happy with the purchase.',
		'Nice design and good material. Looks exactly as expected.',
		'Good fit and excellent quality. Would definitely recommend it.',
		'Very nice product. The quality feels great and the design is clean.',
	);

	$products = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$product_index = 0;

	foreach ( $products as $product_id ) {

		/*
		 * Check whether this product already has
		 * an approved review with a rating.
		 */
		$reviews = get_comments(
			array(
				'post_id'    => $product_id,
				'status'     => 'approve',
				'meta_key'   => 'rating',
				'meta_value' => '',
				'compare'    => '!=',
				'number'     => 1,
			)
		);

		/*
		 * If the product already has a rated review,
		 * leave it alone.
		 */
		if ( ! empty( $reviews ) ) {
			$product_index++;
			continue;
		}

		$ratings = $rating_sets[ $product_index % count( $rating_sets ) ];

		foreach ( $ratings as $review_index => $rating ) {

			$authors = array(
				'Alex',
				'Sarah',
			);

			$author = $authors[ $review_index ];

			$review_id = wp_insert_comment(
				array(
					'comment_post_ID'      => $product_id,
					'comment_author'       => $author,
					'comment_author_email' => strtolower( $author ) . '@demo.local',
					'comment_content'      => $review_texts[ $review_index % count( $review_texts ) ],
					'comment_type'         => 'review',
					'comment_approved'     => 1,
					'comment_author_IP'    => '127.0.0.1',
				)
			);

			if ( $review_id ) {

				// Store the WooCommerce star rating.
				update_comment_meta(
					$review_id,
					'rating',
					$rating
				);

				// Mark as a verified/demo purchase review.
				update_comment_meta(
					$review_id,
					'verified',
					1
				);
			}
		}

		/*
		 * Recalculate WooCommerce rating data.
		 */
		if ( function_exists( 'wc_update_product_rating_counts' ) ) {
			wc_update_product_rating_counts( $product_id );
		}

		if ( function_exists( 'wc_update_product_average_rating' ) ) {
			wc_update_product_average_rating( $product_id );
		}

		if ( function_exists( 'wc_update_product_review_count' ) ) {
			wc_update_product_review_count( $product_id );
		}

		// Clear product cache/transients.
		wc_delete_product_transients( $product_id );

		$product_index++;
	}

	// Mark the setup as completed.
	update_option( 'shopco_demo_reviews_v2_done', 1 );
}

add_action( 'admin_init', 'shopco_create_demo_reviews_and_ratings' );



/**
 * ============================================================
 * SHOP.CO - MOBILE HEADER ACTIONS
 * ============================================================
 *
 * Shortcode:
 * [shopco_mobile_header_actions]
 *
 * Visible only at 981px and below.
 *
 * Includes:
 * - Search toggle
 * - Sliding search form
 * - Cart icon
 * - Cart count
 * - My Account icon
 * - CSS
 * - JavaScript
 */


/**
 * ============================================================
 * SHORTCODE
 * ============================================================
 */

function shopco_mobile_header_actions_shortcode() {

	$cart_count = 0;

	if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
		$cart_count = WC()->cart->get_cart_contents_count();
	}

	ob_start();
	?>

	<div
		class="shopco-mobile-header-actions"
		data-shopco-mobile-header
	>

		<!-- ==================================================
		     SEARCH TOGGLE BUTTON
		     ================================================== -->

		<button
			type="button"
			class="shopco-mobile-search-toggle"
			aria-label="Search"
			aria-expanded="false"
			aria-controls="shopco-mobile-search"
		>

			<svg
				width="22"
				height="22"
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				aria-hidden="true"
			>

				<circle
					cx="11"
					cy="11"
					r="7"
					stroke="currentColor"
					stroke-width="1.8"
				/>

				<path
					d="M16.5 16.5L21 21"
					stroke="currentColor"
					stroke-width="1.8"
					stroke-linecap="round"
				/>

			</svg>

		</button>


		<!-- ==================================================
		     SEARCH FORM
		     ================================================== -->

		<form
			id="shopco-mobile-search"
			class="shopco-mobile-search"
			role="search"
			method="get"
			action="<?php echo esc_url( home_url( '/' ) ); ?>"
		>

			<input
				type="search"
				name="s"
				placeholder="Search..."
				aria-label="Search products"
				autocomplete="off"
			>

			<input
				type="hidden"
				name="post_type"
				value="product"
			>


			<button
				type="submit"
				class="shopco-mobile-search-submit"
				aria-label="Submit Search"
			>

				<svg
					width="20"
					height="20"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>

					<circle
						cx="11"
						cy="11"
						r="7"
						stroke="currentColor"
						stroke-width="1.8"
					/>

					<path
						d="M16.5 16.5L21 21"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
					/>

				</svg>

			</button>

		</form>


		<?php if ( class_exists( 'WooCommerce' ) ) : ?>

			<!-- ==================================================
			     CART
			     ================================================== -->

			<a
				class="shopco-mobile-cart"
				href="<?php echo esc_url( wc_get_cart_url() ); ?>"
				aria-label="Shopping Cart"
			>

				<svg
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>

					<path
						d="M3 4H5L7.4 15.2C7.6 16.2 8.5 17 9.5 17H18.5C19.5 17 20.4 16.3 20.7 15.3L22 9H6"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>

					<circle
						cx="9"
						cy="20"
						r="1.3"
						fill="currentColor"
					/>

					<circle
						cx="18"
						cy="20"
						r="1.3"
						fill="currentColor"
					/>

				</svg>


				<span class="shopco-mobile-cart-count">
					<?php echo esc_html( $cart_count ); ?>
				</span>

			</a>


			<!-- ==================================================
			     MY ACCOUNT
			     ================================================== -->

			<a
				class="shopco-mobile-account"
				href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
				aria-label="My Account"
			>

				<svg
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
				>

					<circle
						cx="12"
						cy="8"
						r="3.5"
						stroke="currentColor"
						stroke-width="1.8"
					/>

					<path
						d="M5 20C5.7 16.8 8.3 15 12 15C15.7 15 18.3 16.8 19 20"
						stroke="currentColor"
						stroke-width="1.8"
						stroke-linecap="round"
					/>

				</svg>

			</a>

		<?php endif; ?>

	</div>


	<!-- ======================================================
	     CSS
	     ====================================================== -->

	<style>

		/* -----------------------------------------------
		   Hide on desktop
		   ----------------------------------------------- */

		.shopco-mobile-header-actions {
			display: none;
		}


		/* -----------------------------------------------
		   Mobile / Tablet
		   ----------------------------------------------- */

		@media (max-width: 981px) {

			.shopco-mobile-header-actions {

				position: relative;

				display: flex;
				align-items: center;
				justify-content: flex-end;

				gap: 8px;

				width: auto;
				height: 44px;

				margin: 0;
				padding: 0;

				box-sizing: border-box;
			}


			/* ==========================================
			   SEARCH TOGGLE
			   ========================================== */

			.shopco-mobile-search-toggle {

				display: flex;
				align-items: center;
				justify-content: center;

				width: 44px;
				height: 44px;

				flex: 0 0 44px;

				margin: 0;
				padding: 0;

				border: 0;
				border-radius: 4px;

				background: transparent;

				color: #000;

				cursor: pointer;

				outline: none;

				-webkit-appearance: none;
				appearance: none;

				position: relative;
				z-index: 100001;
			}


			.shopco-mobile-search-toggle:hover,
			.shopco-mobile-search-toggle:focus,
			.shopco-mobile-search-toggle:active {

				background: transparent;
				color: #000;

				outline: none;
				box-shadow: none;
			}


			.shopco-mobile-search-toggle svg {

				display: block;

				width: 22px;
				height: 22px;
			}


			/* ==========================================
			   SEARCH FORM
			   ========================================== */

			.shopco-mobile-search {

				position: absolute;
                display: none !important;
				top: 50%;
				right: 0;
                 width:94vw !important;
				z-index: 100000;

				display: flex;
				align-items: center;

				width: min(
					calc(100vw - 30px),
					360px
				);

				height: 44px;

				margin: 0;
				padding: 0;

				box-sizing: border-box;

				background: #fff;

				border-radius: 24px;

				transform:
					translateX(110%)
					translateY(-50%);

				opacity: 0;

				visibility: hidden;

				pointer-events: none;

				transition:
					transform 0.3s ease,
					opacity 0.25s ease,
					visibility 0.3s ease;

				box-shadow:
					0 4px 15px rgba(0, 0, 0, 0.10);
			}


			/* ==========================================
			   SEARCH OPEN
			   ========================================== */

			.shopco-mobile-header-actions.shopco-search-open
			.shopco-mobile-search {

				transform:
					translateX(0%)
					translateY(92%);
                   display: block !important;
				opacity: 1;

				visibility: visible;

				pointer-events: auto;
			}


			/* ==========================================
			   SEARCH INPUT
			   ========================================== */

			.shopco-mobile-search input[type="search"] {

				display: block;
               
				width: 100%;
				height: 44px;

				margin: 0;
				padding: 0 50px 0 17px;

				box-sizing: border-box;

				border: 1px solid #ddd;
				border-radius: 24px;

				background: #fff;

				color: #000;

				font-family: inherit;
				font-size: 15px;
				font-weight: 400;

				line-height: 44px;

				outline: none;

				box-shadow: none;

				-webkit-appearance: none;
				appearance: none;
			}


			.shopco-mobile-search input[type="search"]:focus {

				border-color: #000;

				outline: none;

				box-shadow: none;
			}


			/* Remove Safari search controls */

			.shopco-mobile-search
			input[type="search"]::-webkit-search-decoration,

			.shopco-mobile-search
			input[type="search"]::-webkit-search-cancel-button,

			.shopco-mobile-search
			input[type="search"]::-webkit-search-results-button,

			.shopco-mobile-search
			input[type="search"]::-webkit-search-results-decoration {

				-webkit-appearance: none;
			}


			/* ==========================================
			   SEARCH SUBMIT BUTTON
			   ========================================== */

			.shopco-mobile-search-submit {

				position: absolute;

				top: 50%;
				right: 9px;

				display: flex;
				align-items: center;
				justify-content: center;

				width: 34px;
				height: 34px;

				margin: 0;
				padding: 0;

				border: 0;

				background: transparent;

				color: #000;

				transform: translateY(-50%);

				cursor: pointer;

				outline: none;

				-webkit-appearance: none;
				appearance: none;
			}


			.shopco-mobile-search-submit:hover,
			.shopco-mobile-search-submit:focus {

				background: transparent;
				color: #000;

				outline: none;
				box-shadow: none;
			}


			.shopco-mobile-search-submit svg {

				display: block;

				width: 20px;
				height: 20px;
			}


			/* ==========================================
			   CART
			   ========================================== */

			.shopco-mobile-cart {

				position: relative;

				display: flex;
				align-items: center;
				justify-content: center;

				width: 44px;
				height: 44px;

				flex: 0 0 44px;

				margin: 0;
				padding: 0;

				color: #000;

				text-decoration: none;

				z-index: 100001;
			}


			.shopco-mobile-cart:hover,
			.shopco-mobile-cart:focus {

				color: #000;
				text-decoration: none;
			}


			.shopco-mobile-cart svg {

				display: block;

				width: 24px;
				height: 24px;
			}


			/* ==========================================
			   CART COUNT
			   ========================================== */

			.shopco-mobile-cart-count {

				position: absolute;

				top: 2px;
				right: 1px;

				display: flex;
				align-items: center;
				justify-content: center;

				min-width: 16px;
				height: 16px;

				padding: 0 4px;

				box-sizing: border-box;

				border-radius: 50%;

				background: #000;

				color: #fff;

				font-family: Arial, sans-serif;
				font-size: 9px;
				font-weight: 600;

				line-height: 1;
			}


			/* ==========================================
			   ACCOUNT
			   ========================================== */

			.shopco-mobile-account {

				display: flex;
				align-items: center;
				justify-content: center;

				width: 44px;
				height: 44px;

				flex: 0 0 44px;

				margin: 0;
				padding: 0;

				color: #000;

				text-decoration: none;

				z-index: 100001;
			}


			.shopco-mobile-account:hover,
			.shopco-mobile-account:focus {

				color: #000;
				text-decoration: none;
			}


			.shopco-mobile-account svg {

				display: block;

				width: 24px;
				height: 24px;
			}


			/* ==========================================
			   REDUCE MOTION
			   ========================================== */

			@media (prefers-reduced-motion: reduce) {

				.shopco-mobile-search {

					transition: none;
				}
			}

		}

	</style>


	<!-- ======================================================
	     JAVASCRIPT
	     ====================================================== -->

	<script>

	(function () {

		/*
		 * Initialize SHOP.CO mobile search.
		 */
		function shopcoInitMobileSearch() {

			const headers = document.querySelectorAll(
				'[data-shopco-mobile-header]'
			);

			if (!headers.length) {
				return;
			}


			headers.forEach(function (header) {

				/*
				 * Prevent duplicate initialization.
				 */
				if (
					header.dataset.searchInitialized === 'true'
				) {
					return;
				}


				const toggle =
					header.querySelector(
						'.shopco-mobile-search-toggle'
					);

				const search =
					header.querySelector(
						'.shopco-mobile-search'
					);

				const input =
					header.querySelector(
						'.shopco-mobile-search input[type="search"]'
					);


				/*
				 * Make sure all required elements exist.
				 */
				if (
					!toggle ||
					!search ||
					!input
				) {
					return;
				}


				header.dataset.searchInitialized = 'true';


				/*
				 * ==========================================
				 * OPEN SEARCH
				 * ==========================================
				 */

				function openSearch() {

					header.classList.add(
						'shopco-search-open'
					);

					toggle.setAttribute(
						'aria-expanded',
						'true'
					);


					/*
					 * Focus after animation starts.
					 */
					setTimeout(function () {

						input.focus();

					}, 300);

				}


				/*
				 * ==========================================
				 * CLOSE SEARCH
				 * ==========================================
				 */

				function closeSearch() {

					header.classList.remove(
						'shopco-search-open'
					);

					toggle.setAttribute(
						'aria-expanded',
						'false'
					);

					input.blur();

				}


				/*
				 * ==========================================
				 * SEARCH BUTTON
				 * ==========================================
				 */

				toggle.addEventListener(
					'click',
					function (event) {

						event.preventDefault();

						event.stopPropagation();


						const isOpen =
							header.classList.contains(
								'shopco-search-open'
							);


						if (isOpen) {

							closeSearch();

						} else {

							openSearch();

						}

					}
				);


				/*
				 * ==========================================
				 * CLICK OUTSIDE
				 * ==========================================
				 */

				document.addEventListener(
					'click',
					function (event) {

						if (
							header.classList.contains(
								'shopco-search-open'
							) &&
							!header.contains(
								event.target
							)
						) {

							closeSearch();

						}

					}
				);


				/*
				 * ==========================================
				 * ESC KEY
				 * ==========================================
				 */

				document.addEventListener(
					'keydown',
					function (event) {

						if (
							event.key === 'Escape' &&
							header.classList.contains(
								'shopco-search-open'
							)
						) {

							closeSearch();

							toggle.focus();

						}

					}
				);

			});

		}


		/*
		 * Run after DOM is ready.
		 */
		if (
			document.readyState === 'loading'
		) {

			document.addEventListener(
				'DOMContentLoaded',
				shopcoInitMobileSearch
			);

		} else {

			shopcoInitMobileSearch();

		}

	})();

	</script>

	<?php

	return ob_get_clean();
}


/**
 * ============================================================
 * REGISTER SHORTCODE
 * ============================================================
 */

add_shortcode(
	'shopco_mobile_header_actions',
	'shopco_mobile_header_actions_shortcode'
);