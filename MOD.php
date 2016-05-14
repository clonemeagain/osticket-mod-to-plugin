<?php
/** To build a MOD into a Plugin
 Simply add a .php file into /mods from here,
 extend the class MOD and fill in those two functions.
 Assumes webserver has chmod o+rw -R /osticket
 
 For instance, check out mods/fix_scroll.php
 You can add other files, with other classes if you need.. We only use
 subclasses of MOD for the install/uninstall functions.
 */
abstract class MOD {
	/**
	 * An array of Fix objects.
	 *
	 * @var array[Fix]
	 */
	protected $fixes;
	abstract public function install();
	abstract public function uninstall();
	
	/**
	 * Applies a fix
	 */
	protected function apply() {
		foreach ( $this->fixes as $fix ) {
			$fix->apply ();
		}
	}
	
	/**
	 * Applies the reverse operation to the fix.
	 */
	protected function undo() {
		foreach ( $this->fixes as $fix ) {
			$this->undo ();
		}
	}
}
/**
 * A simplified way of finding and replacing without all the boilerplate.
 * Automatically runs apply/undo when Site Admin turns the Plugin/Mod on or off
 *
 * Used in class MOD
 */
abstract class AbstractFix {
	var $file;
	var $find;
	var $replace;
	var $method;
	/**
	 * Utility function
	 * Fetches the contents of a file relative to ROOT_DIR
	 *
	 * @param string $name
	 *        	of the file
	 * @return string the contents of the file.
	 */
	public function read($name) {
		if (! is_readable ( ROOT_DIR . $name )) {
			throw new \Exception ( "Unable to read $name." );
		}
		return file_get_contents ( ROOT_DIR . $name );
	}
	/**
	 * Utility function
	 * Overwrites a file with your new contents.
	 *
	 * @param string $file
	 *        	the name of the file.
	 * @param string $contents
	 *        	of the file
	 * @return the number of bytes written.
	 */
	public function write($file, $contents) {
		if (! is_writable ( ROOT_DIR . $file )) {
			throw new \Exception ( "Unable to edit $file, can't write to the file." );
		}
		return file_put_contents ( ROOT_DIR . $file, $contents );
	}
	
	/**
	 * Called by ModToPlugin on saving the Admin Config
	 */
	public function apply() {
		// First, let's get the file in question.
		try {
			$file = $this->read ( $this->file );
			// Make umask MOAR SECURE
			$backup = dirname ( __THIS__ ) . dirname ( $this->file ) . DIRECTORY_SEPARATOR . $this->file;
			if (file_exists ( $backup )) {
				// We've already applied a fix to this! backup is the original file.. don't change again!
				// Could be an issue with updates.. hmm.
			} else {
				$original_mask = umask ( '0077' ); // set -rw-------
				if (! is_dir ( dirname ( backup ) ) && ! mkdir ( dirname ( $backup ), 0777, TRUE )) {
					// We couldn't make the backup folder. :-( Bleat?
				} else {
					chmod ( dirname ( $backup ), '0755' ); // set -rwx-rx-rx
					file_put_contents ( $backup, $file );
					chmod ( $backup, '0644' ); // / set -rw-r--r--
				}
			}
			
			// Perform the repair
			$fixed = $this->method ( $this->find, $this->replace, $file );
			// Write it back out
			if ($this->write ( $this->file, $fixed )) {
				return true;
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			global $ost;
			$ost->logError ( "MOD FixScroll Install Error", $e->getMessage () );
		}
	}
	
	/**
	 * Called by ModToPlugin on saving the Admin Config
	 */
	public function undo() {
		// We need to return the line..
		try {
			// First, let's get the file in question.
			$file = $this->read ( $this->file );
			// Undo our fix
			$fixed = $this->method ( $this->replace, $this->find, $file );
			// Write it back out
			$this->write ( $this->file, $fixed );
		} catch ( Exception $e ) {
			global $ost;
			$ost->logError ( "[MOD] UnInstall Error", $e->getMessage () );
		}
	}
}
/**
 * Perform a str_replace style fix
 *
 * @author Grizly
 *        
 */
class StrFix {
	var $method = 'str_replace';
}
/**
 * Perform a preg_replace style fix
 *
 * @author Grizly
 *        
 */
class RegexFix extends Fix {
	var $method = 'preg_replace';
}

/**
 * Perform a git apply using a patch file.
 *
 *
 *
 * @author Owner
 *        
 */
class GitFix extends Fix {
	var $patch;
	var $method = 'git';
	
	public function __construct($patch_file){
		$this->patch = $patch_file;
	}
	
	/**
	 * Called by ModToPlugin on saving the Admin Config
	 */
	public function apply() {
		try {
			$git = exec ( "which git" );
			if (file_exists ( $this->patch )) {
				chdir ( ROOT_DIR );
				$message = exec ( "$git apply {$this->patch}" );
			}
		} catch ( Exception $e ) {
			global $ost;
			$ost->logError ( "MOD Install Error", $e->getMessage () );
		}
	}
	
	/**
	 * Called by ModToPlugin on saving the Admin Config
	 */
	public function undo() {
		try {
			$git = exec ( "which git" );
			if (file_exists ( $this->patch )) {
				chdir ( ROOT_DIR );
				$message = exec ( "$git apply -R {$this->patch}" );
			}
		} catch ( Exception $e ) {
			global $ost;
			$ost->logError ( "MOD UnInstall Error", $e->getMessage () );
		}
	}
}