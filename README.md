# osticket-mod-to-plugin

A plugin for [osTicket](https://github.com/osTicket/osTicket) that helps encapsulate older-style MOD's into containers of a Plugin. 

Meaning, you can export patch-set's of your existing MOD, (compared to core osTicket), save those patches, and replay them at will to install and reverse them to uninstall. From the Plugin Admin screen. ;-)

Also, if you are changing something simple, this plugin provides simplish find & replace semantics against core, again, with reversible-ness. And Autobackup. 

Might be useful, haven't tested it yet, but the idea seems right. 

Also, I want this to work, so I'll be testing it properly on Monday. 

## Basic uses (Examples included)
[MOD: Fix Scroll](https://github.com/clonemeagain/osticket-mod-to-plugin/blob/master/mods/fix_scroll.php): Stops the page from scrolling down when viewing tickets as agent.

## Advanced uses
[MOD: Invoice Number](https://github.com/clonemeagain/osticket-mod-to-plugin/blob/master/mods/add_invoice_number.php): Makes use of a patch saved in a sub-directory of the /mods/ folder, and replays against the core codebase. Installs Invoice Number methods and save/update code into class.tickets.php. Generally not a great idea, but as it's saved as a patch, it can be easily replayed against the code, until it changes drastically enough that the patch breaks, and then it won't work anyway. 

