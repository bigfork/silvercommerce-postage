<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\Postage\Helpers\Parcel;
use SilverCommerce\Postage\Model\PostageType;
use SilverCommerce\Postage\Helpers\PostageOption;

/**
 * Represents a flat shipping cost, based on the selected regions.
 *
 * NOTE If you dont select any regions, this rate will be applied to
 * ALL regions
 */
class FlatRate extends PostageType
{
    private static $table_name = 'PostageType_FlatRate';

    private static $db = [
        "Price" => "Currency"
    ];

    private static $many_many = [
        "Locations" => Zone::class
    ];

    /**
     * If the current parcel is located in an area that we
     * allow flat rate
     *
     * @param Parcel
     * @return SSList
     */
    public function getPossiblePostage(Parcel $parcel)
    {
        $return = ArrayList::create();
        $locations = $this->Locations();
        $country = $parcel->getCountry();
        $region = $parcel->getRegion();
        $tax = null;

        if ($this->Tax()->exists()) {
            $tax = $this->Tax()->ValidTax();
        }
        
        $postage = PostageOption::create(
            $this->Name,
            $this->Price,
            $tax
        );

        if (!$locations->exists()) {
            $return->add($postage);
        } elseif (isset($country) && isset($region)) {
            $locations = $locations->filter([
                "Regions.CountryCode" => $country,
                "Regions.Code" => $region
            ]);

            if ($locations->exists()) {
                $return->add($postage);
            }
        }

        return $return;
    }
}
