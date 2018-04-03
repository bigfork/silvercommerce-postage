<?php

namespace SilverCommerce\Postage\Tests\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverCommerce\Postage\Extensions\PostageExtension;

class ExtendableObject extends DataObject implements TestOnly
{
    private static $db = [
        "Title" => "Varchar"
    ];

    private static $extensions = [
        PostageExtension::class
    ];
}
