<?php
/**
 * Interface for a test service for error handling testing
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Mage_TestModule3_Service_ErrorInterfaceV1
{
    public function success($request);
    public function resourceNotFoundException($request);
    public function serviceException($request);
    public function parameterizedException($request);
    public function authorizationException($request);
    public function otherException($request);
}
