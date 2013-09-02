<?php

if (defined('FILEMGR_MEMORY_LIMIT') && ini_get('memory_limit') < FILEMGR_MEMORY_LIMIT) ini_set('memory_limit', FILEMGR_MEMORY_LIMIT);


/** Copy directory tree
  * @param $srcdir Source Directory (currently works with directories only)
  * @param $dstdir Destination directory, created if it doesn't exist
  * @return number of copied files and folders
  * 
  * If the destination file exists, it is replaced only if the source file
  * has a later modification time. The function keeps the original
  * modification-time of files.
  * 
  * Example:
  * $num = dircopy('/data/massive/collection', '/where/it/should/be');
  */
function dircopy($srcdir, $dstdir) {
  $srcdir = or_die(realpath($srcdir), "Invalid source directory '$srcdir'");
    
  set_time_limit(0);
  $num = 0;
  if(!is_dir($dstdir)) {
      $success = mkdir($dstdir);
      if (!$success) {
          throw new Exception("Unable to create dir '$dstdir')");
      }
  }
  $dstdir = or_die(realpath($dstdir), "Invalid destination directory '$dstdir'");
  
  foreach(scandir($srcdir) as $file) {
      if($file != '.' && $file != '..') {
            $srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
            $dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;
            if(is_file($srcfile)) {
                if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
                if($ow > 0) {
                    if(copy($srcfile, $dstfile)) {
                    fix_file_permissions($dstfile);
                    touch($dstfile, filemtime($srcfile));
                    $num++;
                    } else {
                        throw new Exception("Unable to copy file '".basename($srcfile)."' ('$srcfile' to '$dstfile')");
                    }                  
                }
            } else if(is_dir($srcfile)) {
                if (strpos($dstfile, $srcfile) === 0) {
                    // Cowardly refusing to recursively copy stuff that
                    // is located in the destination
                    continue;
                }
                $num += dircopy($srcfile, $dstfile);
            }
      }
  }
  return $num;
}

/** Delete file or entire directory
  *
  * @param $file path to file or directory
  * @return true when everything was removed
  */
function rmall($file) {
    $path = realpath($file);
    if ($path === false) throw new Exception("Trying to remove nonexisting file '$file'");
    return rmall_r($path);
}

function rmall_r($file) {
    $success = true;
    if (filetype($file) == "dir") {
        $path = $file.DIRECTORY_SEPARATOR;
        foreach(scandir($file) as $contained) {
            if ($contained != "." && $contained != "..") {
                $success = $success && rmall_r($path.$contained);
            }
        }
        $success = $success && rmdir($file);
    } else {
        $success = $success && unlink($file);
    }
    return $success;
}

/** Ensure path has FILEBASEDIR as root.
  * @param $path Path to be checked
  * @return Sanitized path or false if the path does not exist or is not in FILEBASEDIR
  * If the path given as argument is relative (does not start with a slash) then FILEBASEDIR is prepended to it.
  */
function ensure_filebasedir_path($path) {
    $base =  realpath(FILEBASEDIR);
    if ($path[0] != '/') $path = $base.'/'.$path;
    $path = realpath($path);

    // Ensure $path is in FILEBASEDIR 
    if (strpos($path, $base) !== 0) return false;

    return $path;
}

/** Get an alphabetically sorted list of files in $directory.
  * 
  * @param $directory look for files in this directory
  * @param $filter optional, PREG filename filter
  * @return list of filenames, results are cached during the request.
  * 
  * The following things will be ignored:
  *   - Dot-files (those starting with a dot)
  *   - directories (not really, see below)
  *   - filenames starting with 'th_' or 'alt_' (legacy files)
  *   - files not matching the regexp in $filter (if given)
  * 
  * WARNING This function tries to be fast. It sacrifices accuracy (the ideal)
  * for speed (the drug). Because it is expensive to determine that a directory
  * entry is indeed a file and not a directory, a heuristic is employed. This
  * means that sometimes weird directory entries will be returned in the list.
  * 
  */
