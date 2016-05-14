<?php
/**
 * Fixes that annoying scroll setting for Agents.
 * Some things can't be fixed by javascript or editing the HTML afterwards.
 * Note, you don't have to use StrFix() objects to do this, you can write your own changes!
 * Just override the apply & undo functions and put whatever you like in them!
 */
class FixScroll extends MOD {
	public function __construct() {
		
		// Which version of code do we support?
		$this->versions = array (
				1.6,
				1.7,
				1.8,
				1.9 
		);
		
		// Build our fix.
		$fix = new StrFix ();
		$fix->file = '/scp/js/scp.js';
		$fix->find = '$("input:not(.dp):visible:enabled:first").focus();';
		$fix->replace = '// ' . $this->find;
		
		// Persist the fix.
		$this->fixes [] = $fix;
	}
}
