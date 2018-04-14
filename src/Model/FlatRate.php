<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
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
        $exclude = $this->Exclusions();
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

        if ($exclude->exists()) {
            $exclude = $exclude->filter(
                [
                    "Regions.CountryCode" => $country,
                    "Regions.Code" => $region
                ]
            );
        }

        if (!$exclude->exists()) {
            if ($locations->exists()) {
                $locations = $locations->filter(
                    [
                        "Regions.CountryCode" => $country,
                        "Regions.Code" => $region
                    ]
                );

                if ($locations->exists()) {
                    $return->add($postage);
                }
            } else {
                $return->add($postage);
            }
        }

        return $return;
    }
}
