# PostageForm

This module includes a `PostageForm` object that can be used to automatically
add find relevent `PostageObject` items and gender them as a radio list.

It will also save the selection to the attached object, if the object extends
`PostageExtension` [see usage](usage.md).

## Form with dropdowns

You can add the postage form to your controller, that adds dropdowns to select
country and region codes, using the following code:

```php

use SilverStripe\Control\Controller;
use SilverCommerce\Postage\Forms\PostageForm;

class MyController extends Controller
{
    private static $allowed_actions = [
        "PostageForm"
    ];

    public function PostageForm()
    {
        $form = PostageForm::create(
            $this,
            "PostageForm",
            $my_postable_object, // An object that extends PostageExtension
            $total_value, // Total monitary value of the item to be posted
            $total_weight, // Total weight of item to be posted
            $total_items // Total number of items to be posted 
        );

        return $form;
    }
}
```

**NOTE** You have to fist select a country and region before the postage list
is generated

## Form with pre-defined country/region

You can also add a version of the form that accepts the country and region
codes (which then hides the dropdown fields). This form will autogenerate
the available `PostageOption` objects and when you submit the form will
write them to your object.

```php

use SilverStripe\Control\Controller;
use SilverCommerce\Postage\Forms\PostageForm;

class MyController extends Controller
{
    private static $allowed_actions = [
        "PostageForm"
    ];

    public function PostageForm()
    {
        $form = PostageForm::create(
            $this,
            "PostageForm",
            $my_postable_object, // An object that extends PostageExtension
            $total_value, // Total monitary value of the item to be posted
            $total_weight, // Total weight of item to be posted
            $total_items, // Total number of items to be posted
            "GB", // 2 character country code
            "GLS", // ISO-3166-2 subdivision/region code
        );

        return $form;
    }
}
```