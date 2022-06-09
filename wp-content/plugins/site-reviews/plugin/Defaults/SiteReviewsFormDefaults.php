<?php

namespace GeminiLabs\SiteReviews\Defaults;

use GeminiLabs\SiteReviews\Defaults\DefaultsAbstract as Defaults;
use GeminiLabs\SiteReviews\Helper;
use GeminiLabs\SiteReviews\Helpers\Arr;

class SiteReviewsFormDefaults extends Defaults
{
    /**
     * @var array
     */
    public $casts = [
        'debug' => 'bool',
    ];

    /**
     * @var array
     */
    public $guarded = [
        'description',
        'title',
    ];

    /**
     * @var array
     */
    public $mapped = [
        'assign_to' => 'assigned_posts',
        'category' => 'assigned_terms',
        'user' => 'assigned_users',
    ];

    /**
     * @var array
     */
    public $sanitize = [
        'form_id' => 'id',
        'id' => 'id',
    ];

    /**
     * @return array
     */
    protected function defaults()
    {
        return [
            'assigned_posts' => '',
            'assigned_terms' => '',
            'assigned_users' => '',
            'class' => '',
            'debug' => false,
            'description' => '',
            'excluded' => '',
            'form_id' => '', // used for the validation session key and to generate the honeypot hash
            'hide' => '',
            'id' => '',
            'title' => '',
        ];
    }

    /**
     * Normalize provided values, this always runs first.
     * @return array
     */
    protected function normalize(array $values = [])
    {
        foreach ($this->mapped as $old => $new) {
            if ('custom' === Arr::get($values, $old)) {
                $values[$old] = Arr::get($values, $new);
            }
        }
        return parent::normalize($values);
    }
}
