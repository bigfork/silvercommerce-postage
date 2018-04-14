<?php

namespace SilverCommerce\Postage\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverCommerce\Postage\Model\PostageType;

/**
 * Add inverse many_many to Zones
 */
class ZoneExtension extends DataExtension
{
    private static $belongs_many_many = [
        'Postage' => PostageType::class . ".Locations",
        'ExcludedPostage' => PostageType::class . ".Exclusions"
    ];
}
