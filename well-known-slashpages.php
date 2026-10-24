<?php
/**
 * Plugin Name: .well-known/slashpages
 * Plugin URI: https://github.com/pfefferle/wordpress-well-known-slashpages
 * Description: Lists the slash pages of the site (/about, /now, /uses, …) as OPML at /.well-known/slashpages.
 * Author: Matthias Pfefferle
 * Author URI: https://notiz.blog/
 * Version: 1.0.0
 * License: GPL-2.0-or-later
 * License URI: https://opensource.org/license/gpl-2-0/
 * Text Domain: wellknownslashpages
 * Update URI: https://github.com/pfefferle/wordpress-well-known-slashpages
 */

namespace Well_Known_Slashpages;

/**
 * The name of the option that stores the slash pages.
 *
 * @var string
 */
const OPTION = 'well_known_slashpages';

/**
 * The plugin version.
 *
 * @var string
 */
const VERSION = '1.0.0';

/**
 * The handle of the script that shows the slash pages in the browser.
 *
 * @var string
 */
const SCRIPT = 'well-known-slashpages';

/**
 * The URLs of the script that shows the slash pages in the browser and of its
 * dependencies, in load order.
 *
 * The script is registered like any other, with wp-i18n for the
 * translations. An OPML document has no wp_head(), so the URLs are built
 * from the registry like WP_Scripts does.
 *
 * @return string[] The script URLs.
 */
function get_script_urls() {
	$scripts = \wp_scripts();

	if ( ! $scripts->query( SCRIPT ) ) {
		\wp_register_script(
			SCRIPT,
			\plugins_url( 'assets/js/well-known-slashpages.js', __FILE__ ),
			array( 'wp-i18n' ),
			VERSION,
			array( 'strategy' => 'defer' )
		);
	}

	$scripts->all_deps( SCRIPT );
	$handles        = $scripts->to_do;
	$scripts->to_do = array();
	$urls           = array();

	foreach ( $handles as $handle ) {
		$script = $scripts->registered[ $handle ];

		// Aliases like "jquery" have no source of their own.
		if ( ! $script->src ) {
			continue;
		}

		$src = $script->src;

		if ( ! \preg_match( '|^(https?:)?//|', $src ) && ! ( $scripts->content_url && 0 === \strpos( $src, $scripts->content_url ) ) ) {
			$src = $scripts->base_url . $src;
		}

		if ( null !== $script->ver ) {
			$src = \add_query_arg( 'ver', $script->ver ? $script->ver : $scripts->default_version, $src );
		}

		/** This filter is documented in wp-includes/class-wp-scripts.php */
		$urls[] = (string) \apply_filters( 'script_loader_src', $src, $handle );
	}

	return $urls;
}

/**
 * The slash pages listed on https://slashpages.net/.
 *
 * The first name is the key, other names of the same page are `aliases`.
 *
 * @return array[] Map of slash page name => `description` and optional `aliases`.
 */
