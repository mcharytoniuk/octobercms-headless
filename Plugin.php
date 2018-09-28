<?php

namespace Newride\Headless;

use Newride\Headless\Components\StaticContent;
use System\Classes\PluginBase;

/**
 * HeadlessCMS Plugin Information File.
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'HeadlessCMS',
            'description' => 'Edit website content.',
            'author' => 'Newride',
            'icon' => 'icon-leaf',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            StaticContent::class => 'staticContent',
        ];
    }
}
