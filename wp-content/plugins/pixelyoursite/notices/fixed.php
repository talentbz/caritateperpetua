<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/*
 * Notice structure
        [
            'plugins' =>[], // can be "woo","wcf","edd" or empty array
            'slug'  => '',// unique id
            'message' => '' // message with html tags
        ]
 * */

function adminGetFixedNotices() {
    return [
        [
            'plugins' =>["woo","wcf"],
            'slug'  => 'wcf_and_woo_promo',
            'message' => 'HOT: Improve CartFlows tracking with PixelYourSite Professional: <a target="_blank" href="https://www.pixelyoursite.com/cartflows-and-pixelyoursite">CLICK TO LEARN MORE</a>'
        ]
    ];
}