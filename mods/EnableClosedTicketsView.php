<?php

/**
 * Fixes issue with Closed tickets being unavailable when the agent can only see "Those assigned"
 * 
 * Apparently by design, and I agree, the strictest definition of "Only see those assigned" does mean, that once a ticket is closed, it is no longer visible.
 * 
 * However, this should re-enable that view. 
 * 
 * Forum discussion:  http://osticket.com/forum/discussion/78517/wrong-behavior-if-limit-ticket-access-to-only-assigned-tickets-enabled
 * 
 * @author [fearless](http://osticket.com/forum/profile/135389/fearless)
 * 
 * Patch described in Forum post above, written against the develop branch
 * 
 * Modified slightly.. as I think we don't need to add any code where simply removing it works as well. 
 */
class EnableClosedTicketsView extends MOD {
	public function __construct() {
		// This changes a fair bit between versions.. so we do need to specify which ones we can support.
		$this->fixes [1.9] = new GitFix ( dirname ( __FILE__ ) . '/EnableClosedTicketsView.1.9.patch' );
	}
}