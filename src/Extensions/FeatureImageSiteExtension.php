<?php

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\Forms\FieldList;

class FeatureImageSiteExtension extends SiteTreeExtension
{
    public function updateSiteCMSFields(FieldList $fields)
    {
        $tabPath = $this->getOwner()->config()->get('feature_image_tab_path');
        if ($tabPath)
        {
            $fields->addFieldsToTab(
                $tabPath,
                [
                    $imageField = UploadField::create(
                        'LocalFeatureImage',
                        $this->getOwner()->fieldLabel('FeatureImage')
                    )
                ]
            );
            $folder = $this->getOwner()->config()->get('feature_image_upload_folder');
            if (!empty($folder)) {
                $imageField->setFolderName($folder);
            }
        }
    }
}
