<?php
require_once (INCLUDE_DIR . 'class.plugin.php');
require_once ('config.php');
require_once ('MOD.php');

/**
 * Allows Mod's to be rewritten as Plugins.. hopefully. 
 * 
 * Also, more importantly, allows plugins to be enabled/disabled via Admin with checkboxes. 
 * 
 * I hope this works, it really depends on MOD's being written as PHP find & replace style things.
 * 
 * Should really use patches.. that would be good. 
 */
class ModToPlugin extends Plugin {
	var $config_class = 'ModToPluginConfig';
	function bootstrap() {
		// Do we need anything on BootStrap? Dunno.
		// This Plugin really only allows other things to edit the core
		// We really don't need menus or things do we?
		// maybe.. maybe we do..
		// Maybe a zesty admin interface "Which MOD's to install/uninstall" etc.
	}
	public static function getMods() {
		$mods = array ();
		// Find all [MOD]'s
		foreach ( dirname ( __FILE__ ) . '/mods/*.php' as $mod ) {
			include_once ($mod);
		}
		$classes = get_declared_classes ();
		foreach ( $classes as $class ) {
			if (is_subclass_of ( $class, 'MOD', TRUE )) {
				// Good enough!
				$mods [] = $class;
			}
		}
		return $mods;
	}
	
	/**
	 * Required stub.
	 *
	 * {@inheritDoc}
	 *
	 * @see Plugin::uninstall()
	 */
	function uninstall() {
		$errors = array ();
		// Let's undo all the mods.. if they want this gone, they must want it GONE
		foreach ( $this->getMods () as $mod ) {
			try {
				$m = new $mod ();
				$m->uninstall ();
			} catch ( Exception $e ) {
				$errors [] = $e->getMessage ();
			}
		}
		// Finally, run the default code
		parent::uninstall ( $errors );
	}
	function pre_uninstall(&$errors) {
		// again we need to check for web-process permissions.
		return $this->check_server_permissions ( $errors );
	}
	
	/**
	 * Required stub
	 */
	public function getForm() {
		return array ();
	}
	
	/**
	 * For most of this stuff, we need to know that the web-process has
	 * write access to the code.
	 */
	function pre_save($config, &$errors) {
		$this->check_server_permissions ( $errors );
		$config = $this->getConfig (); // Might have to be getForm()
		$mods = $this->getMods ();
		// Let's initiate the installer/uninstaller for each.
		foreach ( $mods as $mod ) {
			$mod = new $mod ();
			if ($config->get ( 'mod-enabled-' . $mod )) {
				$mod->install ();
			} else {
				$mod->uninstall ();
			}
		}
	}
	private function check_server_permissions(&$errors) {
		if (! is_writable ( __THIS__ )) {
			$errors [get_class ( $this )] = 'Unable to write to files.. Check your web-process\'s permissions.';
		}
	}
}