function listFiles($directory, $filter = '') {
    static $cache = array();
    $cache_key = $filter.$directory;
    if (!isset($cache[$cache_key])) {
        $files = array();
        $absolute_path = ensure_filebasedir_path($directory);
        if (!$absolute_path) throw new Exception("$directory is not in FILEBASEDIR, maybe you hava a stale dircache?");
        $dir = new DirectoryIterator($absolute_path);
        while($dir->valid()) {
            $file = $dir->getFilename();
            // We assume that directory entries having a standard filename extension (a dot as the fourth-last character) are files, not directories.
            // This heuristic saves syscalls but may wrongly list directories as files.
            if(  substr($file, 0, 1) != "."
                && (substr($file, -4, 1) == '.' || $dir->isFile()) // <-- heuristic
                && substr($file, 0, 3) != "th_"
                && substr($file, 0, 4) != "alt_"
                && ($filter == '' || preg_match("/".$filter."/i", $file))
            )
                $files[$file] = $file;
            $dir->next();
        }
        uksort($files, 'strnatcasecmp');
        $cache[$cache_key] = $files;
    }
    $files = $cache[$cache_key];
    reset($files);
    return $files;
}

/** Same as listFiles(), but returns FileInfo objects */
function listFileInfo($directory, $filter = '') {
    $files = listFiles($directory, $filter);
    $fileinfos = array();
    foreach($files as $file) $fileinfos[] = new FileInfo(ensure_filebasedir_path("$directory/$file"));
    return $fileinfos;
}

/** Get list of managed directories
  * @param $prefix restrict to dirs that start with this path */
