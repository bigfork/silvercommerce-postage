<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverCommerce\GeoZones\Model\Zone;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\Postage\Helpers\Parcel;
use SilverCommerce\TaxAdmin\Model\TaxCategory;
use SilverCommerce\TaxAdmin\Helpers\MathsHelper;
use SilverCommerce\Postage\Helpers\PostageOption;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;

/**
 * Postage objects list available postage costs and destination locations
 *
 */
class QuantityBased extends PostageType
{
    private static $table_name = 'PostageType_QuantityBased';

    private static $has_many = [
        "Rates" => SinglePostageRate::class
    ];

    private static $many_many = [
        "Locations" => Zone::class
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $rates_field = $fields->dataFieldByName("Rates");

            if (isset($rates_field)) {
                $config = $rates_field->getConfig();
                $config
                    ->removeComponentsByType(GridFieldDetailForm::class)
                    ->removeComponentsByType(GridFieldEditButton::class)
                    ->removeComponentsByType(GridFieldDataColumns::class)
                    ->removeComponentsByType(GridFieldDeleteAction::class)
                    ->removeComponentsByType(GridFieldAddNewButton::class)
                    ->removeComponentsByType(GridFieldAddExistingAutocompleter::class)
                    ->addComponent(new GridFieldEditableColumns())
                    ->addComponent(new GridFieldAddNewInlineButton())
                    ->addComponent(new GridFieldDeleteAction());
            }
        });

        return parent::getCMSFields();
    }

    /**
     * If the current parcel is located in an area that we
     * allow and has an acceptable weight
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
        $value = (float)$parcel->getItems();
        $check = false;
        
        // Should this type filter based on location
        if (!$locations->exists()) {
            $check = true;
        } elseif (isset($country) && isset($region)) {
            $locations = $locations->filter([
                "Regions.CountryCode" => $country,
                "Regions.Code" => $region
            ]);

            if ($locations->exists()) {
                $check = true;
            }
        }

        if ($check) {
            $rates = $this->Rates()->filter([
                "Min:LessThanOrEqual" => $value,
                "Max:GreaterThanOrEqual" => $value
            ]);

            foreach ($rates as $rate) {
                $return->add(PostageOption::create(
                    $this->Name,
                    $rate->Price,
                    $this->Tax()->Rates()->first()
                ));
            }
        }

        return $return;
    }

}
