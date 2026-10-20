<?php
/**
 * Основные функции темы CSS Cable B2B.
 *
 * @package CSSCable
 */

defined( 'ABSPATH' ) || exit;

define( 'CSSCABLE_VERSION', '0.3.0' );
define( 'CSSCABLE_DIR', get_template_directory() );
define( 'CSSCABLE_URI', get_template_directory_uri() );
require_once CSSCABLE_DIR . '/inc/commerce-pages.php';

/**
 * Настройка темы.
 */
function csscable_setup(): void {
	load_theme_textdomain(
		'csscable',
		CSSCABLE_DIR . '/languages'
	);

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 70,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 420,
		'single_image_width'    => 720,
		'product_grid'          => array(
			'default_rows'    => 12,
			'min_rows'        => 4,
			'max_rows'        => 30,
			'default_columns' => 1,
			'min_columns'     => 1,
			'max_columns'     => 1,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Основное меню', 'csscable' ),
		'footer'  => __( 'Меню в подвале', 'csscable' ),
	) );
}
add_action( 'after_setup_theme', 'csscable_setup' );

/**
 * Подключение стилей и скриптов.
 */
/**
 * Подключение стилей и скриптов.
 */
function csscable_enqueue_assets(): void {
	wp_enqueue_style(
		'csscable-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'csscable-style',
		get_stylesheet_uri(),
		array(),
		CSSCABLE_VERSION
	);

	wp_enqueue_style(
		'csscable-app',
		CSSCABLE_URI . '/assets/css/app.css',
		array( 'csscable-style' ),
		CSSCABLE_VERSION
	);

	wp_enqueue_script(
		'csscable-theme',
		CSSCABLE_URI . '/assets/js/theme.js',
		array(),
		CSSCABLE_VERSION,
		true
	);

	if (
		function_exists( 'is_account_page' )
		&& is_account_page()
	) {
		wp_enqueue_style(
			'csscable-my-account',
			CSSCABLE_URI . '/assets/css/my-account.css',
			array( 'csscable-app' ),
			CSSCABLE_VERSION
		);

		wp_add_inline_script(
			'csscable-theme',
			'
			document.addEventListener("click", function (event) {
				const button = event.target.closest("[data-password-toggle]");

				if (!button) {
					return;
				}

				const inputId = button.getAttribute("data-password-toggle");
				const input = document.getElementById(inputId);

				if (!input) {
					return;
				}

				const showPassword = input.type === "password";

				input.type = showPassword ? "text" : "password";

				button.setAttribute(
					"aria-label",
					showPassword ? "Скрыть пароль" : "Показать пароль"
				);
			});
			'
		);
	}
} 

add_action( 'wp_enqueue_scripts', 'csscable_enqueue_assets' );

/**
 * Убираем стандартный сайдбар WooCommerce.
 */
function csscable_remove_woocommerce_sidebar(): void {
	remove_action(
		'woocommerce_sidebar',
		'woocommerce_get_sidebar',
		10
	);
}
add_action( 'wp', 'csscable_remove_woocommerce_sidebar' );

/**
 * Собственная обёртка WooCommerce.
 */
function csscable_woocommerce_wrappers(): void {
	remove_action(
		'woocommerce_before_main_content',
		'woocommerce_output_content_wrapper',
		10
	);

	remove_action(
		'woocommerce_after_main_content',
		'woocommerce_output_content_wrapper_end',
		10
	);

	add_action(
		'woocommerce_before_main_content',
		'csscable_wrapper_start',
		10
	);

	add_action(
		'woocommerce_after_main_content',
		'csscable_wrapper_end',
		10
	);
}
add_action( 'wp', 'csscable_woocommerce_wrappers' );

function csscable_wrapper_start(): void {
	echo '<div class="cc-content">';
}

function csscable_wrapper_end(): void {
	echo '</div>';
}

/**
 * Количество товаров в корзине.
 */
