<?php
include(dirname(__FILE__).'/../../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../../init.php');

function adminer_object()
{
    // required to run any plugin
    include_once _PS_MODULE_DIR_.'debugtoolbar/tools/adminer/plugin.php';

    // autoloader
    include_once _PS_MODULE_DIR_.'debugtoolbar/tools/adminer/plugins/frames.php';

    $plugins = array(
        // specify enabled plugins here
        new AdminerFrames
    );

    /* It is possible to combine customization and plugins:
    class AdminerCustomization extends AdminerPlugin {
    }
    return new AdminerCustomization($plugins);
    */

    return new AdminerPlugin($plugins);
}
$adminer = include_once(_PS_MODULE_DIR_.'debugtoolbar/tools/adminer/adminer.php');
