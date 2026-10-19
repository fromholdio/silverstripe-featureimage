<?php

declare(strict_types=1);

namespace Fromholdio\FeatureImage\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Dev\Deprecation;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\SiteConfig\SiteConfig;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class FeatureImagePageExtension extends Extension
{

    const MODE_SITE = 'site';
    
    const MODE_PARENT = 'parent';
    
    const MODE_SELF = 'self';

    private static array $db = [
        'FeatureImageMode' => 'Varchar'
    ];

    private static array $has_one = [
        'LocalFeatureImage' => Image::class
    ];

    private static array $owns = [
        'LocalFeatureImage'
    ];

    private static string $feature_image_tab_path = 'Root.Main';
    
    private static string $feature_image_upload_folder = 'features';

    private static array $feature_image_mode_labels = [
        self::MODE_PARENT => 'Inherit from parent page',
        self::MODE_SITE => 'Use site feature image',
        self::MODE_SELF => 'Upload/select custom image'
    ];

    public function getFeatureImage()
    {
        $image = null;
        $mode = $this->getOwner()->FeatureImageMode;
        if (!$mode) {
            $mode = $this->getOwner()->getDefaultFeatureImageMode();
        }
        
        if ($mode === self::MODE_SELF) {
            $localImage = $this->getOwner()->LocalFeatureImage();
            if ($localImage && $localImage->exists()) {
                $image = $localImage;
            }
        } elseif ($mode === self::MODE_PARENT) {
            if ($this->getOwner()->getIsFeatureImageMultisitesEnabled()) {
                if ($this->getOwner()->ParentID !== $this->getOwner()->SiteID) {
                    $image = $this->getOwner()->Parent()->getFeatureImage();
                }
            } else {
                if ($this->getOwner()->ParentID) {
                    $image = $this->getOwner()->Parent()->getFeatureImage();
                }
            }
        }
        
        if (!$image || $mode === self::MODE_SITE) {
            $config = $this->getOwner()->getFeatureImageConfig();
            if ($config) {
                $image = $config->getFeatureImage();
            }
        }

        if ($this->getOwner()->hasMethod('updateFeatureImage')) {
            return $this->getOwner()->updateFeatureImage($mode, $image);
        }
        
        return $image;
    }

    /**
     * @deprecated 1.1.0 Use getFeatureImage() instead
     */
    public function getInheritedFeatureImage()
    {
        Deprecation::notice('1.1.0', 'Use getFeatureImage() instead');
        return $this->getOwner()->getFeatureImage();
    }

    public function updateCMSFields(FieldList $fields): void
    {
        $tabPath = $this->getOwner()->config()->get('feature_image_tab_path');
        $imageFields = $this->getOwner()->getFeatureImageCMSFieldsArray();
        if ($tabPath && count($imageFields) > 0) {
            foreach ($imageFields as $imageField) {
                $fields->addFieldToTab($tabPath, $imageField);
            }
            
            if (!$this->getOwner()->FeatureImageMode) {
                $this->getOwner()->FeatureImageMode = $this->getOwner()->getDefaultFeatureImageMode();
            }
        }
    }

    public function getFeatureImageConfig()
    {
        $config = null;
        if ($this->getOwner()->getIsFeatureImageMultisitesEnabled()) {
            $site = $this->getOwner()->Site();
            if ($site && $site->exists()) {
                $config = $site;
            }
        } else {
            $config = SiteConfig::current_site_config();
        }

        if ($this->getOwner()->hasMethod('updateFeatureImageConfig')) {
            return $this->getOwner()->updateFeatureImageConfig($config);
        }
        
        return $config;
    }

    public function getDefaultFeatureImageMode()
    {
        $mode = self::MODE_PARENT;
        if ($this->getOwner()->getIsFeatureImageMultisitesEnabled()) {
            if ($this->getOwner()->ParentID === $this->getOwner()->SiteID) {
                $mode = self::MODE_SITE;
            } elseif ($this->getOwner()->ID === $this->getOwner()->SiteID) {
                $mode = self::MODE_SELF;
            }
        } elseif (!$this->getOwner()->ParentID) {
            $mode = self::MODE_SITE;
        }

        if ($this->getOwner()->hasMethod('updateDefaultFeatureImageMode')) {
            return $this->getOwner()->updateDefaultFeatureImageMode($mode);
        }
        
        return $mode;
    }

    public function getFeatureImageModeOptions()
    {
        $options = $this->getOwner()->config()->get('feature_image_mode_labels');
        if ($this->getOwner()->getIsFeatureImageMultisitesEnabled()) {
            if ($this->getOwner()->ParentID === $this->getOwner()->SiteID) {
                if (isset($options[self::MODE_PARENT])) {
                    unset($options[self::MODE_PARENT]);
                }
            } elseif ($this->getOwner()->ID === $this->getOwner()->SiteID) {
                if (isset($options[self::MODE_SITE])) {
                    unset($options[self::MODE_SITE]);
                }
                
                if (isset($options[self::MODE_PARENT])) {
                    unset($options[self::MODE_PARENT]);
                }
            }
        } elseif (!$this->getOwner()->ParentID) {
            if (isset($options[self::MODE_PARENT])) {
                unset($options[self::MODE_PARENT]);
            }
        }

        if ($this->getOwner()->hasMethod('updateFeatureImageModeOptions')) {
            return $this->getOwner()->updateFeatureImageModeOptions($options);
        }
        
        return $options;
    }

    public function getFeatureImageCMSFieldsArray()
    {
        $fields = [];

        $options = $this->getOwner()->getFeatureImageModeOptions();
        if (count($options) === 1) {
            $fields[] = HiddenField::create('FeatureImageMode', false);
        } elseif (count($options) > 1) {
            $fields[] = OptionsetField::create(
                'FeatureImageMode',
                $this->getOwner()->fieldLabel('FeatureImageMode'),
                $options
            );
        }
        
        if (isset($options[self::MODE_SELF])) {
            $imageWrapper = Wrapper::create(
                $imageField = UploadField::create(
                    'LocalFeatureImage',
                    $this->getOwner()->fieldLabel('FeatureImage')
                )
            );
            $folder = $this->getOwner()->config()->get('feature_image_upload_folder');
            if (!empty($folder)) {
                $imageField->setFolderName($folder);
            }

            $fields[] = $imageWrapper;
            if (count($options) > 1) {
                $imageWrapper
                    ->displayIf('FeatureImageMode')
                    ->isEqualTo(self::MODE_SELF);
            }
        }

        if ($this->getOwner()->hasMethod('updateFeatureImageCMSFieldsArray')) {
            return $this->getOwner()->updateFeatureImageCMSFieldsArray($fields);
        }
        
        return $fields;
    }

    public function onBeforeWrite(): void
    {
        if ($this->getOwner()->FeatureImageMode === $this->getOwner()->getDefaultFeatureImageMode()) {
            $this->getOwner()->FeatureImageMode = '';
        }
    }

    public function getIsFeatureImageMultisitesEnabled(): bool
    {
        $manifest = ModuleLoader::inst()->getManifest();
        return ($manifest->moduleExists('symbiote/silverstripe-multisites')
            || $manifest->moduleExists('fromholdio/silverstripe-configured-multisites'));
    }
}
