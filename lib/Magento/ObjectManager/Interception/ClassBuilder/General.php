<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class Magento_ObjectManager_Interception_ClassBuilder_General
    implements Magento_ObjectManager_Interception_ClassBuilder
{

    /**
     * Compose interceptor class name for the given class
     *
     * @param string $originalClassName
     * @return string
     */
    public function composeInterceptorClassName($originalClassName)
    {
        return $originalClassName . '_Interceptor';
    }
}
