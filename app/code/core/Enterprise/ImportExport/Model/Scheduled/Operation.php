<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Operation model
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method string getOperationType() getOperationType()
 * @method Enterprise_ImportExport_Model_Scheduled_Operation setOperationType() setOperationType(string $value)
 * @method string getEntityType() getEntityType()
 * @method string getEntitySubtype() getEntitySubtype()
 * @method Enterprise_ImportExport_Model_Scheduled_Operation setEntityType() setEntityType(string $value)
 * @method Enterprise_ImportExport_Model_Scheduled_Operation setEntitySubtype() setEntitySubtype(string $value)
 * @method string|array getStartTime() getStartTime()
 * @method Enterprise_ImportExport_Model_Scheduled_Operation setStartTime() setStartTime(string $value)
 * @method string|array getFileInfo() getFileInfo()
 * @method string|array getEntityAttributes() getEntityAttributes()
 * @method string getBehavior() getBehavior()
 * @method string getForceImport() getForceImport()
 * @method Enterprise_ImportExport_Model_Scheduled_Operation setLastRunDate() setLastRunDate(int $value)
 * @method int getLastRunDate() getLastRunDate()
 */
class Enterprise_ImportExport_Model_Scheduled_Operation extends Mage_Core_Model_Abstract
{
    /**
     * Log directory
     *
     */
    const LOG_DIRECTORY = 'log/import_export/';

    /**
     * File history directory
     *
     */
    const FILE_HISTORY_DIRECTORY = 'history';

    /**
     * Email config prefix
     */
    const CONFIG_PREFIX_EMAILS = 'trans_email/ident_';

    /**
     * Cron config template
     */
    const CRON_STRING_PATH = 'crontab/jobs/scheduled_operation_%d/%s';

    /**
     * Cron callback config
     */
    const CRON_MODEL = 'Enterprise_ImportExport_Model_Observer::processScheduledOperation';

    /**
     * Cron job name prefix
     */
    const CRON_JOB_NAME_PREFIX = 'scheduled_operation_';

    /**
     * Date model
     *
     * @var Mage_Core_Model_Date
     */
    protected $_dateModel;

    /**
     * Initialize operation model
     *
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Mage_Core_Model_Date $dateModel
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param array $data
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    public function __construct(Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Mage_Core_Model_Date $dateModel,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($eventDispatcher, $cacheManager, $resource, $resourceCollection, $data);

        $this->_init('Enterprise_ImportExport_Model_Resource_Scheduled_Operation');

        $this->_dateModel = $dateModel;
    }

    /**
     * Date model getter
     *
     * @return Mage_Core_Model_Date
     */
    public function getDateModel()
    {
        return $this->_dateModel;
    }

    /**
     * Send email notification
     *
     * @param array $vars
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    public function sendEmailNotification($vars = array())
    {
        $storeId = Mage::app()->getStore()->getId();
        $copyTo = explode(',', $this->getEmailCopy());
        $copyMethod = $this->getEmailCopyMethod();

        $mailer = Mage::getSingleton('Mage_Core_Model_Email_Template_Mailer');
        $emailInfo = Mage::getModel('Mage_Core_Model_Email_Info');

        $receiverEmail = Mage::getStoreConfig(
            self::CONFIG_PREFIX_EMAILS . $this->getEmailReceiver() . '/email',
            $storeId
        );
        $receiverName  = Mage::getStoreConfig(
            self::CONFIG_PREFIX_EMAILS . $this->getEmailReceiver() . '/name',
            $storeId
        );

        $emailInfo->addTo($receiverEmail, $receiverName);

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('Mage_Core_Model_Email_Info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender($this->getEmailSender());
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($this->getEmailTemplate());
        $mailer->setTemplateParams($vars);
        $mailer->send();
        return $this;
    }

    /**
     * Unserialize file_info and entity_attributes after load
     *
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _afterLoad()
    {
        $fileInfo = $this->getFileInfo();
        if (trim($fileInfo)) {
            $this->setFileInfo(unserialize($fileInfo));
        }

        $attrsInfo = $this->getEntityAttributes();
        if (trim($attrsInfo)) {
            $this->setEntityAttributes(unserialize($attrsInfo));
        }

        return parent::_afterLoad();
    }

    /**
     * Serialize file_info and entity_attributes arrays before save
     *
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _beforeSave()
    {
        $fileInfo = $this->getFileInfo();
        if (is_array($fileInfo) && $fileInfo) {
            $this->setFileInfo(serialize($fileInfo));
        }

        $attrsInfo = $this->getEntityAttributes();
        if (is_array($attrsInfo) && $attrsInfo) {
            $this->setEntityAttributes(serialize($attrsInfo));
        }

        return parent::_beforeSave();
    }

    /**
     * Add task to cron after save
     *
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _afterSave()
    {
        if ($this->getStatus() == 1) {
            $this->_addCronTask();
        } else {
            $this->_dropCronTask();
        }
        return parent::_afterSave();
    }

    /**
     * Delete cron task
     *
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _afterDelete()
    {
        $this->_dropCronTask();
        return parent::_afterDelete();
    }

    /**
     * Add operation to cron
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _addCronTask()
    {
        $frequency = $this->getFreq();
        $time = $this->getStartTime();
        if (!is_array($time)) {
            $time = explode(':', $time);
        }
        $cronExprArray = array(
            intval($time[1]),
            intval($time[0]),
            ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY) ? '1' : '*',
            '*',
            ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY) ? '1' : '*'
        );

        $cronExprString = join(' ', $cronExprArray);
        $exprPath  = $this->getExprConfigPath();
        $modelPath = $this->getModelConfigPath();
        try {
            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load($exprPath, 'path')
                ->setValue($cronExprString)
                ->setPath($exprPath)
                ->save();

            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load($modelPath, 'path')
                ->setValue(self::CRON_MODEL)
                ->setPath($modelPath)
                ->save();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('Mage_Cron_Helper_Data')->__('Unable to save the cron expression.'));
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Remove cron task
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _dropCronTask()
    {
        try {
            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load($this->getExprConfigPath(), 'path')
                ->delete();
            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load($this->getModelConfigPath(), 'path')
                ->delete();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('Mage_Cron_Helper_Data')->__('Unable to delete the cron task.'));
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Get cron_expr config path
     *
     * @return string
     */
    public function getExprConfigPath()
    {
        return sprintf(self::CRON_STRING_PATH, $this->getId(), 'schedule/cron_expr');
    }

