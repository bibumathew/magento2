<?php
/**
 * Magento-specific SOAP fault.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Model_Soap_Fault extends RuntimeException
{
    const FAULT_REASON_INTERNAL = 'Internal Error.';

    /**#@+
     * Fault codes that are used in SOAP faults.
     */
    const FAULT_CODE_SENDER = 'Sender';
    const FAULT_CODE_RECEIVER = 'Receiver';

    /**#@+
     * Nodes that can appear in Detail node of SOAP fault.
     */
    const DETAIL_ERROR_CODE = 'ErrorCode';
    const DETAIL_PARAMETERS = 'Parameters';
    const DETAIL_EXCEPTION_TRACE = 'ExceptionTrace';
    /**#@-*/

    /** @var string */
    protected $_soapFaultCode;

    /** @var string */
    protected $_errorCode;

    /** @var array */
    protected $_parameters;

    /**
     * Details that are used to generate 'Detail' node of SoapFault.
     *
     * @var array
     */
    protected $_details = array();

    /**
     * Construct exception.
     *
     * @param string $reason
     * @param string $faultCode
     * @param Exception $previous
     * @param array $parameters
     * @param string|null $errorCode
     */
    public function __construct(
        $reason = self::FAULT_REASON_INTERNAL,
        $faultCode = self::FAULT_CODE_RECEIVER,
        Exception $previous = null,
        $parameters = array(),
        $errorCode = null
    ) {
        parent::__construct($reason, 0, $previous);
        $this->_soapCode = $faultCode;
        $this->_parameters = $parameters;
        $this->_errorCode = $errorCode;
    }

    /**
     * Render exception as XML.
     *
     * @param $isDeveloperMode
     * @return string
     */
    public function toXml($isDeveloperMode = false)
    {
        if ($isDeveloperMode) {
            $this->addDetails(array(self::DETAIL_EXCEPTION_TRACE => "<![CDATA[{$this->getTraceAsString()}]]>"));
        }
        if ($this->getParameters()) {
            $this->addDetails(array(self::DETAIL_PARAMETERS => $this->getParameters()));
        }
        if ($this->getErrorCode()) {
            $this->addDetails(array(self::DETAIL_ERROR_CODE => $this->getErrorCode()));
        }

        // TODO: Implement Current language definition
        $language = 'en';
        return $this->getSoapFaultMessage($this->getMessage(), $this->getSoapCode(), $language, $this->getDetails());
    }

    /**
     * Retrieve additional details about current fault.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Retrieve error code.
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Add details about current fault.
     *
     * @param array $details Associative array containing details about current fault
     * @return Mage_Webapi_Model_Soap_Fault
     */
    public function addDetails($details)
    {
        $this->_details = array_merge($this->_details, $details);
        return $this;
    }

    /**
     * Retrieve additional details about current fault.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * Retrieve SOAP fault code.
     *
     * @return string
     */
    public function getSoapCode()
    {
        return $this->_soapCode;
    }

    /**
     * Generate SOAP fault message in XML format.
     *
     * @param string $reason Human-readable explanation of the fault
     * @param string $code SOAP fault code
     * @param string $language Reason message language
     * @param array|null $details Detailed reason message(s)
     * @return string
     */
    public function getSoapFaultMessage($reason, $code, $language, $details)
    {
        if (is_array($details) && !empty($details)) {
            $detailsXml = $this->_convertDetailsToXml($details);
            $detailsXml = $detailsXml ? "<env:Detail>" . $detailsXml . "</env:Detail>" : '';
        } else {
            $detailsXml = '';
        }
        $detailsNamespace = !empty($detailsXml) ? 'xmlns:m="http://magento.com"': '';
        $reason = htmlentities($reason);
        $message = <<<FAULT_MESSAGE
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" $detailsNamespace>
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:$code</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="$language">$reason</env:Text>
         </env:Reason>
         $detailsXml
      </env:Fault>
   </env:Body>
</env:Envelope>
FAULT_MESSAGE;
        return $message;
    }

    /**
     * Recursively convert details array into XML structure.
     *
     * @param array $details
     * @return string
     */
    protected function _convertDetailsToXml($details)
    {
        $detailsXml = '';
        foreach ($details as $detailNode => $detailValue) {
            $detailNode = htmlspecialchars($detailNode);
            if (is_numeric($detailNode)) {
                continue;
            }
            if (is_string($detailValue) || is_numeric($detailValue)) {
                $detailsXml .= "<m:$detailNode>" . htmlspecialchars($detailValue) . "</m:$detailNode>";
            } elseif (is_array($detailValue)) {
                $detailsXml .= "<m:$detailNode>" . $this->_convertDetailsToXml($detailValue) . "</m:$detailNode>";
            }
        }
        return $detailsXml;
    }
}
