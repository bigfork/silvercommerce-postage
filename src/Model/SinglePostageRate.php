<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\DataObject;
use SilverCommerce\Postage\Model\PriceBased as PriceBased;

class SinglePostageRate extends DataObject
{
    private static $table_name = 'SinglePostageRate';

    private static $db = [
        "Min" => "Decimal",
        "Max" => "Decimal",
        "Price" => "Currency"
    ];

    private static $has_one = [
        "WeightPostage" => WeightBased::class,
        "PricePostage" => PriceBased::class,
        "QuantityPostage" => QuantityBased::class
    ];

    private static $summary_fields = [
        "Min",
        "Max",
        "Price"
    ];
}