    /**
     * Get cron callback model config path
     *
     * @return string
     */
    public function getModelConfigPath()
    {
        return sprintf(self::CRON_STRING_PATH, $this->getId(), 'run/model');
    }

    /**
     * Load operation by cron job code.
     * Operation id must present in job code.
     *
     * @throws Mage_Core_Exception
     * @param string $jobCode
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    public function loadByJobCode($jobCode)
    {
        $idPos = strrpos($jobCode, '_');
        if ($idPos !== false) {
            $operationId = (int)substr($jobCode, $idPos + 1);
        }
        if (!isset($operationId) || !$operationId) {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Invalid cron job task'));
        }

        return $this->load($operationId);
    }

    /**
     * Run scheduled operation. If some error ocurred email notification will be send
     *
     * @return bool
     */
    public function run()
    {
        $runDate = $this->getDateModel()->date();
        $runDateTimestamp = $this->getDateModel()->gmtTimestamp($runDate);

        $this->setLastRunDate($runDateTimestamp);

        $operation = $this->getInstance();
        $operation->setRunDate($runDateTimestamp);

        $result = false;
        try {
            $result = $operation->runSchedule($this);
            if ($result) {
                $operation->reindexAll();
            }
        } catch (Exception $e) {
            $operation->addLogComment($e->getMessage());
        }

        $filePath = $this->getHistoryFilePath();
        if (!file_exists($filePath)) {
            $filePath = Mage::helper('Enterprise_ImportExport_Helper_Data')->__('File has been not created');
        }

        if (!$result || isset($e) && is_object($e)) {
            $operation->addLogComment(
                Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Operation finished with fail status')
            );
            $this->sendEmailNotification(array(
                'operationName'  => $this->getName(),
                'trace'          => nl2br($operation->getFormatedLogTrace()),
                'entity'         => $this->getEntityType(),
                'dateAndTime'    => $runDate,
                'fileName'       => $filePath
            ));
        }

        $this->setIsSuccess($result);
        $this->save();

        return $result;
    }

