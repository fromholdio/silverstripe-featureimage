<?php

declare(strict_types=1);

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class FeatureImageSiteExtension extends Extension
{
    public function updateSiteCMSFields(FieldList $fields): void
    {
        $tabPath = $this->getOwner()->config()->get('feature_image_tab_path');
        if ($tabPath) {
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
