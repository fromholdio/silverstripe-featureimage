<?php

declare(strict_types=1);

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class FeatureImageSiteConfigExtension extends Extension
{
    private static array $has_one = [
        'LocalFeatureImage' => Image::class
    ];

    private static array $owns = [
        'LocalFeatureImage'
    ];

    private static string $feature_image_tab_path = 'Root.Main';

    private static string $feature_image_upload_folder = 'features';

    public function getFeatureImage()
    {
        $image = $this->getOwner()->LocalFeatureImage();
        return $image && $image->exists() ? $image : null;
    }

    public function updateCMSFields(FieldList $fields): void
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
