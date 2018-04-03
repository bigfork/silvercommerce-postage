<?php

namespace SilverCommerce\Postage\Helpers;

use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\FieldType\DBCurrency;

/**
 * Generic container for postage data, PossiblePostage objects need to be
 * created by PostageTypes and then returned as part of the ArrayList
 * 
 * This object contains 3 generic params:
 * 
 *  - name (the name of the postage)
 *  - price (the cost of this object)
 *  - tax (the TaxCategory assigned to this object)
 */
class PostageOption
{
    use Injectable;

    /**
     * The Name of the current postage item
     */
    protected $name = null;

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The price of this current shipping option
     * 
     * @var int
     */
    protected $price = 0;

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Assign tax to this item, this should be an instance of
     * TaxCategory
     * 
     * @var TaxRate
     */
    protected $tax = null;

    /**
     * Get the value of tax
     */ 
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set the value of tax
     *
     * @return self
     */ 
    public function setTax(TaxRate $tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * Get the monitary value of tax for this option
     * 
     * @return float
     */
    public function getTaxPrice()
    {
        $price = $this->getPrice();
        $tax = $this->getTax();
        $rate = ($tax && $tax->exists()) ? $tax->Rate : 0;

        return ($price / 100 * $rate);
    }

    /**
     * Get the total monitary value of this option
     * 
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->getPrice() + $this->getTaxPrice();
    }

    /**
     * Generate a summary of this postage option
     * 
     * @var string
     */
    public function getSummary()
    {
        $area_currency = new DBCurrency("Cost");
        $area_currency->setValue($this->getTotalPrice());

        return $this->getName() . " (" . $area_currency->Nice() . ")";
    }

    /**
     * Generate a unique key for this parcel
     * 
     * @return string
     */
    public function getKey()
    {
        return base64_encode(json_encode((array)$this));
    }

    public function __construct($name, $price, TaxRate $tax = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->tax = $tax;
    }
}