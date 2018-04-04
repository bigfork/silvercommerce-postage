<?php

namespace SilverCommerce\Postage\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverStripe\Forms\ReadonlyField;
use SilverCommerce\Postage\Helpers\PostageOption;

/**
 * Add extra postage options to a DataObject (for example and Invoice or
 * Estimate)
 *
 * This function will attempt to autocalculate tax for an extended object,
 * and will add look for the following extension hooks to update the prices:
 *
 *  - `updateTaxTotal` (to add the postage tax to the current tax value)
 *  - `updateTotal` (to add the current postage price to the total value)
 *
 */
class PostageExtension extends DataExtension
{
    private static $db = [
        "PostageTitle" => "Varchar",
        "PostagePrice" => "Currency"
    ];

    private static $has_one = [
        "PostageTax"   => TaxRate::class
    ];

    private static $casting = [
        "PostageDetails" => "Varchar",
        "PostageTaxPrice" => "Currency",
        "PostageTotal" => "Currency"
    ];

    /**
     * Set the postage settings on this object based on the provided
     * PostageOption.
     *
     * @return self
     */
    public function setPostage(PostageOption $postage)
    {
        $this->owner->PostageTitle = $postage->getName();
        $this->owner->PostagePrice = $postage->getPrice();
        $this->owner->PostageTax = $postage->getTax();

        return $this->owner;
    }

    /**
     * Generate a PostageOption based on this object's details
     *
     * @return PostageOption
     */
    public function getPostage()
    {
        return PostageOption::create(
            $this->owner->PostageTitle,
            $this->owner->PostagePrice,
            $this->owner->PostageTax
        );
    }

    /**
     * Remove all postage settings from this object
     *
     * @return self
     */
    public function clearPostage()
    {
        $this->owner->PostageTitle = "";
        $this->owner->PostagePrice = 0;
        $this->owner->PostageTaxID = null;
    }

    /**
     * Generate a string outlining the details of selected postage
     *
     * @return string
     */
    public function getPostageDetails()
    {
        return $this->owner->PostageTitle . " (" . $this->owner->obj("PostageTotal")->Nice() . ")";
    }

    /**
     * Get the total value of postage (including tax)
     *
     * @return float
     */
    public function getPostageTotal()
    {
        $price = $this->owner->PostagePrice;

        return $price + $this->owner->PostageTaxPrice;
    }

    /**
     * Get the total value of postage (including tax)
     *
     * @return float
     */
    public function getPostageTaxPrice()
    {
        $price = $this->owner->PostagePrice;
        $tax = $this->owner->PostageTax();
        $rate = ($tax->exists()) ? $tax->Rate : 0;

        return ($price / 100 * $rate);
    }

    /**
     * Add the current postage tax top the total tax
     * on this object
     */
    public function updateTaxTotal(&$total)
    {
        $total = $total + $this->owner->PostageTaxPrice;
    }

    /**
     * Attempt to add the postage price to current totals
     *
     */
    public function updateTotal(&$total)
    {
        $total = $total + $this->owner->PostagePrice;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $config = SiteConfig::current_site_config();

        // Move postage selection fields
        $title = $fields->dataFieldByName("PostageTitle");
        $price = $fields->dataFieldByName("PostagePrice");
        $tax = $fields->dataFieldByName("PostageTaxID");

        $fields->addFieldsToTab(
            "Root.Delivery",
            [
                $title,
                $price,
                $tax
            ]
        );
    }
}
