<?php
/**
 * Adds an Invoice Number to tickets.
 * 
 * Did you already modify your pre-1.8 version of osTickets?
 * Want to get your Invoice Number field back without having to fuck around? 
 * This could be the [MOD]/Plugin for you!
 * 
 * If your changes are a bit.. complex.
 * Then really, just edit teh fuck out of a test version, 
 * make a commit, with your message, so you can read about it in your patchset
 * compile a patch using `git format-patch -1 HEAD`, and save the patches to a subfolder of here.
 * This class simply globs all patch files in invoce_patches folder, and applies them.
 * 
 */
class InvoiceNumber extends MOD {
	public function __construct() {
		$this->versions = array (
				1.7,
				1.8,
				1.9
		);
		foreach ( glob ( dirname ( __FILE__ ) . '/invoice_patches/*.patch' ) as $patch ) {
			$this->fixes [] = new GitFix ( $patch );
		}
	}
}
