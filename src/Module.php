<?php
/**
 * @link      http://github.com/zendframework/zend-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n;

class Module
{
    /**
     * Return zend-i18n configuration for zend-mvc application.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'filters'         => $provider->getFilterConfig(),
            'service_manager' => $provider->getDependencyConfig(),
            'validators'      => $provider->getValidatorConfig(),
            'view_helpers'    => $provider->getViewHelperConfig(),
        ];
    }
}