    /**
     * Get file based on "file_info" from server (ftp, local) and put to tmp directory
     *
     * throws Mage_Core_Exception
     * @param Enterprise_ImportExport_Model_Scheduled_Operation_Interface $operation
     * @return string full file path
     */
    public function getFileSource(Enterprise_ImportExport_Model_Scheduled_Operation_Interface $operation)
    {
        $fileInfo = $this->getFileInfo();
        if (empty($fileInfo['file_name'])) {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Unable to read file source. File name is empty'));
        }
        $operation->addLogComment(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Connecting to server'));
        $fs = $this->getServerIoDriver();
        $operation->addLogComment(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Reading import file'));

        $extension = pathinfo($fileInfo['file_name'], PATHINFO_EXTENSION);
        $filePath  = $fileInfo['file_name'];
        $tmpFilePath = sys_get_temp_dir() . DS . uniqid(time(), true) . '.' . $extension;
        if (!$fs->read($filePath, $tmpFilePath)) {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Unable to read import file'));
        }
        $fs->close();
        $operation->addLogComment(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Save history file content "%s"', $this->getHistoryFilePath()));
        $this->_saveOperationHistory($tmpFilePath);
        return $tmpFilePath;
    }

    /**
     * Save/upload file to server (ftp, local)
     *
     * @throws Mage_Core_Exception
     * @param Enterprise_ImportExport_Model_Scheduled_Operation_Interface $operation
     * @param string $fileContent
     * @return bool
     */
    public function saveFileSource(Enterprise_ImportExport_Model_Scheduled_Operation_Interface $operation, $fileContent)
    {
        $result = false;

        $operation->addLogComment(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Save history file content "%s"', $this->getHistoryFilePath()));
        $this->_saveOperationHistory($fileContent);

        $fileInfo = $this->getFileInfo();
        $fs       = $this->getServerIoDriver();
        $fileName = $operation->getScheduledFileName() . '.' . $fileInfo['file_format'];
        $result   = $fs->write($fileName, $fileContent);
        if (!$result) {
            Mage::throwException(
                Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Unable to write file "%s" to "%s" with "%s" driver', $fileName, $fileInfo['file_path'], $fileInfo['server_type'])
            );
        }
        $operation->addLogComment(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Save file content'));

        $fs->close();

        return $result;
    }

    /**
     * Get operation instance by operation type and set specific data to it
     * Supported import, export
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_ImportExport_Model_Export|Enterprise_ImportExport_Model_Import
     */
    public function getInstance()
    {
        $operation = Mage::getModel('Enterprise_ImportExport_Model_' . uc_words($this->getOperationType()));
        if (!$operation || !($operation instanceof Enterprise_ImportExport_Model_Scheduled_Operation_Interface)) {
            Mage::throwException(
                Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Invalid scheduled operation')
            );
        }

        $operation->initialize($this);
        return $operation;
    }

    /**
     * Get and initialize file system driver by operation file section configuration
     *
     * @throws Mage_Core_Exception
     * @return Varien_Io_Abstract
     */
    public function getServerIoDriver()
    {
        $fileInfo = $this->getFileInfo();
        $availableTypes = Mage::getModel('Enterprise_ImportExport_Model_Scheduled_Operation_Data')
            ->getServerTypesOptionArray();
        if (!isset($fileInfo['server_type'])
            || !$fileInfo['server_type']
            || !isset($availableTypes[$fileInfo['server_type']])
        ) {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Invalid server type'));
        }

        $class = 'Varien_Io_' . ucfirst(strtolower($fileInfo['server_type']));
        if (!class_exists($class)) {
            Mage::throwException(
                Mage::helper('Enterprise_ImportExport_Helper_Data')
                    ->__('Invalid server communication class "%s"', $class)
            );
        }
        $driver = new $class;
        $driver->open($this->_prepareIoConfiguration($fileInfo));
        return $driver;
    }

    /**
     * Prepare data for server io driver initialization
     *
     * @param array $fileInfo
     * @return array prepared configuration
     */
    protected function _prepareIoConfiguration($fileInfo)
    {
        $data = array();
        foreach ($fileInfo as $key => &$v) {
            $key = str_replace('file_', '', $key);
            $data[$key] = $v;
        }
        unset($data['format'], $data['server_type'], $data['name']);
        if (isset($data['mode'])) {
            $data['file_mode'] = $data['mode'];
            unset($data['mode']);
        }
        if (isset($data['host']) && strpos($data['host'], ':') !== false) {
            $tmp = explode(':', $data['host']);
            $data['port'] = array_pop($tmp);
            $data['host'] = join(':', $tmp);
        }

        return $data;
    }

    /**
     * Save operation file history.
     *
     * @throws Mage_Core_Exception
     * @param string $source
     * @return Enterprise_ImportExport_Model_Scheduled_Operation
     */
    protected function _saveOperationHistory($source)
    {
        $filePath = $this->getHistoryFilePath();

        $fs = new Varien_Io_File();
        $fs->open(array(
            'path' => dirname($filePath)
        ));
        if (!$fs->write(basename($filePath), $source)) {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Unable to save file history file'));
        }
        return $this;
    }

    /**
     * Get dir path of history operation files
     *
     * @return string
     */
    protected function _getHistoryDirPath()
    {
        $dirPath = Mage::getBaseDir('var') . DS . self::LOG_DIRECTORY . date('Y' . DS . 'm' . DS . 'd')
            . DS . self::FILE_HISTORY_DIRECTORY . DS;

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        return $dirPath;
    }

    /**
     * Get file path of history operation files
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getHistoryFilePath()
    {
        $dirPath = $this->_getHistoryDirPath();

        $fileName = join('_', array(
            $this->_getRunTime(),
            $this->getOperationType(),
            $this->getEntityType()
        ));

        $fileInfo = $this->getFileInfo();
        if (isset($fileInfo['file_format'])) {
            $extension = $fileInfo['file_format'];
        } elseif (isset($fileInfo['file_name'])) {
            $extension = pathinfo($fileInfo['file_name'], PATHINFO_EXTENSION);
        } else {
            Mage::throwException(Mage::helper('Enterprise_ImportExport_Helper_Data')->__('Unknown file format'));
        }

        return $dirPath . $fileName . '.' . $extension;
    }

    /**
     * Get current time
     *
     * @return string
     */
    protected function _getRunTime()
    {
        $runDate = $this->getLastRunDate() ? $this->getLastRunDate() : null;
        return $this->getDateModel()->date('H-i-s', $runDate);
    }
}
