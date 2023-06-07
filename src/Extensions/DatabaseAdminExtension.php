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
                        echo sprintf(" * %s stage images migrated\n", $count);
                    } else {
                        echo sprintf("<p>%s stage images migrated</p>\n", $count);
                    }
                }
            } else {
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo " * no stage images needed migration\n";
                    } else {
                        echo "<p>No stage images needed migration</p>\n";
                    }
                }
            }
            // remove old column
            $query = "ALTER TABLE $table DROP COLUMN FeatureImageID";
            DB::query($query);
        }

        // live
        $table = Page::config()->get('table_name') . '_Live';
        $query = "SHOW COLUMNS FROM $table WHERE FIELD = 'FeatureImageID' OR FIELD = 'LocalFeatureImageID'";
        $count = DB::query($query)->numRecords();
        if ($count == 2) { // both fields exist
            // find records that need migrating
            $query = "SELECT * from $table WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
            $count = DB::query($query)->numRecords();
            if ($count > 0) {
                // migrate fields
                $query = "UPDATE $table set LocalFeatureImageID = FeatureImageID WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
                DB::query($query);
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo sprintf(" * %s live images migrated\n", $count);
                    } else {
                        echo sprintf("<p>%s live images migrated</p>\n", $count);
                    }
                }
            } else {
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo " * no live images needed migration\n";
                    } else {
                        echo "<p>No live images needed migration</p>\n";
                    }
                }
            }
            // remove old column
            $query = "ALTER TABLE $table DROP COLUMN FeatureImageID";
            DB::query($query);
        }

        // versions
        $table = Page::config()->get('table_name') . '_Versions';
        $query = "SHOW COLUMNS FROM $table WHERE FIELD = 'FeatureImageID' OR FIELD = 'LocalFeatureImageID'";
        $count = DB::query($query)->numRecords();
        if ($count == 2) { // both fields exist
            // find records that need migrating
            $query = "SELECT * from $table WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
            $count = DB::query($query)->numRecords();
            if ($count > 0) {
                // migrate fields
                $query = "UPDATE $table set LocalFeatureImageID = FeatureImageID WHERE FeatureImageID > 0 AND LocalFeatureImageID = 0";
                DB::query($query);
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo sprintf(" * %s versions images migrated\n", $count);
                    } else {
                        echo sprintf("<p>%s versions images migrated</p>\n", $count);
                    }
                }
            } else {
                if (!$quiet) {
                    if (Director::is_cli()) {
                        echo " * no versions images needed migration\n";
                    } else {
                        echo "<p>No versions images needed migration</p>\n";
                    }
                }
            }
            // remove old column
            $query = "ALTER TABLE $table DROP COLUMN FeatureImageID";
            DB::query($query);
        }
    }
}