function get_slashpages() {
	$slashpages = array(
		'about'        => array( 'description' => 'A page all about you' ),
		'ai'           => array( 'description' => 'Describes how you use, or don\'t use, AI' ),
		'blank'        => array( 'description' => 'A page left intentionally blank' ),
		'blogroll'     => array( 'description' => 'Other sites you read, follow, or recommend' ),
		'calendar'     => array( 'description' => 'Dates from movies, TV, and culture that you observe' ),
		'canon'        => array( 'description' => 'An annotated list of the things that have most shaped you' ),
		'carry'        => array( 'description' => 'Your everyday carry items' ),
		'caw'          => array( 'description' => 'Crows' ),
		'celebrities'  => array( 'description' => 'Celebrities you\'ve spotted or met' ),
		'changelog'    => array(
			'description' => 'A summary of changes to your website',
			'aliases'     => array( 'log' ),
		),
		'chipotle'     => array( 'description' => 'Your Chipotle order' ),
		'colophon'     => array( 'description' => 'How the site is made, with which tools and technologies' ),
		'community'    => array( 'description' => 'What a community is about and its values' ),
		'concerts'     => array( 'description' => 'Concerts you\'ve attended' ),
		'contact'      => array( 'description' => 'Ways for people to contact you' ),
		'daylists'     => array( 'description' => 'Spotify Daylists created for you in the past' ),
		'death'        => array( 'description' => 'Instructions for family and friends in case of your death' ),
		'defaults'     => array( 'description' => 'The apps you use for various tasks' ),
		'disability'   => array( 'description' => 'Your disabilities and how they affect you' ),
		'elsewhere'    => array(
			'description' => 'Output or writing on sites other than your own',
			'aliases'     => array( 'appearances' ),
		),
		'follow'       => array(
			'description' => 'Your feeds and other ways to subscribe',
			'aliases'     => array( 'subscribe', 'feeds' ),
		),
		'for'          => array(
			'description' => 'Honoring loved ones or thanking those who supported the work',
			'aliases'     => array( 'dedication' ),
		),
		'glossary'     => array(
			'description' => 'Words you often use and what they mean',
			'aliases'     => array( 'lexicon', 'phrased' ),
		),
		'green'        => array( 'description' => 'How you contribute positively to the environment' ),
		'guy'          => array( 'description' => 'An "I\'m an insert interest here guy" style page' ),
		'hats'         => array( 'description' => 'Your hats' ),
		'hello'        => array( 'description' => 'The ways you prefer to keep in touch' ),
		'hills'        => array(
			'description' => 'Foolish hills you\'re willing to die on',
			'aliases'     => array( 'hill' ),
		),
		'ideas'        => array( 'description' => 'Things you want to make, or concepts you\'re considering' ),
		'interests'    => array( 'description' => 'Things you\'re passionate about' ),
		'junk'         => array(
			'description' => 'An indie-web yard sale',
			'aliases'     => array( 'sale', 'buy' ),
		),
		'languages'    => array( 'description' => 'Languages you speak, understand, write, or are learning' ),
		'links'        => array(
			'description' => 'Links to sites you want to share or save for later',
			'aliases'     => array( 'bookmarks' ),
		),
		'next'         => array( 'description' => 'What you\'re going to do next' ),
		'nope'         => array( 'description' => 'Things you don\'t like or don\'t want to do' ),
		'now'          => array( 'description' => 'What you\'re focused on at this point in your life' ),
		'pfp'          => array( 'description' => 'A timeline of your profile pictures' ),
		'photo'        => array(
			'description' => 'A single photo, swapped now and then',
			'aliases'     => array( 'pic' ),
		),
		'podroll'      => array( 'description' => 'Podcasts you recommend' ),
		'posse'        => array( 'description' => 'Your POSSE and cross-posting strategy' ),
		'postroll'     => array( 'description' => 'Interesting or impactful blog posts you\'ve read' ),
		'predictions'  => array( 'description' => 'Forecasts and long-term views on how the world may change' ),
		'pricing'      => array( 'description' => 'Product or service costs' ),
		'questions'    => array( 'description' => 'Questions you\'re asking or thinking about' ),
		'saudade'      => array( 'description' => 'Honoring what and whom you miss, with love' ),
		'save'         => array(
			'description' => 'Referral links and discount codes for things you recommend',
			'aliases'     => array( 'referrals' ),
		),
		'self-hosting' => array(
			'description' => 'The services you host or manage yourself',
			'aliases'     => array( 'self-hosted', 'hosted' ),
		),
		'shoebox'      => array( 'description' => 'A place for odds and ends' ),
		'since'        => array( 'description' => 'Time since you started or stopped something' ),
		'sitemap'      => array( 'description' => 'A list of pages on a website' ),
		'slashes'      => array( 'description' => 'A list of all your slash pages' ),
		'someday'      => array( 'description' => 'Things you\'d someday like to achieve, explore, or learn' ),
		'style'        => array( 'description' => 'A personal or design style guide' ),
		'til'          => array(
			'description' => 'Concise write-ups on small things you\'ve learned',
			'aliases'     => array( 'ways' ),
		),
		'tip'          => array(
			'description' => 'A way for people to tip you or buy you a coffee',
			'aliases'     => array( 'pay', 'coffee' ),
		),
		'top4'         => array(
			'description' => 'Your ranked top three favorites and an honorable mention',
			'aliases'     => array( 'topfour' ),
		),
		'uses'         => array( 'description' => 'The things you use on a daily basis' ),
		'verify'       => array( 'description' => 'Your online identities, listed and proven' ),
		'where'        => array( 'description' => 'Your current location' ),
		'why'          => array( 'description' => 'What you care about and where you\'re heading' ),
		'wish'         => array( 'description' => 'A wishlist that helps people connect with you' ),
		'yep'          => array( 'description' => 'Things you agree with or will do' ),
	);

	/**
	 * Filters the slash pages that can be set on the settings page.
	 *
	 * @param array[] $slashpages Map of slash page name => `description` and optional `aliases`.
	 */
	return \apply_filters( 'well_known_slashpages', $slashpages );
}

/**
 * The page ID or URL of every slash page.
 *
 * A slash page that was never saved defaults to the page with the same slug.
 *
 * @return array Map of slash page name => page ID (int) or URL (string). 0 means not set.
 */
