<?php
/**
 * Helper class for generating OAuth related credentials
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\TestFramework\Authentication;

use Zend\Stdlib\Exception\LogicException;
use Magento\TestFramework\Helper\Bootstrap;
use OAuth\Common\Consumer\Credentials;
use Magento\TestFramework\Authentication\Rest\OauthClient;

class OauthHelper
{
    /** @var array */
    protected static $_apiCredentials;

    /**
     * Generate authentication credentials
     * @param string $date consumer creation date
     * @return array
     * <pre>
     * array (
     *   'key' => 'ajdsjashgdkahsdlkjasldkjals', //consumer key
     *   'secret' => 'alsjdlaskjdlaksjdlasjkdlas', //consumer secret
     *   'verifier' => 'oiudioqueoiquweoiqwueoqwuii'
     *   'consumer' => $consumer, // retrieved consumer Model
     *   'token' => $token // retrieved token Model
     *   );
     * </pre>
     */
    public static function getConsumerCredentials($date = null)
    {
        $integration = self::_createIntegration('all');
        $objectManager = Bootstrap::getObjectManager();
        /** @var $oauthService \Magento\Integration\Service\OauthV1 */
        $oauthService = $objectManager->get('Magento\Integration\Service\OauthV1');
        $consumer = $oauthService->loadConsumer($integration->getConsumerId());
        $url = TESTS_BASE_URL;
        $consumer->setCallbackUrl($url);
        $consumer->setRejectedCallbackUrl($url);
        if (!is_null($date)) {
            $consumer->setCreatedAt($date);
        }
        $consumer->save();
        $token = $objectManager->create('Magento\Integration\Model\Oauth\Token');
        $verifier = $token->createVerifierToken($consumer->getId())->getVerifier();

        return array (
            'key' => $consumer->getKey(),
            'secret' => $consumer->getSecret(),
            'verifier' => $verifier,
            'consumer' => $consumer,
            'token' => $token
        );
    }

    /**
     * Create an access token to associated to a consumer to access APIs. No resources are available to this consumer.
     *
     * @return array comprising of token  key and secret
     * <pre>
     * array (
     *   'key' => 'ajdsjashgdkahsdlkjasldkjals', //token key
     *   'secret' => 'alsjdlaskjdlaksjdlasjkdlas', //token secret
     *   'oauth_client' => $oauthClient // OauthClient instance used to fetch the access token
     *   );
     * </pre>
     */
    public static function getAccessToken()
    {
        $consumerCredentials = self::getConsumerCredentials();
        $credentials = new Credentials($consumerCredentials['key'], $consumerCredentials['secret'], TESTS_BASE_URL);
        $oAuthClient = new OauthClient($credentials);
        $requestToken = $oAuthClient->requestRequestToken();
        $accessToken = $oAuthClient->requestAccessToken(
            $requestToken->getRequestToken(),
            $consumerCredentials['verifier'],
            $requestToken->getRequestTokenSecret()
        );

        /** TODO: Reconsider return format. It is not aligned with method name. */
        return array (
            'key' => $accessToken->getAccessToken(),
            'secret' => $accessToken->getAccessTokenSecret(),
            'oauth_client' => $oAuthClient
        );
    }

    /**
     * Create an access token, tied to integration which has permissions to all API resources in the system.
     *
     * @param array $resources list of resources to grant to the integration
     * @return array
     * <pre>
     * array (
     *   'key' => 'ajdsjashgdkahsdlkjasldkjals', //token key
     *   'secret' => 'alsjdlaskjdlaksjdlasjkdlas', //token secret
     *   'oauth_client' => $oauthClient // OauthClient instance used to fetch the access token
     *   'integration' => $integration // Integration instance associated with access token
     *   );
     * </pre>
     * @throws LogicException
     */
    public static function getApiAccessCredentials($resources = null)
    {
        if (!self::$_apiCredentials) {
            $integration = self::_createIntegration($resources);
            $objectManager = Bootstrap::getObjectManager();
            /** @var \Magento\Integration\Service\OauthV1 $oauthService */
            $oauthService = $objectManager->get('Magento\Integration\Service\OauthV1');
            $oauthService->createAccessToken($integration->getConsumerId());
            $accessToken = $oauthService->getAccessToken($integration->getConsumerId());
            if (!$accessToken) {
                throw new LogicException('Access token was not created.');
            }
            $consumer = $oauthService->loadConsumer($integration->getConsumerId());
            $credentials = new Credentials(
                $consumer->getKey(), $consumer->getSecret(), TESTS_BASE_URL);
            /** @var $oAuthClient OauthClient */
            $oAuthClient = new OauthClient($credentials);

            self::$_apiCredentials = array(
                'key' => $accessToken->getToken(),
                'secret' => $accessToken->getSecret(),
                'oauth_client' => $oAuthClient,
                'integration' => $integration
            );
        }
        return self::$_apiCredentials;
    }

    /**
     * Forget API access credentials.
     */
    public static function clearApiAccessCredentials()
    {
        self::$_apiCredentials = false;
    }

    /**
     * Remove fs element with nested elements.
     *
     * @param string $dir
     * @param bool   $doSaveRoot
     */
    protected static function _rmRecursive($dir, $doSaveRoot = false)
    {
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $object) {
                if (is_dir($object)) {
                    self::_rmRecursive($object);
                } else {
                    unlink($object);
                }
            }
            if (!$doSaveRoot) {
                rmdir($dir);
            }
        } else {
            unlink($dir);
        }
    }

    /**
     * Create integration instance.
     *
     * @param array $resources
     * @return \Magento\Integration\Model\Integration
     * @throws \Zend\Stdlib\Exception\LogicException
     */
    protected static function _createIntegration($resources)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var $integrationService \Magento\Integration\Service\IntegrationV1Interface */
        $integrationService = $objectManager->get('Magento\Integration\Service\IntegrationV1Interface');

        $params = ['name' => 'Integration' . microtime()];

        if ($resources === null || $resources == 'all') {
            $params['all_resources'] = true;
        } else {
            $params['resource'] = $resources;
        }
        $integration = $integrationService->create($params);
        $integration->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)->save();

        /** Magento cache must be cleared to activate just created ACL role. */
        $varPath = realpath('../../../var');
        if (!$varPath) {
            throw new LogicException("Magento cache cannot be cleared after new ACL role creation.");
        } else {
            $cachePath = $varPath . '/cache';
            if (is_dir($cachePath)) {
                self::_rmRecursive($cachePath, true);
            }
        }
        return $integration;
    }
}
