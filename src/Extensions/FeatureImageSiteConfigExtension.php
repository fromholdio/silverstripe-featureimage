<?php

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class FeatureImageSiteConfigExtension extends DataExtension
{
    private static $has_one = [
        'LocalFeatureImage' => Image::class
    ];

    private static $owns = [
        'LocalFeatureImage'
    ];

    private static $feature_image_tab_path = 'Root.Main';
    private static $feature_image_upload_folder = 'features';

    public function updateCMSFields(FieldList $fields)
    {
        $tabPath = $this->getOwner()->config()->get('feature_image_tab_path');
        if ($tabPath) {
           $imageField = UploadField::create(
               'LocalFeatureImage',
               $this->getOwner()->fieldLabel('FeatureImage')
           );
            $folder = $this->getOwner()->config()->get('feature_image_upload_folder');
            if (!empty($folder)) {
                $imageField->setFolderName($folder);
            }
           $fields->addFieldToTab($tabPath, $imageField);
        }
    }
}
