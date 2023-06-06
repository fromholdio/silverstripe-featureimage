<?php

namespace Fromholdio\FeatureImage\Extensions;

use Page;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DB;

class DatabaseAdminExtension extends Extension
{
    public function onAfterBuild($quiet, $populate, $testMode)
    {
        // migrates has_one FeatureImage to LocalFeatureImage
        $table = Page::config()->get('table_name');
        $query = "SHOW COLUMNS FROM $table WHERE FIELD = 'FeatureImageID' OR FIELD = 'LocalFeatureImageID'";
        $count = DB::query($query)->numRecords();
        if ($count == 2) { // both fields exist
            if (!$quiet) {
                if (Director::is_cli()) {
                    echo "\nMIGRATING FEATURE IMAGES\n\n";
                } else {
                    echo "\n<p><b>Migrating Feature Images</b></p>\n\n";
                }
            }
            // find records that need migrating
            $query = "SELECT * from $table WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
            $count = DB::query($query)->numRecords();
            if ($count > 0) {
                // migrate fields
                $query = "UPDATE $table set LocalFeatureImageID = FeatureImageID WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
                DB::query($query);
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo sprintf(" * %s images migrated\n", $count);
                    } else {
                        echo sprintf("<p>%s images migrated</p>\n", $count);
                    }
                }
            } else {
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo " * no images needed migration\n";
                    } else {
                        echo "<p>No images needed migration</p>\n";
                    }
                }
            }
            // remove old column
            $query = "ALTER TABLE $table DROP COLUMN FeatureImageID";
            DB::query($query);
        }
    }
}
