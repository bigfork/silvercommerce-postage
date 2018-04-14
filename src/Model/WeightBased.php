<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
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
class WeightBased extends PostageType
{
    private static $table_name = 'PostageType_WeightBased';

    private static $has_many = [
        "Rates" => SinglePostageRate::class
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
        $exclude = $this->Exclusions();
        $country = $parcel->getCountry();
        $region = $parcel->getRegion();
        $value = (float)$parcel->getWeight();
        $check = false;
        $tax = null;

        if ($this->Tax()->exists()) {
            $tax = $this->Tax()->ValidTax();
        }

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
                    $check = true;
                }
            } else {
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
                    $tax
                ));
            }
        }

        return $return;
    }
}