function csscable_cart_count(): int {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * AJAX-обновление счётчика корзины.
 *
 * @param array $fragments WooCommerce fragments.
 * @return array
 */
function csscable_cart_fragments( array $fragments ): array {
	ob_start();
	?>
	<span class="cc-cart-count">
		<?php echo esc_html( csscable_cart_count() ); ?>
	</span>
	<?php

	$fragments['.cc-cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter(
	'woocommerce_add_to_cart_fragments',
	'csscable_cart_fragments'
);

/**
 * URL страницы магазина.
 */
function csscable_shop_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

/**
 * URL корзины.
 */
function csscable_cart_url(): string {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

/**
 * URL личного кабинета.
 */
function csscable_account_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return wp_login_url();
}

/**
 * Активный пункт меню.
 *
 * @param string $section Раздел.
 */
function csscable_is_active( string $section ): bool {
	switch ( $section ) {
		case 'catalog':
			return function_exists( 'is_shop' )
				&& (
					is_shop()
					|| is_product()
					|| is_product_category()
					|| is_product_tag()
				);

		case 'cart':
			return function_exists( 'is_cart' ) && is_cart();

		case 'checkout':
			return function_exists( 'is_checkout' ) && is_checkout();

		case 'account':
			return function_exists( 'is_account_page' ) && is_account_page();

		default:
			return is_front_page();
	}
}

/**
 * Простая SVG-иконка.
 *
 * @param string $name Название иконки.
 */
function csscable_icon( string $name ): void {
	$icons = array(
		'home' => '<path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z"/>',
		'catalog' => '<path d="M4 5h16M4 12h16M4 19h16"/><path d="M7 3v4M7 10v4M7 17v4"/>',
		'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.4 10.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/>',
		'orders' => '<path d="M6 3h12v18H6z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
		'documents' => '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/>',
		'company' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M8 10h2M14 10h2"/>',
		'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'logout' => '<path d="M10 17l5-5-5-5M15 12H3M14 3h7v18h-7"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return;
	}

	echo '<svg class="cc-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">';
	echo wp_kses(
		$icons[ $name ],
		array(
			'path'   => array( 'd' => true ),
			'circle' => array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			),
		)
	);
	echo '</svg>';
}

/**
 * Текст внизу каталога.
 */
function csscable_product_count_text(): string {
	if ( ! function_exists( 'wc_get_loop_prop' ) ) {
		return '';
	}

	$total = (int) wc_get_loop_prop( 'total' );

	return sprintf(
		_n(
			'%s товар',
			'%s товаров',
			$total,
			'csscable'
		),
		number_format_i18n( $total )
	);
}

/**
 * Дополнительные классы body.
 */
function csscable_body_classes( array $classes ): array {
	$classes[] = 'cc-theme';
	$classes[] = 'pro';

	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		$classes[] = 'cc-woocommerce-page';
	}

	return $classes;
}
add_filter( 'body_class', 'csscable_body_classes' );

/**
 * Не показываем стандартный заголовок WooCommerce.
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );

/**
 * Один товар в строке.
 */
add_filter(
	'loop_shop_columns',
	static function (): int {
		return 1;
	}
);
/**
 * Ресурсы новой карточки товара.
 */
function csscable_enqueue_single_product_assets(): void {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	wp_enqueue_style(
		'csscable-single-product',
		CSSCABLE_URI . '/assets/css/single-product.css',
		array( 'csscable-app' ),
		CSSCABLE_VERSION
	);

	wp_enqueue_script(
		'csscable-single-product',
		CSSCABLE_URI . '/assets/js/single-product.js',
		array(),
		CSSCABLE_VERSION,
		true
	);
}
add_action(
	'wp_enqueue_scripts',
	'csscable_enqueue_single_product_assets',
	20
);

/**
 * После кнопки «Заказ в один клик» отправляем пользователя
 * непосредственно на оформление заказа.
 *
 * @param string $url Стандартный URL перенаправления.
 */
function csscable_buy_now_redirect( string $url ): string {
	if (
		isset( $_REQUEST['cc_buy_now'] ) &&
		'1' === sanitize_text_field(
			wp_unslash( $_REQUEST['cc_buy_now'] )
		)
	) {
		return wc_get_checkout_url();
	}

	return $url;
}
add_filter(
	'woocommerce_add_to_cart_redirect',
	'csscable_buy_now_redirect'
);
/**
 * Убираем стандартные хлебные крошки WooCommerce на странице товара.
 * Собственные хлебные крошки выводятся внутри content-single-product.php.
 */
function csscable_remove_default_product_breadcrumbs(): void {
	if ( function_exists( 'is_product' ) && is_product() ) {
		remove_action(
			'woocommerce_before_main_content',
			'woocommerce_breadcrumb',
			20
		);
	}
}
add_action(
	'wp',
	'csscable_remove_default_product_breadcrumbs',
	20
);
/**
 * Собственный текст уведомления после добавления товара в корзину.
 *
 * @param string $message  Стандартное сообщение WooCommerce.
 * @param array  $products Добавленные товары: product_id => quantity.
 * @param bool   $show_qty Показывать количество.
 */
function csscable_add_to_cart_message(
	string $message,
	array $products,
	bool $show_qty
): string {
	if ( empty( $products ) ) {
		return $message;
	}

	$items = array();

	foreach ( $products as $product_id => $quantity ) {
		$product = wc_get_product( (int) $product_id );

		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$unit = function_exists( 'csscable_b2b_product_unit' )
			? csscable_b2b_product_unit( $product )
			: 'шт.';

		$items[] = sprintf(
			'<strong>«%1$s»</strong> — <strong>%2$s %3$s</strong>',
			esc_html( $product->get_name() ),
			esc_html( wc_format_localized_decimal( (float) $quantity ) ),
			esc_html( $unit )
		);
	}

	if ( empty( $items ) ) {
		return $message;
	}

	return sprintf(
		'<span class="cc-cart-notice-text">В корзину добавлено: %1$s.</span>
		<a href="%2$s" class="button wc-forward">Перейти в корзину</a>',
		implode( ', ', $items ),
		esc_url( wc_get_cart_url() )
	);
}
add_filter(
	'wc_add_to_cart_message_html',
	'csscable_add_to_cart_message',
	10,
	3
);
/**
 * =========================================================
 * Страница авторизации
 * =========================================================
 */

