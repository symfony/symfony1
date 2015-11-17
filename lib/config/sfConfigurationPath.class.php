<?php

/**
 *
 * @author      Bruno Escudeiro <bruno.escudeiro@smark.io>
 * @copyright   2015 Smarkio
 * @project     [SMARKIO_URL_LICENSE_HERE]
 *
 * [SMARKIO_DISCLAIMER]
 */
interface sfConfigurationPath
{
    /**
     * Gets the configuration file paths for a given relative configuration path.
     *
     * @param string $configPath The configuration path
     *
     * @return array An array of paths
     */
    public function getConfigPaths($configPath);
}
