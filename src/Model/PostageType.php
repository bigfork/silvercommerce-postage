<?php

namespace SilverCommerce\Postage\Model;

use SilverStripe\ORM\DataObject;
use SilverCommerce\GeoZones\Model\Zone;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\Postage\Helpers\Parcel;
use SilverCommerce\TaxAdmin\Model\TaxCategory;
use SilverCommerce\Postage\Tasks\PostageUpgradeTask;

/**
 * Postage Types are a base class for creating differnt types of postage.
 *
 * Custom postage types need to provide their own implementation of `getPossiblePostage`.
 * This is the method that will be called when trying to determine a list of
 * possible postage options for the current order.
 *
 */
class PostageType extends DataObject
{
    private static $table_name = 'PostageType';

    private static $db = [
        "Name" => "Varchar",
        "Enabled" => "Boolean"
    ];

    private static $has_one = [
        "Tax" => TaxCategory::class,
        "Site" => SiteConfig::class
    ];

    private static $many_many = [
        "Locations" => Zone::class
    ];

    private static $summary_fields = [
        "Name",
        "Enabled"
    ];

    public function getTitle()
    {
        return $this->Name;
    }

    /**
     * Return a list of possible postage options that can be rendered into the postage
     * form.
     *
     * NOTE Even if you have one option, you need to return a list, containing one item.
     *
     * The list can be any implementation of an SSList
     *
     * @param Parcel
     * @return SSList
     */
    public function getPossiblePostage(Parcel $parcel)
    {
        user_error("You must implement your own 'getPossiblePostage' method");
    }

    /**
     * {@inheritdoc}
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (PostageUpgradeTask::config()->run_during_dev_build) {
            $task = new PostageUpgradeTask();
            $task->up();
        }
    }
}
