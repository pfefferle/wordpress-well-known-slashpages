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
 * @return string[] Map of slash page name => description.
 */
function get_slashpages() {
	$slashpages = array(
		'about'        => 'A page all about you',
		'ai'           => 'Describes how you use, or don\'t use, AI',
		'blank'        => 'A page left intentionally blank',
		'blogroll'     => 'Other sites you read, follow, or recommend',
		'calendar'     => 'Dates from movies, TV, and culture that you observe',
		'canon'        => 'An annotated list of the things that have most shaped you',
		'carry'        => 'Your everyday carry items',
		'caw'          => 'Crows',
		'celebrities'  => 'Celebrities you\'ve spotted or met',
		'changelog'    => 'A summary of changes to your website',
		'chipotle'     => 'Your Chipotle order',
		'colophon'     => 'How the site is made, with which tools and technologies',
		'community'    => 'What a community is about and its values',
		'concerts'     => 'Concerts you\'ve attended',
		'contact'      => 'Ways for people to contact you',
		'daylists'     => 'Spotify Daylists created for you in the past',
		'death'        => 'Instructions for family and friends in case of your death',
		'defaults'     => 'The apps you use for various tasks',
		'disability'   => 'Your disabilities and how they affect you',
		'elsewhere'    => 'Output or writing on sites other than your own',
		'follow'       => 'Your feeds and other ways to subscribe',
		'for'          => 'Honoring loved ones or thanking those who supported the work',
		'glossary'     => 'Words you often use and what they mean',
		'green'        => 'How you contribute positively to the environment',
		'guy'          => 'An "I\'m an insert interest here guy" style page',
		'hats'         => 'Your hats',
		'hello'        => 'The ways you prefer to keep in touch',
		'hills'        => 'Foolish hills you\'re willing to die on',
		'ideas'        => 'Things you want to make, or concepts you\'re considering',
		'interests'    => 'Things you\'re passionate about',
		'junk'         => 'An indie-web yard sale',
		'languages'    => 'Languages you speak, understand, write, or are learning',
		'links'        => 'Links to sites you want to share or save for later',
		'next'         => 'What you\'re going to do next',
		'nope'         => 'Things you don\'t like or don\'t want to do',
		'now'          => 'What you\'re focused on at this point in your life',
		'pfp'          => 'A timeline of your profile pictures',
		'photo'        => 'A single photo, swapped now and then',
		'podroll'      => 'Podcasts you recommend',
		'posse'        => 'Your POSSE and cross-posting strategy',
		'postroll'     => 'Interesting or impactful blog posts you\'ve read',
		'predictions'  => 'Forecasts and long-term views on how the world may change',
		'pricing'      => 'Product or service costs',
		'questions'    => 'Questions you\'re asking or thinking about',
		'saudade'      => 'Honoring what and whom you miss, with love',
		'save'         => 'Referral links and discount codes for things you recommend',
		'self-hosting' => 'The services you host or manage yourself',
		'shoebox'      => 'A place for odds and ends',
		'since'        => 'Time since you started or stopped something',
		'sitemap'      => 'A list of pages on a website',
		'slashes'      => 'A list of all your slash pages',
		'someday'      => 'Things you\'d someday like to achieve, explore, or learn',
		'style'        => 'A personal or design style guide',
		'til'          => 'Concise write-ups on small things you\'ve learned',
		'tip'          => 'A way for people to tip you or buy you a coffee',
		'top4'         => 'Your ranked top three favorites and an honorable mention',
		'uses'         => 'The things you use on a daily basis',
		'verify'       => 'Your online identities, listed and proven',
		'where'        => 'Your current location',
		'why'          => 'What you care about and where you\'re heading',
		'wish'         => 'A wishlist that helps people connect with you',
		'yep'          => 'Things you agree with or will do',
	);

	/**
	 * Filters the slash pages that can be set on the settings page.
	 *
	 * @param string[] $slashpages Map of slash page name => description.
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
	$links = array();

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

		$links[] = array(
			'category' => '/' . $name,
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
	echo '<?xml version="1.0" encoding="' . \esc_attr( \get_option( 'blog_charset' ) ) . '"?>' . PHP_EOL;
	echo '<opml version="2.0">' . PHP_EOL;

	// The script shows the OPML as a page in the browser.
	$translations = \load_script_textdomain( SCRIPT, 'wellknownslashpages' );

	if ( $translations ) {
		\printf(
			"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" type="application/json" id="well-known-slashpages-translations">%s</script>' . PHP_EOL,
			\htmlspecialchars( $translations, ENT_XML1 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML.
		);
	}

	foreach ( get_script_urls() as $src ) {
		\printf(
			"\t" . '<script xmlns="http://www.w3.org/1999/xhtml" src="%s" defer=""/>' . PHP_EOL, // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- An OPML document has no wp_head().
			\htmlspecialchars( $src, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for XML.
		);
	}

	echo "\t<head>" . PHP_EOL;
	/* translators: %s: Site title. */
	echo "\t\t<title>" . \esc_html( \sprintf( \__( 'Slash pages of %s', 'wellknownslashpages' ), \get_bloginfo( 'name', 'display' ) ) ) . '</title>' . PHP_EOL;
	echo "\t\t<dateCreated>" . \esc_html( \gmdate( 'D, d M Y H:i:s' ) ) . ' GMT</dateCreated>' . PHP_EOL;
	echo "\t\t<ownerId>" . \esc_html( \home_url( '/' ) ) . '</ownerId>' . PHP_EOL;
	echo "\t\t<docs>http://opml.org/spec2.opml</docs>" . PHP_EOL;
	echo "\t</head>" . PHP_EOL;
	echo "\t<body>" . PHP_EOL;

	foreach ( get_outlines() as $link ) {
		\printf(
			"\t\t" . '<outline type="link" text="%s" category="%s" url="%s"/>' . PHP_EOL,
			\esc_attr( $link['text'] ),
			\esc_attr( $link['category'] ),
			\esc_url( $link['url'] )
		);
	}

	echo "\t</body>" . PHP_EOL;
	echo '</opml>' . PHP_EOL;
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
						<p class="description"><?php echo \esc_html( $slashpages[ $name ] ); ?></p>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php \submit_button(); ?>
		</form>
	</div>
	<?php
}
