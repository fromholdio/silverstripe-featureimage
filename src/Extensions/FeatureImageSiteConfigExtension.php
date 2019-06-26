<?php

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class FeatureImageSiteConfigExtension extends DataExtension
{
    private static $has_one = [
        'FeatureImage' => Image::class
    ];

    private static $owns = [
        'FeatureImage'
    ];

    private static $feature_image_tab_path = 'Root.Main';

    public function updateCMSFields(FieldList $fields)
    {
        $tabPath = $this->getOwner()->config()->get('feature_image_tab_path');
        if ($tabPath) {
           $imageField = UploadField::create(
               'FeatureImage',
               $this->getOwner()->fieldLabel('FeatureImage')
           );
           $fields->addFieldToTab($tabPath, $imageField);
        }
    }
}