function get_cached_dirs($prefix = false) {
    static $cached_dirs = array();
    if (isset($cached_dirs[$prefix])) return $cached_dirs[$prefix];
    
    $query_params = array();
    $where_prefix = '';
    if ($prefix) {
        $where_prefix = "WHERE path LIKE ?";
        $query_params []= $prefix.'%';
    }
    
    global $aquarius;
    $dirs = $aquarius->db->listquery("
        SELECT path
        FROM cache_dirs
        $where_prefix
        ORDER BY path
    ", $query_params);
    $cached_dirs[$prefix] = $dirs;
    return $dirs;
}

function getAllDirs() {
    $dirs = array();
    foreach ( split(";", FILE_ROOT_DIRS) as $rootDir ) {
        $dirs = array_merge(getAvailableDirectories($rootDir),$dirs);
    }
    return $dirs;
}

/** Get list of subdirectories
  * Hidden dirs (directories starting with a dot) are ignored. Directories in
  * the BLOCKED_DIRS list are ignored but their subdirs are included.
  * @param $directory path to the root directory
  * @return list of subdirectories of $directory (including $directory)
*/
function getAvailableDirectories($directory) {
    $blocked = false;
    foreach(explode(";", BLOCKED_DIRS) as $blocked_dir) {
        if (fnmatch($blocked_dir, $directory)) $blocked = true;
    }

    $dirs = array();
    if (!$blocked) $dirs []= $directory;
    $path =  FILEBASEDIR.$directory;
    $handle = opendir($path);
    if (!$handle) throw new Exception("Unable to open dir '$path'");


    $handle = opendir(FILEBASEDIR.$directory);
    while (false !== ($dir = readdir($handle))) {
        if(!is_dir(FILEBASEDIR.$directory.DIRECTORY_SEPARATOR.$dir) or $dir[0] == '.') continue;
        $dirs = array_merge($dirs, getAvailableDirectories($directory.DIRECTORY_SEPARATOR.$dir));
    }
    closedir($handle);
    return $dirs;
}


/** Compare the modification date of two files
  * For the purpose of this function, nonexisting files were last modified at the start of the UNIX epoch (so they're usually older than existing files).
  * @return The age difference in seconds (negative if file1 is older than file2) */
function file_compare_mtime($path1, $path2) {
    $age1 = 0;
    $age2 = 0;
    if (file_exists($path1)) $age1 = get(stat($path1), 'mtime');
    if (file_exists($path2)) $age2 = get(stat($path2), 'mtime');

    return $age1 - $age2;
}

function getFileButton($fileName) {
	$extension = strtolower(substr($fileName, -4));
	switch ($extension) {
		case '.pdf':
			return 'pdf.gif';
		case '.doc':
		case '.txt':
		case '.rtf':
			return 'doc.gif';
		case '.xls':
		case '.csv':
			return 'xls.gif';
		case '.mp3':
			return 'mp3.gif';			
		default:
			return 'file.gif';
	}
}


/** Get the type of a supported image file
  * Currently we support GIF, JPEG and PNG.
  * @param $image_path absolute file path to image
  * @return extension string or false if it isn't a supported image */
function image_type($image_path) {
    $size = @getimagesize($image_path);
    if ($size) {
        $type = $size[2];
        if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {
            return $type;
        }
    }
    return false;
}

/** Get file extension for supported image type.
  * Compatibilty for PHP versions < 5.2 which do not have image_type_to_extension(). Besides, image_type_to_extension() returns '.jpeg' for JPEG images, we want '.jpg' uniformly */
function image_type_to_extension_compat($type) {
    switch($type) {
        case IMAGETYPE_GIF:  return '.gif';
        case IMAGETYPE_JPEG: return '.jpg';
        case IMAGETYPE_PNG:  return '.png';
        default: throw new Exception("Don't know about type $type");
    }
}


/** Load image of all supported types.
  * @param $image_path absolute path to the image
  * @return Imagesize array and loaded image on success, false on failure */
function image_load($image_path) {
    $size = @getimagesize($image_path);
    $image = false;
    if ($size) {
        $type = $size[2];
        switch($type) {
            case IMAGETYPE_GIF:  $image = imagecreatefromgif($image_path); break;
            case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($image_path); break;
            case IMAGETYPE_PNG:  $image = imagecreatefrompng($image_path); break;
            default:             $image = false;
        }
    }
    if ($image) return compact('size', 'image', 'type');
    else return false;
}

/** Saves an image to a file based on its type.
  * @param $image Image to save
  * @param $type One of IMAGETYPE_GIF, IMAGETYPE_JPEG or IMAGETYPE_PNG
  * @return true on success */
function image_save($image, $type, $image_path) {
    switch($type) {
        case IMAGETYPE_GIF:  return imagegif($image, $image_path);
        case IMAGETYPE_JPEG: return imagejpeg($image, $image_path, QUALITY);
        case IMAGETYPE_PNG:  return imagepng($image, $image_path);
        default:             return false;
    }
}

/** Create downsized image for images bigger than $max_size.
  * @param $size image size info array
  * @param $image image resource to be resized
  * @param $resize_type either 'w', 'h' to limit the height or width; or 'm' to automatically choose the longer side
  * @param $max_size maximum length allowed, bigger images are resized to this size
  * @param $type target picture type, this is only important if you want to preserve transparency in PNG or GIF files. You'd pass in IMAGETYPE_GIF or IMAGETYPE_PNG
  * @return a resized copy of $image or false if $max_size was not transgressed */
function image_downsize($size, $image, $resize_type, $max_size, $type) {
    $new_size = false;
    if ($resize_type == 'm') $resize_type = ($size[0] > $size[1]) ? 'w' : 'h';
    if ($resize_type == 'w' && $size[0] > $max_size) $new_size = array($max_size, $size[1] * ($max_size / $size[0]));
    if ($resize_type == 'h' && $size[1] > $max_size) $new_size = array($size[0] * ($max_size / $size[1]), $max_size);

    // Stop if there's nothing to be done
    if (!$new_size) return false;

    // Downsize the image to new size
    $new_image = imagecreatetruecolor($new_size[0], $new_size[1]);

    // Preserve alpha channel for GIF and PNG
    if ($type == IMAGETYPE_GIF or $type == IMAGETYPE_PNG) {
        $transparent_color_index = imagecolortransparent($image);
        if ($transparent_color_index >= 0) {
            $transparent_color = imagecolorsforindex($image, $transparent_color_index);
            $new_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
            imagefill($new_image, 0, 0, $new_index);
            imagecolortransparent($new_image, $new_index);
        } else if ($type == IMAGETYPE_PNG) {
            // PNG without an alpha channel? Let's make one.
            imagealphablending($new_image, false);
            $transparent_color = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefill($new_image, 0, 0, $transparent_color);
            imagesavealpha($new_image, true);
        }
    }
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1]);
    return $new_image;
}

/** Update all thumbnails in a directory */
function update_directory_thumbnails($directory, $force=false) {
    set_time_limit(0);
    foreach (listFiles($directory) as $filename) {
        update_thumbnails($directory.'/'.$filename, $force);
    }
}

