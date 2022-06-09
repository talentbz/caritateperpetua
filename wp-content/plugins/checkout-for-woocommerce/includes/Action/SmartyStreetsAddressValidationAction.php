<?php

namespace Objectiv\Plugins\Checkout\Action;

use SmartyStreets\PhpSdk\ClientBuilder;
use SmartyStreets\PhpSdk\International_Street\Client as InternationalStreetApiClient;
use SmartyStreets\PhpSdk\International_Street\Lookup;
use SmartyStreets\PhpSdk\StaticCredentials;
use SmartyStreets\PhpSdk\US_Street\Client as USStreetApiClient;

/**
 * Class LogInAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class SmartyStreetsAddressValidationAction extends CFWAction {
	protected $smartystreets_auth_id;
	protected $smartystreets_auth_token;

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $smartystreets_auth_id, $smartystreets_auth_token ) {
		parent::__construct( 'cfw_smartystreets_address_validation', false );

		$this->smartystreets_auth_id    = $smartystreets_auth_id;
		$this->smartystreets_auth_token = $smartystreets_auth_token;
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		try {
			$original_address = $_POST['address'];

			if ( ! is_array( $original_address ) ) {
				throw new \Exception( 'POST address is not a valid array of address info.' );
			}

			$suggested_address = $this->get_suggested_address( $original_address );

			$output_address = $this->format_suggested_address( $original_address, $suggested_address );

			$this->out(
				array(
					'result'     => true,
					'address'    => stripslashes( $output_address ),
					'original'   => stripslashes( WC()->countries->get_formatted_address( $original_address ) ),
					'components' => $suggested_address,
				)
			);
		} catch ( \Exception $ex ) {
			$this->out(
				array(
					'result'  => false,
					'message' => $ex->getMessage(),
				)
			);
		}
	}

	protected function format_suggested_address( array $original_address, array $suggested_address ) {
		$changed_component_keys = array_keys( array_diff_assoc( $suggested_address, $original_address ) );

		if ( empty( $changed_component_keys ) ) {
			throw new \Exception( 'Suggested address matched input address' );
		}

		$poisoned_address = $suggested_address;
		$replace_start    = 'checkoutwc_0';
		$replace_end      = 'checkoutwc_1';

		foreach ( $changed_component_keys as $key ) {
			$poisoned_address[ $key ] = "{$replace_start}{$suggested_address[$key]}{$replace_end}";
		}

		$output_address = WC()->countries->get_formatted_address( $poisoned_address );
		$output_address = str_ireplace( $replace_start, '<span style="color:red">', $output_address );
		$output_address = str_ireplace( $replace_end, '</span>', $output_address );

		return $output_address;
	}

	protected function get_suggested_address( array $address ) {
		$credentials = new StaticCredentials( $this->smartystreets_auth_id, $this->smartystreets_auth_token );
		$builder     = new ClientBuilder( $credentials );

		$builder->retryAtMost( 0 )->withMaxTimeout( 3000 );

		// Whenever we add another condition to this tree it's time to break this out into OO with factory.
		if ( 'US' === $address['country'] ) {
			return $this->getDomesticAddressSuggestion( $address, $builder->buildUsStreetApiClient() );
		} elseif ( 'GB' === $address['country'] ) {
			return $this->getUKAddressSuggestion( $address, $builder->buildInternationalStreetApiClient() );
		} else {
			return $this->getInternationalAddressSuggestion( $address, $builder->buildInternationalStreetApiClient() );
		}
	}

	/**
	 * @param array $address
	 * @param USStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getDomesticAddressSuggestion( array $address, USStreetApiClient $client ): array {
		$lookup = new \SmartyStreets\PhpSdk\US_Street\Lookup();

		$lookup->setStreet( $address['address_1'] );
		$lookup->setStreet2( $address['address_2'] );
		$lookup->setCity( $address['city'] );
		$lookup->setState( $address['state'] );
		$lookup->setZipcode( $address['postcode'] );
		$lookup->setMaxCandidates( 1 );
		$lookup->setMatchStrategy( 'invalid' );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\US_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];

		$suggested_address   = $first_candidate->getDeliveryLine1();
		$suggested_address_2 = $first_candidate->getDeliveryLine2();
		$suggested_postcode  = $first_candidate->getComponents()->getZipcode();
		$suggested_state     = $first_candidate->getComponents()->getStateAbbreviation();
		$suggested_city      = $first_candidate->getComponents()->getCityName();

		return array(
			'address_1' => $suggested_address,
			'address_2' => $suggested_address_2,
			'city'      => $suggested_city,
			'state'     => $suggested_state,
			'postcode'  => $suggested_postcode,
			'country'   => 'US',
			'company'   => $address['company'],
		);
	}

	/**
	 * @param array $address
	 * @param InternationalStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getInternationalAddressSuggestion( array $address, InternationalStreetApiClient $client ): array {
		$lookup = new Lookup();

		$lookup->setInputId( '0' );
		$lookup->setAddress1( $address['address_1'] );
		$lookup->setAddress2( $address['address_2'] );
		$lookup->setLocality( $address['city'] );
		$lookup->setAdministrativeArea( $address['state'] );
		$lookup->setCountry( $address['country'] );
		$lookup->setPostalCode( $address['postcode'] );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\International_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];
		$analysis        = $first_candidate->getAnalysis();
		$precision       = $analysis->getAddressPrecision();

		if ( 'Premise' !== $precision && 'DeliveryPoint' !== $precision ) {
			throw new \Exception( 'Candidate match is too fuzzy' );
		}

		$suggested_address   = $first_candidate->getAddress1();
		$suggested_address_2 = $first_candidate->getAddress2();
		$suggested_country   = substr( $first_candidate->getComponents()->getCountryIso3(), 0, -1 );
		$suggested_zip       = ! empty( $first_candidate->getComponents()->getPostalCodeExtra() ) ? $first_candidate->getComponents()->getPostalCodeShort() . ' - ' . $first_candidate->getComponents()->getPostalCodeExtra() : $first_candidate->getComponents()->getPostalCodeShort();
		$suggested_state     = $first_candidate->getComponents()->getAdministrativeArea();
		$suggested_city      = $first_candidate->getComponents()->getLocality();

		return array(
			'address_1' => $suggested_address,
			'address_2' => $suggested_address_2,
			'company'   => $address['company'],
			'city'      => $suggested_city,
			'country'   => $suggested_country,
			'state'     => $suggested_state,
			'postcode'  => $suggested_zip,
		);
	}

		/**
	 * @param array $address
	 * @param InternationalStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getUKAddressSuggestion( array $address, InternationalStreetApiClient $client ): array {
		$lookup = new Lookup();

		$lookup->setInputId( '0' );
		$lookup->setAddress1( $address['address_1'] );
		$lookup->setAddress2( $address['address_2'] );
		$lookup->setLocality( $address['city'] );
		$lookup->setAdministrativeArea( $address['state'] );
		$lookup->setCountry( $address['country'] );
		$lookup->setPostalCode( $address['postcode'] );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\International_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];
		$analysis        = $first_candidate->getAnalysis();
		$precision       = $analysis->getAddressPrecision();

		if ( 'Premise' !== $precision && 'DeliveryPoint' !== $precision ) {
			throw new \Exception( 'Candidate match is too fuzzy' );
		}

		$suggested_address   = $first_candidate->getAddress1();
		$suggested_address_2 = $first_candidate->getAddress2();
		$suggested_country   = substr( $first_candidate->getComponents()->getCountryIso3(), 0, -1 );
		$suggested_zip       = ! empty( $first_candidate->getComponents()->getPostalCodeExtra() ) ? $first_candidate->getComponents()->getPostalCodeShort() . ' - ' . $first_candidate->getComponents()->getPostalCodeExtra() : $first_candidate->getComponents()->getPostalCodeShort();
		$suggested_state     = $first_candidate->getComponents()->getAdministrativeArea();
		$suggested_city      = $first_candidate->getComponents()->getLocality();

		$result = array(
			'address_1' => $suggested_address,
			'company'   => $address['company'],
			'city'      => $suggested_city,
			'country'   => $suggested_country,
			'state'     => $suggested_state,
			'postcode'  => $suggested_zip,
		);

		if ( ! in_array( $suggested_address_2, $result, true ) ) {
			$result['address_2'] = $suggested_address_2;
		}

		return $result;
	}
}
