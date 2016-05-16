<?php
require_once (INCLUDE_DIR . 'class.plugin.php');
require_once ('config.php');
require_once ('MOD.php');

/**
 * Allows Mod's to be rewritten as Plugins..
 * hopefully.
 *
 * Also, more importantly, allows plugins to be enabled/disabled via Admin with checkboxes.
 *
 * I hope this works, it really depends on MOD's being written as PHP find & replace style things.
 *
 * Should really use patches.. that would be good.
 */
class ModToPlugin extends Plugin
{

    var $config_class = 'ModToPluginConfig';

    function bootstrap()
    {
        // Do we need anything on BootStrap? Dunno.
        // This Plugin really only allows other things to edit the core
        // We really don't need menus or things do we?
        // maybe.. maybe we do..
        // Maybe a zesty admin interface "Which MOD's to install/uninstall" etc.
    }

    public static function getMods()
    {
        $mods = array();
        // Find all [MOD]'s
        foreach (glob(dirname(__FILE__) . '/mods/*.php') as $mod) {
            include_once ($mod);
        }
        $classes = get_declared_classes();
        foreach ($classes as $class) {
            if (is_subclass_of($class, 'MOD', TRUE)) {
                // Good enough!
                $mods[] = $class;
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
    function uninstall()
    {
        $errors = array();

        if ($this->getConfig()->get('mod-purge-on-uninstall')) {
            // Let's undo all the mods.. if they want this gone, they must want it GONE
            foreach ($this->getMods() as $mod) {
                try {
                    $m = new $mod();
                    $m->uninstall();
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        // Finally, run the default code
        parent::uninstall($errors);
    }

    /**
     * Required stub
     */
    public function getForm()
    {
        return array();
    }

    /**
     * Verifies that the file exists and can be written to..
     * Best to use before you try something.
     *
     * @param string $file
     * @throws \Exception on non-existance or non-writableness.
     */
    public static function checkPermissions($file)
    {
        if (! file_exists($file) || ! is_writable($file)) {
            $perms = fileperms($file) & 0777;
            $perms .= ' They should be 640, or 644 etc, basically, your webserver needs to be able to write to the files for this to work.';
            throw new \Exception('Unable to write to file ' . $file . ', Check your web-process\'s permissions. Current permissions: ' . $perms);
        }
    }
}
