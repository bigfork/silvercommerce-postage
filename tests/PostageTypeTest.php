<?php

namespace SilverCommerce\Postage\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Config\Config;
use SilverCommerce\GeoZones\Model\Region;
use SilverCommerce\Postage\Helpers\Parcel;
use SilverCommerce\Postage\Model\FlatRate;
use SilverCommerce\Postage\Model\WeightBased;
use SilverCommerce\Postage\Model\PriceBased;
use SilverCommerce\Postage\Model\QuantityBased;

/**
 * Test functionality of provided postage types
 *
 */
class PostageTypeTest extends SapphireTest
{
    const POSTAGE_UK_NAME = "UK";

    const POSTAGE_GLOBAL_NAME = "Global";

    /**
     * Add scaffold postage types
     *
     * @var string
     * @config
     */
    protected static $fixture_file = 'PostageTypes.yml';

    public function setUp()
    {
        parent::setUp();
        Config::inst()->set(Region::class, "create_on_build", false);
    }

    /**
     * Test possible postage results for flat rate shipping
     */
    public function testFlatRate()
    {
        $uk_parcel = Parcel::create("GB", "GLS");
        $de_parcel = Parcel::create("DE", "BE");

        $uk_rate = $this->objFromFixture(FlatRate::class, 'uk');
        $global_rate = $this->objFromFixture(FlatRate::class, 'global');

        $uk_results = $uk_rate->getPossiblePostage($uk_parcel);
        $de_results = $uk_rate->getPossiblePostage($de_parcel);
        $global_results = $global_rate->getPossiblePostage($uk_parcel);

        $this->assertEquals(1, $uk_results->count());
        $this->assertEquals(self::POSTAGE_UK_NAME, $uk_results->first()->getName());
        $this->assertEquals(5.0, $uk_results->first()->getPrice());
        $this->assertEquals(0, $de_results->count());
        $this->assertEquals(1, $global_results->count());
        $this->assertEquals(self::POSTAGE_GLOBAL_NAME, $global_results->first()->getName());
        $this->assertEquals(9.0, $global_results->first()->getPrice());
    }

    /**
     * Test possible postage results for flat rate shipping
     */
    public function testWeightBased()
    {
        $uk_parcel = Parcel::create("GB", "GLS")
            ->setWeight(1);

        $de_parcel = Parcel::create("DE", "BE")
            ->setWeight(1);

        $uk_rate = $this->objFromFixture(WeightBased::class, 'uk');
        $global_rate = $this->objFromFixture(WeightBased::class, 'global');

        $uk_results = $uk_rate->getPossiblePostage($uk_parcel);
        $de_results = $uk_rate->getPossiblePostage($de_parcel);
        $global_results = $global_rate->getPossiblePostage($uk_parcel);

        $this->assertEquals(1, $uk_results->count());
        $this->assertEquals(self::POSTAGE_UK_NAME, $uk_results->first()->getName());
        $this->assertEquals(1, $uk_results->first()->getPrice());
        $this->assertEquals(0, $de_results->count());
        $this->assertEquals(1, $global_results->count());
        $this->assertEquals(self::POSTAGE_GLOBAL_NAME, $global_results->first()->getName());
        $this->assertEquals(10, $global_results->first()->getPrice());
    }

    /**
     * Test possible postage results for flat rate shipping
     */
    public function testPriceBased()
    {
        $uk_parcel = Parcel::create("GB", "GLS")
            ->setValue(8);

        $de_parcel = Parcel::create("DE", "BE")
            ->setValue(8);

        $uk_rate = $this->objFromFixture(PriceBased::class, 'uk');
        $global_rate = $this->objFromFixture(PriceBased::class, 'global');

        $uk_results = $uk_rate->getPossiblePostage($uk_parcel);
        $de_results = $uk_rate->getPossiblePostage($de_parcel);
        $global_results = $global_rate->getPossiblePostage($uk_parcel);

        $this->assertEquals(1, $uk_results->count());
        $this->assertEquals(self::POSTAGE_UK_NAME, $uk_results->first()->getName());
        $this->assertEquals(5, $uk_results->first()->getPrice());
        $this->assertEquals(0, $de_results->count());
        $this->assertEquals(1, $global_results->count());
        $this->assertEquals(self::POSTAGE_GLOBAL_NAME, $global_results->first()->getName());
        $this->assertEquals(10, $global_results->first()->getPrice());
    }

    /**
     * Test possible postage results for flat rate shipping
     */
    public function testQuantityBased()
    {
        $uk_parcel = Parcel::create("GB", "GLS")
            ->setItems(3);

        $de_parcel = Parcel::create("DE", "BE")
            ->setItems(3);

        $uk_rate = $this->objFromFixture(QuantityBased::class, 'uk');
        $global_rate = $this->objFromFixture(QuantityBased::class, 'global');

        $uk_results = $uk_rate->getPossiblePostage($uk_parcel);
        $de_results = $uk_rate->getPossiblePostage($de_parcel);
        $global_results = $global_rate->getPossiblePostage($uk_parcel);

        $this->assertEquals(1, $uk_results->count());
        $this->assertEquals(self::POSTAGE_UK_NAME, $uk_results->first()->getName());
        $this->assertEquals(4, $uk_results->first()->getPrice());
        $this->assertEquals(0, $de_results->count());
        $this->assertEquals(1, $global_results->count());
        $this->assertEquals(self::POSTAGE_GLOBAL_NAME, $global_results->first()->getName());
        $this->assertEquals(10, $global_results->first()->getPrice());
    }
}
