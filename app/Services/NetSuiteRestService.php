<?php

namespace App\Services;

use App\Utils\HmacSha256;
use Eher\OAuth\Consumer;
use Eher\OAuth\OAuthException;
use Eher\OAuth\Request;
use Eher\OAuth\Token;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class NetSuiteRestService
{

    protected string $accountId;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $tokenId;
    protected string $tokenSecret;
    
    public function __construct()
    {
        $this->accountId      = (string) env('NETSUITE_ACCOUNT_ID');
        $this->consumerKey    = (string) env('NETSUITE_CONSUMER_KEY');
        $this->consumerSecret = (string) env('NETSUITE_CONSUMER_SECRET');
        $this->tokenId        = (string) env('NETSUITE_TOKEN_ID');
        $this->tokenSecret    = (string) env('NETSUITE_TOKEN_SECRET');
    }
    /**
     * Envía la solicitud al RESTlet.
     *
     * @throws GuzzleException
     */
    public function request(string $path, string $method = 'GET', array $data = [])
    {
        $client = new Client();
        $restletUrl = "https://{$this->accountId}.restlets.api.netsuite.com/app/site/hosting" . $path;
        $headers = $this->buildHeaders($method, $restletUrl);

        $options = [
            RequestOptions::HEADERS => $headers,
        ];

        if (strtoupper($method) !== 'GET') {
            $options[RequestOptions::JSON] = $data;
        }

        $response = $client->request($method, $restletUrl, $options);

        if ($response->getStatusCode() >= 500) {
            return null;
        }

        return json_decode($response->getBody()?->getContents(), true);
    }

    /**
     * Construye los headers de autenticación OAuth1 para NetSuite.
     */
    private function buildHeaders(string $method, string $url): array
    {
        $signatureMethod = new HmacSha256();
        $consumer = new Consumer($this->consumerKey, $this->consumerSecret);
        $accessToken = new Token($this->tokenId, $this->tokenSecret);

        $request = new Request($method, $url, [
            'oauth_nonce'            => md5(mt_rand()),
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0',
            'oauth_token'            => $accessToken->key,
            'oauth_consumer_key'     => $consumer->key,
            'oauth_signature_method' => $signatureMethod->get_name(),
        ]);

        $signature = $request->build_signature($signatureMethod, $consumer, $accessToken);
        $request->set_parameter('oauth_signature', $signature);
        $request->set_parameter('realm', $this->accountId);

        try {
            return [
                'Authorization' => substr($request->to_header($this->accountId), 15),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ];
        } catch (OAuthException $e) {
            return [];
        }
    }
}