function get_values() {
	$saved    = (array) \get_option( OPTION, array() );
	$settings = array();

	foreach ( get_slashpages() as $name => $description ) {
		if ( \array_key_exists( $name, $saved ) ) {
			$settings[ $name ] = $saved[ $name ];
			continue;
		}

		$page              = \get_page_by_path( $name );
		$settings[ $name ] = $page && 'publish' === $page->post_status ? $page->ID : 0;
	}

	return $settings;
}

/**
 * The slash pages that are set, with title and URL.
 *
 * @return array[] List of slash pages (`category`, `text`, `url`).
 */
function get_outlines() {
	$slashpages = get_slashpages();
	$links      = array();

	foreach ( get_values() as $name => $value ) {
		if ( \is_int( $value ) ) {
			if ( ! $value || 'publish' !== \get_post_status( $value ) ) {
				continue;
			}

			$text = \get_the_title( $value );
			$url  = \get_permalink( $value );
		} else {
			$text = \ucfirst( $name );
			$url  = $value;
		}

		// All names of the page, the first name first.
		$names = \array_merge( array( $name ), isset( $slashpages[ $name ]['aliases'] ) ? $slashpages[ $name ]['aliases'] : array() );

		$links[] = array(
			'category' => '/' . \implode( ',/', $names ),
			'text'     => $text,
			'url'      => $url,
		);
	}

	/**
	 * Filters the slash pages before they are served.
	 *
	 * @param array[] $links List of slash pages (`category`, `text`, `url`).
	 */
	return \apply_filters( 'well_known_slashpages_links', $links );
}

/**
 * Print the OPML document.
 */
function render_opml() {
	// The script shows the OPML as a page in the browser.
	$translations = \load_script_textdomain( SCRIPT, 'wellknownslashpages' );

	echo '<?xml version="1.0" encoding="' . \esc_attr( \get_option( 'blog_charset' ) ) . '"?>' . PHP_EOL;

	// phpcs:disable Generic.WhiteSpace.ScopeIndent -- Control tags at the start of the line keep the indentation of the XML.
	?>
<opml version="2.0">
<?php if ( $translations ) : ?>
	<script xmlns="http://www.w3.org/1999/xhtml" type="application/json" id="well-known-slashpages-translations"><?php echo \htmlspecialchars( $translations, ENT_XML1 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML. ?></script>
<?php endif; ?>
<?php foreach ( get_script_urls() as $src ) : ?>
	<script xmlns="http://www.w3.org/1999/xhtml" src="<?php echo \htmlspecialchars( $src, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Escaped for XML, an OPML document has no wp_head(). ?>" defer=""/>
<?php endforeach; ?>
	<head>
		<title><?php echo \esc_html( \sprintf( /* translators: %s: Site title. */ \__( 'Slash pages of %s', 'wellknownslashpages' ), \get_bloginfo( 'name', 'display' ) ) ); ?></title>
		<dateCreated><?php echo \esc_html( \gmdate( 'D, d M Y H:i:s' ) ); ?> GMT</dateCreated>
		<ownerId><?php echo \esc_html( \home_url( '/' ) ); ?></ownerId>
		<docs>http://opml.org/spec2.opml</docs>
	</head>
	<body>
<?php foreach ( get_outlines() as $link ) : ?>
		<outline type="link" text="<?php echo \esc_attr( $link['text'] ); ?>" category="<?php echo \esc_attr( $link['category'] ); ?>" url="<?php echo \esc_url( $link['url'] ); ?>"/>
<?php endforeach; ?>
	</body>
</opml>
<?php
	// phpcs:enable Generic.WhiteSpace.ScopeIndent
}

/**
 * Parse request for .well-known/slashpages.
 *
 * @param \WP $wp The WordPress environment for the request.
 */
function parse_request( $wp ) {
	if ( ! \array_key_exists( 'well-known', $wp->query_vars ) || 'slashpages' !== $wp->query_vars['well-known'] ) {
		return;
	}

	\header( 'Content-Type: text/xml; charset=' . \get_option( 'blog_charset' ), true );
	\header( 'Access-Control-Allow-Origin: *', true );

	render_opml();
	exit;
}
\add_action( 'parse_request', __NAMESPACE__ . '\parse_request' );

/**
 * Accept .well-known query variables.
 *
 * @param string[] $vars The query variables.
 *
 * @return string[] The query variables.
 */
function query_vars( $vars ) {
	$vars[] = 'well-known';
	return $vars;
}
\add_filter( 'query_vars', __NAMESPACE__ . '\query_vars' );

/**
 * Add rewrite rules for .well-known/slashpages and .well-known/slashpages.opml.
 */
function rewrite_rules() {
	\add_rewrite_rule( '^.well-known/slashpages(\.opml)?$', 'index.php?well-known=slashpages', 'top' );
}
\add_action( 'init', __NAMESPACE__ . '\rewrite_rules', 15 );

/**
 * Flush the rewrite rules on activation and deactivation.
 */
function flush_rewrite_rules() {
	namespace\rewrite_rules();
	\flush_rewrite_rules();
}
\register_activation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );
\register_deactivation_hook( __FILE__, __NAMESPACE__ . '\flush_rewrite_rules' );

