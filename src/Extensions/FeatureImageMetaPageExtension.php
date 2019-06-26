<?php

namespace Fromholdio\FeatureImage\Extensions;

use Innoweb\MetaCounter\Model\SiteTreeExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class FeatureImageMetaPageExtension extends SiteTreeExtension
{
    const MODE_FEATURE = 'feature';
    const MODE_SITEMETA = 'sitemeta';
    const MODE_CUSTOM = 'custom';

    private static $db = [
        'MetaImageMode' => 'Varchar'
    ];

    private static $social_meta_image_mode_labels = [
        self::MODE_FEATURE => 'Use page feature image',
        self::MODE_SITEMETA => 'Use site meta image',
        self::MODE_CUSTOM => 'Upload/select custom image'
    ];

    public function getSocialMetaImage()
    {
        $image = null;
        $mode = $this->getOwner()->MetaImageMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultMetaImageMode();
        }
        if ($mode === self::MODE_CUSTOM) {
            if ($this->getOwner()->MetaImage() && $this->getOwner()->MetaImage()->exists()) {
                $image = $this->getOwner()->MetaImage();
            }
        }
        else if ($mode === self::MODE_FEATURE) {
            $image = $this->getOwner()->getInheritedFeatureImage();
        }
        else if ($mode === self::MODE_SITEMETA) {
            $config = $this->getOwner()->getSocialMetaConfig();
            $image = $config->getSocialMetaValue('SiteImage');
        }
        if (!$image) {
            $image = $this->getOwner()->getDefaultSocialMetaImage();
        }
        if ($this->getOwner()->hasMethod('updateSocialMetaImage')) {
            $image = $this->getOwner()->updateSocialMetaImage($mode, $image);
        }
        return $image;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $imageField = $fields->dataFieldByName('MetaImage');
        if (!$imageField) {
            return;
        }

        $modeOptions = $this->getOwner()->getMetaImageModeOptions();
        if (!is_array($modeOptions) || count($modeOptions) < 1) {
            return;
        }

        if (!$this->getOwner()->MetaImageMode) {
            $this->getOwner()->MetaImageMode = $this->getOwner()->getDefaultMetaImageMode();
        }

        $modeField = OptionsetField::create(
            'MetaImageMode',
            $this->getOwner()->fieldLabel('MetaImageMode'),
            $modeOptions
        );

        $imageWrapper = Wrapper::create($imageField);
        $imageWrapper
            ->displayIf('MetaImageMode')
            ->isEqualTo(self::MODE_CUSTOM);

        $fields->replaceField('MetaImage', $modeField);
        $fields->insertAfter('MetaImageMode', $imageWrapper);
    }

    public function getDefaultMetaImageMode()
    {
        return self::MODE_FEATURE;
    }

    public function getMetaImageModeOptions()
    {
        return $this->getOwner()->config()->get('social_meta_image_mode_labels');
    }

    public function onBeforeWrite()
    {
        if ($this->getOwner()->MetaImageMode === $this->getOwner()->getDefaultMetaImageMode()) {
            $this->getOwner()->MetaImageMode = '';
        }
    }
}