/** On some hosts (Genotec, I'm looking at you) files uploaded via HTTP/PHP are not visible to FTP. */
function fix_file_permissions($filepath) {
    global $aquarius;
    return chmod($filepath, $aquarius->conf('filemanager/upload_mode', 0777));
}

/** Generate thumbnails for an image.
  * Original picture is resized to max size specified in directory settings.
  * Both a thumbnail and an alt image are generated with prefixes 'th_' and 'alt_' respectively, sizes depend on the configured sizes in the directoy settings.
  * Thumbs are updated only if modification time is older than original image.
  * @param $image path to the file, relative from FILEBASEDIR
  * @param $force Whether to regenerate the thumbnails if they exist and are newer than the image
  * @return error code, error code is negative if file isn't an image in a supported format, 0 if the thumbs were generated successfully, >0 if there was an error. */
function update_thumbnails($image, $force=false) {
    $error = 0;
    
    // Ensure that the file exists
    $filepath = FILEBASEDIR.$image;
    if (!file_exists($filepath)) $error = 1;

    // Ensure it's a supported image type
    $type = false;
    if (!$error) {
        $type = image_type($filepath);
        if (!image_type($filepath)) $error = -1;
    }
    
    if (!$error) {
        // Load directory specific settings
        $settings = DB_DataObject::factory('directory_properties');
        $settings->load(dirname($image));

        // Load the image
        $loaded = image_load($filepath);
        if (!$loaded) $error = 1;
    }
    if (!$error) {
        $size = $loaded['size'];
        $type = $size[2];
        $image = $loaded['image'];

        // Downsize image to max_size
        if ($settings->max_size > 0) {
            $downsized = image_downsize($size, $image, $settings->resize_type, $settings->max_size, $type);

            // Overwrite original if it was resized
            if ($downsized) {
                image_save($downsized, $type, $filepath);
                imagedestroy($downsized);
            }
        }

        // Update alt and th image if a size is set
        foreach(array('th_', 'alt_') as $thumb) {
            $thumb_size = $settings->{$thumb.'size'};
            if ($thumb_size > 0) {
                $newpath = file_prefix($filepath, $thumb);

                // Update only if original's modification date is newer than alt mdate
                if ($force || file_compare_mtime($newpath, $filepath) < 0) {
                    $downsized = image_downsize($size, $image, $settings->resize_type, $thumb_size, $type);

                    // If the image was downsized, we write the resized copy, else we can use the original
                    if ($downsized) {
                        image_save($downsized, $type, $newpath);
                        imagedestroy($downsized);
                    } else {
                        copy($filepath, $newpath);
                    }
                }
            }
        }
        imagedestroy($image);
    }
    return $error;
}

/** Did you know that when the webserver hits the POST size limit, it
  * doesn't tell you, but just gives you an empty POST? Yeah, that's what
  * it does. This function just pukes an exception when it detects
  * this case.
  */
function check_for_failed_file_uploads() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!empty($_POST)) return;

    // So what's the POST size limit? We don't get a number of bytes, but a
    // string we have to parse. It's wrong we have to even check for this
    // condition like that, now we even have to parse for a size specifier,
    // seriously?
    $max_size_str = ini_get('post_max_size');
    $suffix = strtoupper(substr($max_size_str, -1));
    $u = 0;
    switch(strtoupper(substr($max_size_str, -1))) {
        case 'K': $u = 1024; break;
        case 'M': $u = 1024*1024; break;
        case 'G': $u = 1024*1024*1024; break;
        default:  $u = 1;
    }
    $max_size = 0;
    if ($u === 1) $max_size = (int)$max_size_str;
    else $max_size = (int)substr($max_size_str, 0, -1) * $u;

    $actual_size = $_SERVER['CONTENT_LENGTH'];
    if ($actual_size > $max_size) {
        throw new Exception("Upload size $actual_size is bigger than PHP's post_max_size setting ($max_size, $max_size_str).");
    }
}