/**
 * Подключаем фирменные стили на wp-login.php.
 */
function csscable_login_assets(): void {
	wp_enqueue_style(
		'csscable-login-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'csscable-login',
		CSSCABLE_URI . '/assets/css/login.css',
		array( 'csscable-login-font' ),
		CSSCABLE_VERSION
	);
}
add_action( 'login_enqueue_scripts', 'csscable_login_assets' );

/**
 * Содержимое фирменного блока и заголовок формы.
 *
 * @param string $message Стандартное сообщение WordPress.
 * @return string
 */
function csscable_login_message( string $message ): string {
	static $rendered = false;

	if ( $rendered ) {
		return $message;
	}

	$rendered = true;

	ob_start();
	?>
	<section class="cc-login-hero" aria-label="<?php esc_attr_e( 'ЦСС Кабель', 'csscable' ); ?>">
		<div class="cc-login-hero__content">
<a
	class="cc-login-brand"
	href="<?php echo esc_url( home_url( '/' ) ); ?>"
	aria-label="ЦСС Кабель"
>
	<img
		class="cc-login-brand__logo"
		src="logow.png"
		alt="ЦСС Кабель"
		width="347"
		height="215"
	>
</a>


			<h2>
				Всё для работы<br>
				в одном кабинете
			</h2>

			<p class="cc-login-hero__lead">
				Заказывайте кабельную продукцию, отслеживайте заказы
				и работайте с документами в едином пространстве.
			</p>

			<ul class="cc-login-benefits">
				<li>
					<span class="cc-login-benefits__icon" aria-hidden="true">✓</span>
					<span>Актуальные цены и наличие продукции</span>
				</li>

				<li>
					<span class="cc-login-benefits__icon" aria-hidden="true">✓</span>
					<span>История заказов и статусы поставок</span>
				</li>

				<li>
					<span class="cc-login-benefits__icon" aria-hidden="true">✓</span>
					<span>Документы и информация по договору</span>
				</li>
			</ul>
		</div>
	</section>

	<div class="cc-login-heading">
		<span class="cc-login-heading__eyebrow">
			Личный кабинет
		</span>

		<h1>Добро пожаловать</h1>

		<p>
			Введите данные вашей учётной записи,
			чтобы продолжить работу.
		</p>
	</div>
	<?php

	return ob_get_clean() . $message;
}
add_filter( 'login_message', 'csscable_login_message' );

/**
 * Ссылка логотипа WordPress.
 *
 * @return string
 */
function csscable_login_logo_url(): string {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'csscable_login_logo_url' );

/**
 * Подпись логотипа.
 *
 * @return string
 */
function csscable_login_logo_text(): string {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'csscable_login_logo_text' );

/**
 * После успешного входа отправляем пользователя в раздел «Обзор».
 *
 * Приоритет 999 нужен, потому что плагин cc-b2b-cabinet
 * уже использует фильтр login_redirect.
 *
 * @param string           $redirect_to           Рассчитанный адрес.
 * @param string           $requested_redirect_to Запрошенный адрес.
 * @param WP_User|WP_Error $user                  Пользователь.
 * @return string
 */
function csscable_login_redirect_to_overview(
	string $redirect_to,
	string $requested_redirect_to,
	$user
): string {
	if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
		return $redirect_to;
	}

	return home_url( '/' );
}
add_filter(
	'login_redirect',
	'csscable_login_redirect_to_overview',
	999,
	3
);
/**
 * Пункты меню личного кабинета.
 *
 * Убираем ненужные загрузки и задаём понятные названия.
 *
 * @param array<string,string> $items Пункты WooCommerce.
 * @return array<string,string>
 */
function csscable_account_menu_items( array $items ): array {
	unset( $items['downloads'] );

	$labels = array(
		'dashboard'       => 'Обзор',
		'orders'          => 'Мои заказы',
		'edit-address'    => 'Адреса',
		'payment-methods' => 'Способы оплаты',
		'edit-account'    => 'Настройки профиля',
		'customer-logout' => 'Выйти',
	);

	$ordered = array();

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$ordered[ $endpoint ] = $label;
		}
	}

	return $ordered;
}
add_filter(
	'woocommerce_account_menu_items',
	'csscable_account_menu_items',
	100
);