/**
 * Register the setting.
 */
function register_setting() {
	\register_setting(
		'well-known-slashpages',
		OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => __NAMESPACE__ . '\sanitize',
		)
	);
}
\add_action( 'admin_init', __NAMESPACE__ . '\register_setting' );

/**
 * Sanitize the submitted slash pages.
 *
 * Every slash page is stored, also the empty ones, so a page that was set to
 * "None" does not fall back to the page with the same slug.
 *
 * @param array $input The submitted form values.
 *
 * @return array Map of slash page name => page ID (int) or URL (string).
 */
function sanitize( $input ) {
	$settings = array();

	foreach ( get_slashpages() as $name => $description ) {
		$page = isset( $input[ $name ]['page'] ) ? $input[ $name ]['page'] : '';
		$url  = isset( $input[ $name ]['url'] ) ? \esc_url_raw( \trim( $input[ $name ]['url'] ) ) : '';

		// The spec asks for absolute URLs.
		if ( 'url' === $page && \in_array( \wp_parse_url( $url, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) {
			$settings[ $name ] = $url;
		} else {
			$settings[ $name ] = \absint( $page );
		}
	}

	return $settings;
}

/**
 * Add the settings page.
 */
function admin_menu() {
	\add_options_page(
		\__( 'Slash Pages', 'wellknownslashpages' ),
		\__( 'Slash Pages', 'wellknownslashpages' ),
		'manage_options',
		'well-known-slashpages',
		__NAMESPACE__ . '\render_settings_page'
	);
}
\add_action( 'admin_menu', __NAMESPACE__ . '\admin_menu' );

/**
 * Print the settings page.
 */
function render_settings_page() {
	$pages      = \get_pages();
	$slashpages = get_slashpages();
	?>
	<div class="wrap">
		<h1><?php \esc_html_e( 'Slash Pages', 'wellknownslashpages' ); ?></h1>
		<p>
			<?php
			\printf(
				/* translators: %s: URL of the OPML document. */
				\__( 'Choose a page or enter a URL for every slash page you have. They are listed at %s.', 'wellknownslashpages' ),
				'<a href="' . \esc_url( \home_url( '/.well-known/slashpages' ) ) . '"><code>/.well-known/slashpages</code></a>'
			);
			?>
		</p>
		<form method="post" action="options.php">
			<?php \settings_fields( 'well-known-slashpages' ); ?>
			<table class="form-table" role="presentation">
				<?php
				foreach ( get_values() as $name => $value ) :
					$id       = 'well-known-slashpages-' . $name;
					$selected = \is_int( $value ) ? $value : 'url';
					?>
				<tr>
					<th scope="row"><label for="<?php echo \esc_attr( $id ); ?>">/<?php echo \esc_html( $name ); ?></label></th>
					<td>
						<select id="<?php echo \esc_attr( $id ); ?>" name="<?php echo \esc_attr( OPTION . '[' . $name . '][page]' ); ?>">
							<option value="0"><?php \esc_html_e( '— None —', 'wellknownslashpages' ); ?></option>
							<option value="url" <?php \selected( $selected, 'url' ); ?>><?php \esc_html_e( 'Custom URL', 'wellknownslashpages' ); ?></option>
							<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo \esc_attr( $page->ID ); ?>" <?php \selected( $selected, $page->ID ); ?>><?php echo \esc_html( \get_the_title( $page ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="url" class="regular-text" placeholder="https://" aria-label="<?php echo \esc_attr( \sprintf( /* translators: %s: Slash page name. */ \__( 'Custom URL for /%s', 'wellknownslashpages' ), $name ) ); ?>" name="<?php echo \esc_attr( OPTION . '[' . $name . '][url]' ); ?>" value="<?php echo \is_string( $value ) ? \esc_attr( $value ) : ''; ?>" />
						<p class="description"><?php echo \esc_html( $slashpages[ $name ]['description'] ); ?></p>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php \submit_button(); ?>
		</form>
	</div>
	<?php
}
