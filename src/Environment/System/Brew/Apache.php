<?php

namespace Cdev\Local\Environment\System\Brew;

use Creode\System\Command;
use Cdev\Local\Environment\System\Helpers\ApacheHelper;
use Cdev\Local\Environment\System\Config\ConfigHelper;

class Apache extends Command {
    const COMMAND = 'apachectl';

    /**
     * Apache Helper.
     *
     * @var \Cdev\Local\Environment\System\Helpers\ApacheHelper
     */
    private $_apache;

    /**
     * Config Helper.
     *
     * @var \Cdev\Local\Environment\System\Config\ConfigHelper
     */
    private $_configHelper;

    /**
     * Constructor.
     *
     * @param ApacheHelper $apache
     * @param ConfigHelper $configHelper
     */
    public function __construct($apache, $configHelper) {
        $this->_apache = $apache;
        $this->_configHelper = $configHelper;
    }

    /**
     * Initialises the Apache Setup (create hosts).
     */
    private function initialise($config) {
        if (!$this->_apache->meetsDependencies()) {
            throw new \Exception("Ensure the following Apache modules are installed and loaded:\n " . implode("\n ", ApacheHelper::MODULE_DEPENDENCIES));
        }

        $hostname = $this->_configHelper->getHostname($config);
        $path = $this->_configHelper->getSitePath($config);

        // Check if host exists.
        if (!$this->_apache->siteConfigExists($path)) {
            echo 'Adding a new configuration for `'. $hostname . '` inside `' . $this->_apache->configPath . '`.';
            $this->_apache->addHost($hostname, $path, $config);
        }
    }

    /**
     * Starts up an Apache Server.
     *
     * @param string $path
     * @param Creode\Cdev\Config $config
     * @return void
     */
    public function start($path, $config) {
        $this->initialise($config);
        $this->runExternalCommand('sudo ' . $this::COMMAND, ['-k', 'start'], $path);
    }

    /**
     * Stops an Apache Server.
     *
     * @param string $path
     * @return void
     */
    public function stop($config)
    {
        $this->_apache->removeHost($this->_configHelper->getHostname($config));
    }

    public function nuke($config) {
        $this->stop($config);
    }
}