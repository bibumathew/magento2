<?php
/**
 * Tests fixture for Auto Discovery functionality.
 *
 * Fake resource controller.
 *
 * @copyright {}
 */
class Vendor_Module_Webapi_ResourceController
{
    /**
     * @param Vendor_Module_Webapi_Customer_DataStructure[] $resourceData
     * @param bool $requiredField
     * @param string $optionalField
     * @param int $secondOptional
     * @return Vendor_Module_Webapi_Customer_DataStructure
     */
    public function createV1($resourceData, $requiredField, $optionalField = 'optionalField', $secondOptional = 1)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $resourceId
     * @param Vendor_Module_Webapi_Customer_DataStructure $resourceData
     * @param float $additionalRequired
     */
    public function updateV2($resourceId, $resourceData, $additionalRequired)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $resourceId
     * @return Vendor_Module_Webapi_Customer_DataStructure
     */
    public function getV2($resourceId)
    {
        // Body is intentionally omitted
    }

    /**
     * @param float $additionalRequired
     * @param bool $optional
     * @return Vendor_Module_Webapi_Customer_DataStructure[]
     */
    public function listV2($additionalRequired, $optional = false)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $resourceId
     */
    public function deleteV3($resourceId)
    {
        // Body is intentionally omitted
    }

    /**
     * @param Vendor_Module_Webapi_Customer_DataStructure[] $resourceData
     */
    public function multiUpdateV2($resourceData)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int[] $idsToBeRemoved
     */
    public function multiDeleteV2($idsToBeRemoved)
    {
        // Body is intentionally omitted
    }
}
