<?php
/**
 * Configuration file for SLIR (Smart Lencioni Image Resizer)
 *
 * This file is part of SLIR (Smart Lencioni Image Resizer).
 *
 * SLIR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SLIR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SLIR.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright © 2011, Joe Lencioni
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 * @since 2.0
 * @package SLIR
 */

require_once 'core/slirconfigdefaults.class.php';


class SLIRConfig extends SLIRConfigDefaults {
    public static $enableErrorImages  = false;

    /** Crop towards 'interesting' areas in pictures */
    public static $defaultCropper = SLIR::CROP_CLASS_SMART;
    
    public static $defaultQuality = 95;
    
    public static function init() {
        if (self::$documentRoot === null) {
            self::$documentRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        if (self::$pathToCacheDir === null) {
            self::$pathToCacheDir = self::$documentRoot . '/aquarius/cache/slir';
        }

        if (self::$pathToErrorLog === null) {
        self::$pathToErrorLog = self::$pathToCacheDir . '/error-log.txt';
        }
        parent::init();
    }
    
}

SLIRConfig::init();