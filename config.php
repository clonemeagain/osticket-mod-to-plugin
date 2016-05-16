<?php
require_once INCLUDE_DIR . 'class.plugin.php';

class ModToPluginConfig extends PluginConfig
{
    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate()
    {
        if (! method_exists('Plugin', 'translate')) {
            return array(
                function ($x) {
                    return $x;
                },
                function ($x, $y, $n) {
                    return $n != 1 ? $y : $x;
                }
            );
        }
        return Plugin::translate('mod-to-plugin');
    }

    /**
     * Build an Admin settings page.
     *
     * {@inheritDoc}
     *
     * @see PluginConfig::getOptions()
     */
    function getOptions()
    {
        $mods = ModToPlugin::getMods();
        $confs = array(
            'mod-to-plugin' => new SectionBreakField(array(
                'label' => '[MOD] To Plugin Builder:  Simply add your mod class in ' . dirname(__THIS__) . '/mods. Ensure your webserver can write to all files before installing/uninstalling a mod. You can return the permissions to their securer form afterwards.'
            )),
            'mod-purge-on-uninstall' => new BooleanField(array('label' => 'Uninstall all MOD\'s if this Plugin removed?')),
        );
        foreach ($mods as $mod) {
            $conf_name = 'mod-enabled-' . $mod;
            $desc = isset($mod::$description) ? $mod::$description : 'Custom [MOD], disabling it should revert all changes.';
            $confs[$conf_name] = new BooleanField(array(
                'label' => $mod,
                'hint' => $desc
            ));
        }
        return $confs;
    }

    /**
     * For most of this stuff, we need to know that the web-process has
     * write access to the code.
     */
    function pre_save($config, &$errors)
    {
        $form = $this->getForm();
        $mods = ModToPlugin::getMods();
        // Let's initiate the installer/uninstaller for each.
        foreach ($mods as $mod) {
            $conf_name = 'mod-enabled-' . $mod;

            // Create the mod, it should only ever be created here, we're not creating classes on bootstrap()
            // This should be the one and only time the mod get's instantiated, we let it do it's work, then wait
            // for it to be uninstalled..
            try {
                $obj = new $mod();
                if (isset($form->fields[$conf_name]) && $form->fields[$conf_name] == 1) {
                    if (isset($config[$conf_name]) && $config[$conf_name]) {
                        // Already installed. no-op
                        continue;
                    } elseif (! $obj->install()) {
                        $errors[] = "$mod -> Failed to install.";
                    }
                } elseif (! $obj->uninstall()) {
                    $errors[] = "$mod -> Failed to uninstall.";
                }
            } Catch (\Exception $e) {
                $errors[] = $e->getMessage();
                return FALSE;
            }
        }
    }
}
