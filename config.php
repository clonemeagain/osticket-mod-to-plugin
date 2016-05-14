<?php
require_once INCLUDE_DIR . 'class.plugin.php';
class ModToPluginConfig extends PluginConfig {
	// Provide compatibility function for versions of osTicket prior to
	// translation support (v1.9.4)
	function translate() {
		if (! method_exists ( 'Plugin', 'translate' )) {
			return array (
					function ($x) {
						return $x;
					},
					function ($x, $y, $n) {
						return $n != 1 ? $y : $x;
					} 
			);
		}
		return Plugin::translate ( 'mod-to-plugin' );
	}
	
	/**
	 * Build an Admin settings page.
	 *
	 * {@inheritDoc}
	 *
	 * @see PluginConfig::getOptions()
	 */
	function getOptions() {
		$mods = ModToPlugin::getMods ();
		list ( $__, $_N ) = self::translate ();
		$confs = array (
				'mod-to-plugin' => new SectionBreakField ( array (
						'label' => $__ ( '[MOD] To Plugin Builder' ),
						'description' => $__ ( 'The plugin that helps you build plugins from your mods!. Simply add your mod class in ' . dirname(__THIS__) . '/mods' ) 
				) ) 
		);
		foreach ( $mods as $mod ) {
			$confs ['mod-enabled-' . $mod] = new BooleanField ( array (
					'label' => $__ ( 'Enable [MOD]' ) . $mod,
					'hint' => $__ ( "If you disable this [MOD] it will revert all it's changes." ) 
			) );
		}
	}
}
