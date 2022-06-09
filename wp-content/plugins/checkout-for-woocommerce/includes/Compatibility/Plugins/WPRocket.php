<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class WPRocket {
	public function init() {
		// Exclude CSS from WP Rocket
		add_filter( 'rocket_exclude_css', array( $this, 'exclude_css' ) );

		// Exclude our JavaScript from WP Rocket
		add_filter( 'rocket_exclude_js', array( $this, 'exclude_js' ) );
	}

	public function exclude_css( $excluded_css ) {
		$excluded_css[] = trailingslashit( CFW_PATH_ASSETS ) . 'dist/(.*).css';

		return $excluded_css;
	}

	public function exclude_js( $excluded_js ) {
		$excluded_js[] = trailingslashit( CFW_PATH_ASSETS ) . 'dist/(.*).js';

		return $excluded_js;
	}
}