/** Moves uploaded files to the target directory
  * Sanitizes filenames, initiates creation of thumb and alt images for pictures.
  * @param $upload_info Upload parameters from $_FILES
  * @param $target_directory where the file should be moved (path relative to FILEBASEDIR)
  * @param $unzip unzip file after upload in same directory(optional, default = false)
  * @param $custom_name optional name to use for file, instead of name of uploaded file (note that for images, the extension will be chosen regardless of the extension in custom_name)
  * @return new file name, error code and message, example: array('new_name'=>'bildli_1.jpg', 'error'=>UPLOAD_ERR_OK, 'message'=>array("s_upload_success", 'bildli_1.jpg')). Errors discovered during execution of this method get error code -1 */
function process_upload($upload_info, $target_directory,$unzip = false, $custom_name=false) {
    $message = false;
    $new_name = false;
    $is_image = false;
    $error = $upload_info['error'];
    switch($error) {
        case UPLOAD_ERR_OK:
            break; // Good
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $message = "s_upload_error_file_too_big";
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = "s_upload_error_invalid_upload";
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = false;
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            $message = "s_upload_error_cant_write";
            break;
        default:
            $message = "s_upload_error_unknown";
    }
    if (!$error) {
        // Extract the necessary values from upload info
        $tmp_name = get($upload_info, 'tmp_name');

        $name = $custom_name? $custom_name : get($upload_info, 'name');
        if (strlen($name) < 1) $name = $tmp_name; // Maybe client has not sent a name

        if($unzip) {
            $tmp_dir = $tmp_name."_zip/";
            if(!mkdir($tmp_dir,0777)) throw new Exception("Failed creating $tmp_dir");

            require_once('lib/pclzip.lib.php');
            $archive = new PclZip($tmp_name);
            /* FIXME: PathHandling */
            if($archive->extract(PCLZIP_OPT_PATH, $tmp_dir,
                                                    PCLZIP_OPT_REMOVE_ALL_PATH) == 0) {
                throw new Exception("Error extracting uploaded Zip-File: ".$archive->errorInfo(true));
            }

            if ($dh = opendir($tmp_dir)) {
                while (($file = readdir($dh)) !== false && !$error) {
                    if($file != "." && $file != "..") {
                        $result = move_upload($tmp_dir.$file,$file,$target_directory);
                        if($result['error']) {
                            $message = $result['message'];
                            $error = $result['error'];
                        }
                    }
                }
                closedir($dh);
            }
            rmdir($tmp_dir); // Can't rm a full dir, can you? What is this?
            if (!$error) $message = new Translation("s_zip_upload_success", array($name));
        }
        else {
            $result = move_upload($tmp_name,$name,$target_directory);
            if (!$result['error']) {
                fix_file_permissions($result['new_path']);
                $result['message'] = new Translation("s_upload_success", array($result['new_name']));
            }
            return $result;
        }
    }
    
    return compact('error', 'message');
}

function move_upload($tmp_name,$name,$target_directory) {
    $error = false;
    // See whether we have an image
    $image_type = image_type($tmp_name);
    $is_image = (bool)$image_type;
        
    $new_name = properName($name,$image_type);
        
    $target_path = ensure_filebasedir_path(FILEBASEDIR.$target_directory);
    if ($target_path === false) throw new Exception("Invalid target path $target_path");
    $new_path = $target_path.'/'.basename($new_name);
  
    Log::debug("Movin' $tmp_name to $new_path");
    $success = move_uploaded_file($tmp_name, $new_path);
    if (!$success) {
        $success = rename($tmp_name, $new_path);
    }
    if (!$success) {	
        $error = -1;
        $message = array('s_upload_error_failed_moving', $name, $new_path);
    }

    if (!$error) {
        global $aquarius;
        if ($is_image && $aquarius->conf('legacy/generate_thumbs')) update_thumbnails($target_directory.'/'.$new_name);
    }

    $result = compact('new_name', 'new_path', 'error', 'message');
    return $result;
}

function properName($name,$image_type) {
		// Sanitize filename by replacing special chars
        $new_name = convert_chars(basename($name)); // Sanitize filename

        // If it's an image, ensure correct extension
        if ($image_type) {
            $new_name = preg_replace('/\.[^.]*$/', '', $new_name); // Removes extension
            $new_name = $new_name.image_type_to_extension_compat($image_type);
        }

        // Hackedei: avoid prefixes we use for generated files
        $new_name = ereg_replace('^th_', 'th-', $new_name);
        $new_name = ereg_replace('^alt_', 'alt-', $new_name);
        
        return $new_name;
}

