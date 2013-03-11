<?php 

class FileBrowser {

	// Where in the filesystem the FileBrowser is rooted
	private $base_path;
	
	// Path of the current directory, relative to base_path.
	private $current_path;
	
	public $sort_order;
	
	public static $FilenameOrder;
	public static $SizeOrder;
	public static $TimeOrder;
	
	public static $DirectoryFilter;
	public static $FileFilter;
	public static $DotFilter;
	
	function __construct($base_path) {
		// Initialize order functions and filters (dunno how to create them statically in PHP)
		self::$FilenameOrder = create_function('$fileinfo1, $fileinfo2', 'return strcmp($fileinfo1->name(), $fileinfo2->name());');
		self::$SizeOrder = create_function('$fileinfo1, $fileinfo2', 'return $fileinfo1->size()-$fileinfo2->size();');
		self::$TimeOrder = create_function('$fileinfo1, $fileinfo2', 'return $fileinfo1->mtime()-$fileinfo2->mtime();');
		
		self::$DirectoryFilter = create_function('$fileinfo', 'return $fileinfo->is_dir();');
		self::$FileFilter = create_function('$fileinfo', 'return !$fileinfo->is_dir();');
		self::$DotFilter = self::makeFilter("^\.");
	
		$this->base_path = realpath($base_path);
		$this->current_path = '/';
		$this->sort_order = self::$FilenameOrder;
	}
	
	public static function makeFilter($pattern) {
		return create_function('$fileinfo', 'return FALSE == ereg("'.addslashes($pattern).'", $fileinfo->name());');
	}
	
	function authorize() {
		global $_SESSION;
		$_SESSION["filebrowser_auth$this->base_path"] = true; 
	}

	function authorized() {
		global $_SESSION;
		return $_SESSION["filebrowser_auth$this->base_path"] == true; 	
	}
	
	function absolute_path() {
		return $this->base_path.$this->current_path;
	}
	
	function relative_path() {
		return $this->current_path;
	}
	
	function pwd() {
		return realpath($this->absolute_path());
	}
	
	function cd($relative_path) {
		// Check whether it's a valid path and lies within base_path
		$real_path = realpath($this->absolute_path().$relative_path);
		if ($real_path && is_dir($real_path) && $this->base_path == substr($real_path, 0, strlen($this->base_path)))
			$this->current_path = substr($real_path, strlen($this->base_path), strlen($real_path))."/";
		else
			throw new Exception("Invalid directory '$relative_path'.");
			
	}
	
	function ls($filters = false) {
		$entries = array();
		
		$dir_handle = @opendir($this->absolute_path());
		if (!$dir_handle)
			throw new Exception("Could not open ".$this->absolute_path());
		while ($filename = readdir($dir_handle))
			$entries[] = new FileInfo($this->absolute_path().$filename);	
		closedir($dir_handle);
			
		if ($filters)
			foreach($filters as $filter)
				$entries = array_filter($entries, $filter);	
				
		usort($entries, $this->sort_order);
		
		return $entries;
	}
}

class FileInfo {
	
	var $filepath;

	function __construct($filepath) {
		$this->filepath = $filepath;
	}
	
	function name() {
		return basename($this->filepath);
	}
	
	function getStat($elem) {
		$stat = stat($this->filepath);
		return $stat[$elem];
	}
	
	function size() {
		return $this->getStat('size');
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
}

?>