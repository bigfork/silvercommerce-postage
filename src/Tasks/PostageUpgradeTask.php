<?php

namespace SilverCommerce\Postage\Extensions;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DatabaseAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\Queries\SQLInsert;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverCommerce\Postage\Model\PostageType;
use SilverCommerce\GeoZones\Model\Zone;

class PostageUpgradeTask extends MigrationTask
{
    private static $segment = 'PostageUpgradeTask';

    protected $title = 'Upgrage Postage';

    protected $description = 'Upgrade postage to the latest setup';

    /**
     * Should this task be invoked automatically via dev/build?
     *
     * @config
     * @var bool
     */
    private static $run_during_dev_build = true;

    protected $tables = [
        "PostageType_FlatRate_Locations" => "PostageType_FlatRateID",
        "PostageType_PriceBased_Locations" => "PostageType_PriceBasedID",
        "PostageType_QuantityBased_Locations" => "PostageType_QuantityBasedID",
        "PostageType_WeightBased_Locations" => "PostageType_WeightBasedID"
    ];

    /**
     * {@inheritdoc}
     */
    public function run($request)
    {
        if ($request && $request->getVar('direction') == 'down') {
            $this->down();
        } else {
            $this->up();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->log("Upgrading Postage");
        $i = 0;
        $table_list = DB::table_list();

        // Get locations from existing join tables
        foreach ($this->tables as $table => $column) {
            if (in_array($table, $table_list)) {
                $sqlQuery = new SQLSelect();
                $sqlQuery->setFrom($table);
                $result = $sqlQuery->execute();

                foreach ($result as $row) {
                    $postage = PostageType::get()->byID($row[$column]);
                    $zone = Zone::get()->byID($row['GeoZoneZoneID']);

                    if (isset($postage) && isset($zone)) {
                        $postage->Locations()->add($zone);
                        $i++;
                    }
                }

                // Finally remove table
                DB::query("DROP TABLE $table;");
            }
        }

        // If we have data to migrate, do it now
        if ($i > 0) {
            $this->log("Migrated {$i} postage locations");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->log("Downgrading Postage");
        $i = 0;

        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom("PostageType_Locations");
        $result = $sqlQuery->execute();

        foreach ($result as $row) {
            $postage = PostageType::get()->byID($row['PostageTypeID']);
            $zone = Zone::get()->byID($row['GeoZoneZoneID']);

            if (isset($postage) && isset($zone)) {
                $postage->Locations()->add($zone);
                $i++;
            }
        }

        // If we have data to migrate, do it now
        if ($i > 0) {
            $this->log("Downgraded {$i} postage locations");
        }
    }

    /**
     * Output a message
     *
     * @param string $text
     */
    protected function log($text)
    {
        if (Controller::curr() instanceof DatabaseAdmin) {
            DB::alteration_message($text, 'obsolete');
        } elseif (Director::is_cli()) {
            echo $text . "\n";
        } else {
            echo $text . "<br/>";
        }
    }
}
