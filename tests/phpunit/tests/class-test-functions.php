<?php
/**
 * Test file for Functions.
 *
 * @package Well_Known_Slashpages
 */

namespace Well_Known_Slashpages\Tests;

use Well_Known_Slashpages as Slashpages;

/**
 * Test class for Functions.
 */
class Test_Functions extends \WP_UnitTestCase {

	/**
	 * Clean up the option after each test.
	 */
	public function tear_down() {
		\delete_option( Slashpages\OPTION );
		parent::tear_down();
	}

	/**
	 * Create a page.
	 *
	 * @param string $slug   The page slug.
	 * @param string $status The post status.
	 *
	 * @return int The page ID.
	 */
	private function create_page( $slug, $status = 'publish' ) {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_title'  => \ucfirst( $slug ) . ' page',
				'post_status' => $status,
			)
		);
	}

	/**
	 * Unsaved slash pages default to the published page with the same slug.
	 */
	public function test_defaults_to_page_with_same_slug() {
		$about = $this->create_page( 'about' );
		$this->create_page( 'now', 'draft' );

		$values = Slashpages\get_values();

		$this->assertSame( $about, $values['about'] );
		$this->assertSame( 0, $values['now'] );
		$this->assertSame( 0, $values['uses'] );
	}

	/**
	 * A saved "None" does not fall back to the page with the same slug.
	 */
	public function test_saved_none_overrides_default() {
		$this->create_page( 'about' );
		\update_option( Slashpages\OPTION, array( 'about' => 0 ) );

		$this->assertSame( 0, Slashpages\get_values()['about'] );
	}

	/**
	 * The form values are stored as page IDs, URLs or 0.
	 */
	public function test_sanitize() {
		$values = Slashpages\sanitize(
			array(
				'about' => array(
					'page' => '42',
					'url'  => 'https://example.com/ignored',
				),
				'now'   => array(
					'page' => 'url',
					'url'  => ' https://example.com/jetzt/ ',
				),
				'uses'  => array(
					'page' => 'url',
					'url'  => '',
				),
				'ai'    => array(
					'page' => 'url',
					'url'  => '/jetzt/',
				),
				'nope'  => array( 'page' => 'evil' ),
				'bogus' => array( 'page' => '1' ),
			)
		);

		$this->assertSame( 42, $values['about'] );
		$this->assertSame( 'https://example.com/jetzt/', $values['now'] );
		$this->assertSame( 0, $values['uses'] );
		$this->assertSame( 0, $values['ai'] );
		$this->assertSame( 0, $values['nope'] );
		$this->assertSame( 0, $values['colophon'] );
		$this->assertArrayNotHasKey( 'bogus', $values );
		$this->assertCount( \count( Slashpages\get_slashpages() ), $values );
	}

	/**
	 * Only set and published slash pages are listed, pages with their title,
	 * URLs with the name, and with all names of the page as category.
	 */
	public function test_get_outlines() {
		$about = $this->create_page( 'about' );
		$draft = $this->create_page( 'colophon', 'draft' );

		\update_option(
			Slashpages\OPTION,
			array(
				'about'     => $about,
				'changelog' => 'https://example.com/log/',
				'now'       => 'https://example.com/jetzt/',
				'colophon'  => $draft,
				'uses'      => 0,
			)
		);

		$this->assertSame(
			array(
				array(
					'category' => '/about',
					'text'     => 'About page',
					'url'      => \get_permalink( $about ),
				),
				array(
					'category' => '/changelog,/log',
					'text'     => 'Changelog',
					'url'      => 'https://example.com/log/',
				),
				array(
					'category' => '/now',
					'text'     => 'Now',
					'url'      => 'https://example.com/jetzt/',
				),
			),
			Slashpages\get_outlines()
		);
	}

	/**
	 * The OPML document is well-formed and contains the slash pages.
	 */
	public function test_render_opml() {
		\update_option(
			Slashpages\OPTION,
			array( 'now' => 'https://example.com/now?a=1&b=2' )
		);

		\ob_start();
		Slashpages\render_opml();
		$opml = \simplexml_load_string( \ob_get_clean() );

		$this->assertNotFalse( $opml );
		$this->assertSame( '2.0', (string) $opml['version'] );
		$this->assertSame( \home_url( '/' ), (string) $opml->head->ownerId );
		$this->assertCount( 1, $opml->body->outline );

		// The script that shows the OPML in the browser comes after wp-i18n.
		$scripts = $opml->children( 'http://www.w3.org/1999/xhtml' )->script;
		$sources = array();
		foreach ( $scripts as $script ) {
			$sources[] = (string) $script->attributes()->src;
		}
		$this->assertStringContainsString( 'assets/js/well-known-slashpages.js', \end( $sources ) );
		$this->assertGreaterThan( 1, \count( $sources ) );

		$outline = $opml->body->outline[0];
		$this->assertSame( 'link', (string) $outline['type'] );
		$this->assertSame( 'Now', (string) $outline['text'] );
		$this->assertSame( '/now', (string) $outline['category'] );
		$this->assertSame( 'https://example.com/now?a=1&b=2', (string) $outline['url'] );
	}

	/**
	 * The well-known URLs, with and without .opml, are routed to the plugin.
	 */
	public function test_rewrite_rule() {
		global $wp_rewrite;

		$this->set_permalink_structure( '/%postname%/' );
		Slashpages\rewrite_rules();
		\flush_rewrite_rules();

		$rules = \array_keys( $wp_rewrite->wp_rewrite_rules(), 'index.php?well-known=slashpages', true );
		$this->assertCount( 1, $rules );

		$regex = '#' . $rules[0] . '#';
		$this->assertSame( 1, \preg_match( $regex, '.well-known/slashpages' ) );
		$this->assertSame( 1, \preg_match( $regex, '.well-known/slashpages.opml' ) );
		$this->assertSame( 0, \preg_match( $regex, '.well-known/slashpages.json' ) );

		$this->assertContains( 'well-known', \apply_filters( 'query_vars', array() ) );
	}
}
