<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Search
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Solr search engine adapter that perform raw queries to Solr server based on Conduit solr client library
 * and basic solr adapter
 *
 * @category   Magento
 * @package    Magento_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Search\Model\Adapter;

class HttpStream extends \Magento\Search\Model\Adapter\Solr\AbstractSolr
    implements \Magento\Search\Model\AdapterInterface
{
    /**
     * Object name used to create solr document object
     *
     * @var string
     */
    protected $_clientDocObjectName = 'Apache_Solr_Document';

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_ctlgInventData;

    /**
     * @param \Magento\Customer\Model\Session                              $customerSession
     * @param \Magento\Search\Model\Catalog\Layer\Filter\Price             $filterPrice
     * @param \Magento\Search\Model\Resource\Index                         $resourceIndex
     * @param \Magento\CatalogSearch\Model\Resource\Fulltext               $resourceFulltext
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection
     * @param \Magento\Logger                                   $logger
     * @param \Magento\Core\Model\StoreManagerInterface                    $storeManager
     * @param \Magento\Core\Model\CacheInterface                           $cache
     * @param \Magento\Eav\Model\Config                                    $eavConfig
     * @param \Magento\Search\Model\Factory\Factory                        $searchFactory
     * @param \Magento\Search\Helper\ClientInterface                       $clientHelper
     * @param \Magento\Core\Model\Registry                                 $registry
     * @param \Magento\Core\Model\Store\ConfigInterface                    $coreStoreConfig
     * @param \Magento\CatalogInventory\Helper\Data                        $ctlgInventData
     * @param array                                                       $options
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Search\Model\Catalog\Layer\Filter\Price $filterPrice,
        \Magento\Search\Model\Resource\Index $resourceIndex,
        \Magento\CatalogSearch\Model\Resource\Fulltext $resourceFulltext,
        \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection,
        \Magento\Logger $logger,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\CacheInterface $cache,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Search\Model\Factory\Factory $searchFactory,
        \Magento\Search\Helper\ClientInterface $clientHelper,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\CatalogInventory\Helper\Data $ctlgInventData,
        array $options = array()
    ) {
        $this->_ctlgInventData = $ctlgInventData;
        parent::__construct(
            $customerSession, $filterPrice, $resourceIndex, $resourceFulltext, $attributeCollection,
            $logger, $storeManager, $cache, $eavConfig, $searchFactory, $clientHelper, $registry,
            $coreStoreConfig, $options
        );
    }

    /**
     * Simple Search interface
     *
     * @param string $query The raw query string
     * @param array $params Params can be specified like this:
     *        'offset'      - The starting offset for result documents
     *        'limit        - The maximum number of result documents to return
     *        'sort_by'     - Sort field, can be just sort field name (and asceding order will be used by default),
     *                        or can be an array of arrays like this: array('sort_field_name' => 'asc|desc')
     *                        to define sort order and sorting fields.
     *                        If sort order not asc|desc - asceding will used by default
     *        'fields'      - Fields names which are need to be retrieved from found documents
     *        'solr_params' - Key / value pairs for other query parameters (see Solr documentation),
     *                        use arrays for parameter keys used more than once (e.g. facet.field)
     *        'locale_code' - Locale code, it used to define what suffix is needed for text fields,
     *                        by which will be performed search request and sorting
     *
     * @return array
     */
    protected function _search($query, $params = array())
    {
        $searchConditions = $this->prepareSearchConditions($query);
        if (!$searchConditions) {
            return array();
        }

        $_params = $this->_defaultQueryParams;
        if (is_array($params) && !empty($params)) {
            $_params = array_intersect_key($params, $_params) + array_diff_key($_params, $params);
        }

        $offset = (isset($_params['offset'])) ? (int) $_params['offset'] : 0;
        $limit  = (isset($_params['limit']))
            ? (int) $_params['limit']
            : \Magento\Search\Model\Adapter\Solr\AbstractSolr::DEFAULT_ROWS_LIMIT;

        $languageSuffix = $this->_getLanguageSuffix($params['locale_code']);
        $searchParams   = array();

        if (!is_array($_params['fields'])) {
            $_params['fields'] = array($_params['fields']);
        }

        if (!is_array($_params['solr_params'])) {
            $_params['solr_params'] = array($_params['solr_params']);
        }

        /**
         * Add sort fields
         */
        $sortFields = $this->_prepareSortFields($_params['sort_by']);
        foreach ($sortFields as $sortField) {
            $searchParams['sort'][] = $sortField['sortField'] . ' ' . $sortField['sortType'];
        }

        /**
         * Fields to retrieve
         */
        if ($limit && !empty($_params['fields'])) {
            $searchParams['fl'] = implode(',', $_params['fields']);
        }

        /**
         * Now supported search only in fulltext and name fields based on dismax requestHandler (named as magento_lng).
         * Using dismax requestHandler for each language make matches in name field
         * are much more significant than matches in fulltext field.
         */
        if ($_params['ignore_handler'] !== true) {
            $_params['solr_params']['qt'] = 'magento' . $languageSuffix;
        }

        /**
         * Facets search
         */
        $useFacetSearch = (isset($params['solr_params']['facet']) && $params['solr_params']['facet'] == 'on');
        if ($useFacetSearch) {
            $searchParams += $this->_prepareFacetConditions($params['facet']);
        }

        /**
         * Suggestions search
         */
        $useSpellcheckSearch = isset($params['solr_params']['spellcheck'])
            && $params['solr_params']['spellcheck'] == 'true';

        if ($useSpellcheckSearch) {
            if (isset($params['solr_params']['spellcheck.count'])
                && (int) $params['solr_params']['spellcheck.count'] > 0
            ) {
                $spellcheckCount = (int) $params['solr_params']['spellcheck.count'];
            } else {
                $spellcheckCount = self::DEFAULT_SPELLCHECK_COUNT;
            }

            $_params['solr_params'] += array(
                'spellcheck.collate'         => 'true',
                'spellcheck.dictionary'      => 'magento_spell' . $languageSuffix,
                'spellcheck.extendedResults' => 'true',
                'spellcheck.count'           => $spellcheckCount
            );
        }

        /**
         * Specific Solr params
         */
        if (!empty($_params['solr_params'])) {
            foreach ($_params['solr_params'] as $name => $value) {
                $searchParams[$name] = $value;
            }
        }

        $searchParams['fq'] = $this->_prepareFilters($_params['filters']);

        /**
         * Store filtering
         */
        if ($_params['store_id'] > 0) {
            $searchParams['fq'][] = 'store_id:' . $_params['store_id'];
        }
        if (!$this->_ctlgInventData->isShowOutOfStock()) {
            $searchParams['fq'][] = 'in_stock:true';
        }

        $searchParams['fq'] = implode(' AND ', $searchParams['fq']);

        try {
            $this->ping();
            $response = $this->_client->search(
                $searchConditions, $offset, $limit, $searchParams, \Apache_Solr_Service::METHOD_POST
            );
            $data = json_decode($response->getRawResponse());

            if (!isset($params['solr_params']['stats']) || $params['solr_params']['stats'] != 'true') {
                if ($limit > 0) {
                    $result = array('ids' => $this->_prepareQueryResponse($data));
                }

                /**
                 * Extract facet search results
                 */
                if ($useFacetSearch) {
                    $result['faceted_data'] = $this->_prepareFacetsQueryResponse($data);
                }

                /**
                 * Extract suggestions search results
                 */
                if ($useSpellcheckSearch) {
                    $resultSuggestions = $this->_prepareSuggestionsQueryResponse($data);
                    /* Calc results count for each suggestion */
                    if (isset($params['spellcheck_result_counts']) && $params['spellcheck_result_counts']
                        && count($resultSuggestions)
                        && $spellcheckCount > 0
                    ) {
                        /* Temporary store value for main search query */
                        $tmpLastNumFound = $this->_lastNumFound;

                        unset($params['solr_params']['spellcheck']);
                        unset($params['solr_params']['spellcheck.count']);
                        unset($params['spellcheck_result_counts']);

                        $suggestions = array();
                        foreach ($resultSuggestions as $key => $item) {
                            $this->_lastNumFound = 0;
                            $this->search($item['word'], $params);
                            if ($this->_lastNumFound) {
                                $resultSuggestions[$key]['num_results'] = $this->_lastNumFound;
                                $suggestions[] = $resultSuggestions[$key];
                                $spellcheckCount--;
                            }
                            if ($spellcheckCount <= 0) {
                                break;
                            }
                        }

                        /* Return store value for main search query */
                        $this->_lastNumFound = $tmpLastNumFound;
                    } else {
                        $suggestions = array_slice($resultSuggestions, 0, $spellcheckCount);
                    }
                    $result['suggestions_data'] = $suggestions;
                }
            } else {
                $result = $this->_prepateStatsQueryResponce($data);
            }

            return $result;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
    }

    /**
     * Checks if Solr server is still up
     *
     * @return bool
     */
    public function ping()
    {
        return parent::ping();
    }

    /**
     * Retrieve attribute solr field name
     *
     * @param   \Magento\Catalog\Model\Resource\Eav\Attribute|string $attribute
     * @param   string $target - default|sort|nav
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        return parent::getSearchEngineFieldName($attribute, $target);
    }
}
