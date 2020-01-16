<?php
declare(strict_types=1);

namespace Muffin\Sti;

use Cake\Core\BasePlugin;

/**
 * Class Plugin
 */
class Plugin extends BasePlugin
{
    /**
     * The name of this plugin
     *
     * @var string
     */
    protected $name = 'Sti';

    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected $bootstrapEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected $routesEnabled = false;

    /**
     * Console middleware
     *
     * @var bool
     */
    protected $consoleEnabled = false;
}