/** Find all references to a file
  * @param $file fileinfo object
  * @return list of content IDs that reference the file */
function references($file) {
    global $aquarius;

    /* Digging through all RTE fields to check for a filename is slow, we do it
     * for files in locations where the richtext is configured to take files. */
    $check_rte = false;
    $dir = dirname($file->publicpath());
    $rteconf = $aquarius->conf('admin/rte');
    foreach(array('browse_path_img', 'browse_path_file') as $config_name) {
        $rte_path = $rteconf[$config_name];
        if ($dir == $rte_path) {
            $check_rte = true;
            break;
        }
    }
    
    $relative_name = $file->publicpath();
    $query_params = array($relative_name, $relative_name);
    
    $check_rte_sql = '';
    if ($check_rte) {
        $check_rte_sql = " OR (
                form_field.type = 'rte'
            AND form_field.name = content_field.name
            AND content_field_value.value LIKE  ?
          )";
        $query_params []= '%'.$relative_name.'%';
    }

    // Find all content that references the file
    // The form_field that is joined in provides the base directory, the path must start with this
    // Note the "BINARY" to make comparison case-sensitive
    return $aquarius->db->listquery("
        SELECT DISTINCT content.id
        FROM form_field
        JOIN node ON form_field.form_id = node.cache_form_id
        JOIN content ON node.id = content.node_id
        JOIN content_field ON content.id = content_field.content_id
        JOIN content_field_value ON content_field.id = content_field_value.content_field_id
        WHERE (
                form_field.type = 'file'
            AND BINARY ? LIKE CONCAT(form_field.sup3, '%')
            AND form_field.name = content_field.name
            AND content_field_value.name = 'file'
            AND BINARY CONCAT(form_field.sup3, '/', content_field_value.value) = ?
          ) $check_rte_sql
        ORDER BY content.cache_title, content.lg
    ", $query_params);
}

class FileInfo {
    
    var $filepath;

    function __construct($filepath) {
        $this->filepath = $filepath;
    }

    /** Get Fileinfo for public path (relative to FILEBASEDIR)
      * @param $path path to the file or directory
      * @return FileInfo object or false if the file does not exist.
      */
    static function public_file($path) {
        $valid_path = realpath(FILEBASEDIR.$path);
        if ($valid_path) return new self($valid_path);
        else return false;
    }
    
    function name() {
        return basename($this->filepath);
    }

    function publicpath() {
        $prefix = FILEBASEDIR;
        if (substr($this->filepath, 0, strlen($prefix)) == $prefix) {
            return substr($this->filepath, strlen($prefix));
        } else {
            return false;
        }
    }
    
    function getStat($elem) {
        $stat = stat($this->filepath);
        return $stat[$elem];
    }
    
    function fileinfo() {
        $info = array();
        $info['file'] = $this;
        $info['name'] = $this->name();
        if (image_type($this->filepath)) {
            $info['type'] = 'image';
            $info['size'] = getimagesize($this->filepath);
        }
        elseif (eregi('\.swf$', $this->name()) ) {
            $info['type'] = 'flash';
        } else {
            $info['type'] = 'other';
        }
        $info['is_file'] = is_file($this->filepath);
        if ($info['is_file']) {
            $info['publicpath'] = '/'.$this->publicpath();
            $info['href'] = $this->href();
            $info['button'] = getFileButton($this->name());
        }
        return $info;
    }

    /** File size
      * @param $unit optional SI prefix of the desired unit. Currently supported are 'kB' for kilobytes and 'B' for bytes (default).  */
    function size($unit = 'B') {
        $size = $this->getStat('size');
        switch($unit) {
            case 'B': return $size;
            case 'kB': return ceil($size/1000);
            default: throw new Exception("Unsupported unit: '$unit'");
        }
    }
    
    function mtime() {
        return $this->getStat('mtime');
    }
    
    function is_dir() {
        return is_dir($this->filepath);
    }
    
    function suffix() {
        return array_pop(explode(".", $this->name()));
    }

    function href() {
        return PROJECT_URL.'/'.$this->publicpath();
    }
}
