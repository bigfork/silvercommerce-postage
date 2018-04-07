<?php

namespace SilverCommerce\Postage\Tests\Control;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Control\Director;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Control\Controller;
use SilverCommerce\Postage\Forms\PostageForm;
use SilverCommerce\Postage\Tests\Model\ExtendableObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Controller used for testing form submissions
 */
class PostageController extends Controller implements TestOnly
{

    private static $url_segment = 'postagetest';

    private static $allowed_actions = [
        'index',
        'complete',
        'PostageForm'
    ];

    public function getObject()
    {
        $session = $this->getRequest()->getSession();
        $id = $session->get("ObjectID");
        return ExtendableObject::get()->byID($id);
    }

    public function Link($action = null)
    {
        return Controller::join_links(
            $this->config()->url_segment,
            $action
        );
    }

    public function AbsoluteLink($action = null)
    {
        return Director::absoluteURL($this->Link($action));
    }

    public function RelativeLink($action = null)
    {
        return Controller::join_links(
            $this->Link($action)
        );
    }

    public function index()
    {
        $id = $this->getRequest()->param("ID");
        $object = ExtendableObject::get()->byID($id);
        $session = $this->getRequest()->getSession();
        $session->set("ObjectID", $object->ID);

        $html = '<p class="postage-key">';
        $html .= $object->getPostage()->getKey();
        $html .= '</p>';
        $content = DBHTMLText::create();
        $content->setValue($html);

        $this->customise([
            "Content" => $content
        ]);

        return $this->render();
    }

    public function complete()
    {
        $this->customise([
            "Content" => '<p class="message">form submitted</p>'
        ]);

        return $this->render();
    }

    public function PostageForm()
    {
        $object = $this->getObject();

        $form = PostageForm::create(
            $this,
            "PostageForm",
            $object,
            $object->SubTotal,
            $object->TotalWeight,
            $object->TotalItems,
            $object->DeliveryCountry,
            $object->DeliveryCounty
        );

        return $form;
    }
}
