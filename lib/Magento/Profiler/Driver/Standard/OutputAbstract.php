<?php
/**
 * Abstract class that represents profiler standard driver output
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
abstract class Magento_Profiler_Driver_Standard_OutputAbstract
    implements Magento_Profiler_Driver_Standard_OutputInterface
{
    /**
     * PCRE Regular Expression for filter timer by id
     *
     * @var null|string
     */
    protected $_filterPattern;

    /**
     * List of threshold (minimal allowed) values for profiler data
     *
     * @var array
     */
    protected $_thresholds = array(
        Magento_Profiler_Driver_Standard_Stat::TIME => 0.001,
        Magento_Profiler_Driver_Standard_Stat::COUNT => 10,
        Magento_Profiler_Driver_Standard_Stat::EMALLOC => 10000,
    );

    /**
     * List of columns to output
     *
     * @var array
     */
    protected $_columns = array(
        'Timer Id' => Magento_Profiler_Driver_Standard_Stat::ID,
        'Time'     => Magento_Profiler_Driver_Standard_Stat::TIME,
        'Avg'      => Magento_Profiler_Driver_Standard_Stat::AVG,
        'Cnt'      => Magento_Profiler_Driver_Standard_Stat::COUNT,
        'Emalloc'  => Magento_Profiler_Driver_Standard_Stat::EMALLOC,
        'RealMem'  => Magento_Profiler_Driver_Standard_Stat::REALMEM,
    );

    /**
     * Set profiler output with timer identifiers filter.
     *
     * @param string $filterPattern PCRE pattern to filter timers by their identifiers
     */
    public function setFilterPattern($filterPattern)
    {
        $this->_filterPattern = $filterPattern;
    }

    /**
     * Set threshold (minimal allowed) value for timer column.
     *
     * Timer is being rendered if at least one of its columns is not less than the minimal allowed value.
     *
     * @param string $fetchKey
     * @param int|float|null $minAllowedValue
     */
    public function setThreshold($fetchKey, $minAllowedValue)
    {
        if ($minAllowedValue === null) {
            unset($this->_thresholds[$fetchKey]);
        } else {
            $this->_thresholds[$fetchKey] = $minAllowedValue;
        }
    }

    /**
     * Render statistics column value for specified timer
     *
     * @param mixed $value
     * @param string $columnKey
     * @return string
     */
    protected function _renderColumnValue($value, $columnKey)
    {
        switch ($columnKey) {
            case Magento_Profiler_Driver_Standard_Stat::ID:
                $result = $this->_renderTimerId($value);
                break;
            case Magento_Profiler_Driver_Standard_Stat::TIME:
            case Magento_Profiler_Driver_Standard_Stat::AVG:
                $result = number_format($value, 6);
                break;
            default:
                $result = number_format((string)$value);
        }
        return $result;
    }

    /**
     * Render timer id
     *
     * @param string $timerId
     * @return string
     */
    protected function _renderTimerId($timerId)
    {
        return $timerId;
    }

    /**
     * Render a caption for the profiling results
     *
     * @return string
     */
    protected function _renderCaption()
    {
        return sprintf(
            'Code Profiler (Memory usage: real - %s, emalloc - %s)',
            memory_get_usage(true),
            memory_get_usage()
        );
    }

    /**
     * Retrieve the list of timer ids from timer statistics object.
     *
     * Timer ids will be ordered and filtered by thresholds and filter pattern.
     *
     * @param Magento_Profiler_Driver_Standard_Stat $stat
     * @return array
     */
    protected function _getTimerIds(Magento_Profiler_Driver_Standard_Stat $stat)
    {
        return $stat->getFilteredTimerIds($this->_thresholds, $this->_filterPattern);
    }
}