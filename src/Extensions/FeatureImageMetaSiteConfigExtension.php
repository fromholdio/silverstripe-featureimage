<?php

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class FeatureImageMetaSiteConfigExtension extends DataExtension
{
    const MODE_FEATURE = 'feature';
    const MODE_CUSTOM = 'custom';

    private static $db = [
        'SocialMetaSiteImageMode' => 'Varchar'
    ];

    private static $social_meta_site_image_mode_labels = [
        self::MODE_FEATURE => 'Use site feature image',
        self::MODE_CUSTOM => 'Upload/select custom image'
    ];

    public function populateDefaults()
    {
        $this->getOwner()->SocialMetaSiteImageMode = $this->getOwner()->getDefaultSocialMetaSiteImageMode();
    }

    public function getSocialMetaSiteImage()
    {
        $image = null;
        $mode = $this->getOwner()->SocialMetaSiteImageMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultSocialMetaSiteImageMode();
        }
        if ($mode === self::MODE_CUSTOM) {
            $image = $this->getOwner()->SocialMetaSiteImage();
        }
        else if ($mode === self::MODE_FEATURE) {
            $image = $this->getOwner()->FeatureImage();
        }
        if (!$image) {
            $image = $this->getOwner()->getDefaultSocialMetaSiteImage();
        }
        if ($this->getOwner()->hasMethod('updateSocialMetaSiteImage')) {
            $image = $this->getOwner()->updateSocialMetaSiteImage($mode, $image);
        }
        return $image;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $imageField = $fields->dataFieldByName('SocialMetaSiteImage');
        if (!$imageField) {
            return;
        }

        $modeOptions = $this->getOwner()->getSocialMetaSiteImageModeOptions();
        if (!is_array($modeOptions) || count($modeOptions) < 1) {
            return;
        }

        if (!$this->getOwner()->SocialMetaSiteImageMode) {
            $this->getOwner()->SocialMetaSiteImageMode = $this->getOwner()->getDefaultSocialMetaSiteImageMode();
        }

        $modeField = OptionsetField::create(
            'SocialMetaSiteImageMode',
            $this->getOwner()->fieldLabel('SocialMetaSiteImageMode'),
            $modeOptions
        );

        $imageWrapper = Wrapper::create($imageField);
        $imageWrapper
            ->displayIf('SocialMetaSiteImageMode')
            ->isEqualTo(self::MODE_CUSTOM);

        $fields->replaceField('SocialMetaSiteImage', $modeField);
        $fields->insertAfter('SocialMetaSiteImageMode', $imageWrapper);
    }

    public function getDefaultSocialMetaSiteImageMode()
    {
        return self::MODE_FEATURE;
    }

    public function getSocialMetaSiteImageModeOptions()
    {
        return $this->getOwner()->config()->get('social_meta_site_image_mode_labels');
    }

    public function onBeforeWrite()
    {
        if (!$this->getOwner()->SocialMetaSiteImageMode) {
            $this->getOwner()->SocialMetaSiteImageMode = $this->getOwner()->getDefaultSocialMetaSiteImageMode();
        }
    }
}