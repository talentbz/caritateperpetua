<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class CFWAction
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
abstract class CFWAction {
	/**
	 * @since 1.0.0
	 * @access protected
	 * @var string $id
	 */
	protected $id = '';

	/**
	 * @since 1.0.6
	 * @access private
	 * @var bool
	 */
	private $no_privilege;

	/**
	 * @since 1.0.6
	 * @access private
	 * @var string
	 */
	private $action_prefix;

	/**
	 * Action constructor.
	 *
	 * @param $id
	 * @param bool $no_privilege
	 * @param string $action_prefix
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, bool $no_privilege = true, string $action_prefix = 'wc_ajax_' ) {
		$this->no_privilege  = $no_privilege;
		$this->action_prefix = $action_prefix;

		$this->set_id( $id );
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param boolean $np
	 */
	public function load() {
		remove_all_actions( "{$this->action_prefix}{$this->get_id()}" );
		add_action( "{$this->action_prefix}{$this->get_id()}", array( $this, 'execute' ) );

		if ( true === $this->no_privilege ) {
			remove_all_actions( "{$this->action_prefix}nopriv_{$this->get_id()}" );
			add_action( "{$this->action_prefix}nopriv_{$this->get_id()}", array( $this, 'execute' ) );
		}
	}

	public function execute() {
		/**
		 * PHP Warning / Notice Suppression
		 */
		if ( ! defined( 'CFW_DEV_MODE' ) || ! CFW_DEV_MODE ) {
			ini_set( 'display_errors', 'Off' );
		}

		if ( ! defined( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER' ) ) {
			// Try to prevent errors and errata from leaking into AJAX responses
			// This output buffer is discarded on out();
			@ob_end_clean(); // phpcs:ignore
			ob_start();
		}

		$this->action();
	}

	/**
	 * @since 1.0.0
	 * @access protected
	 * @param $out
	 */
	protected function out( $out ) {
		ini_set( 'display_errors', 'Off' );

		// TODO: Execute and out (in Action) should be final and not overrideable. Action needs to NOT force JSON as an object. Could use a parameter to flip JSON to object
		if ( ! defined( 'CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER' ) ) {
			@ob_end_clean(); // @phpcs:ignore
		}

		wp_send_json( $out );
	}
}
