<?php
/**
 * UniPaymentClient
 */

namespace UniPayment\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use UniPayment\Client\Model\CreateInvoiceRequest;
use UniPayment\Client\Model\GetCurrenciesResponse;
use UniPayment\Client\Model\GetExchangeRateByCurrencyPairResponse;
use UniPayment\Client\Model\GetExchangeRateByFiatCurrencyResponse;
use UniPayment\Client\Model\GetInvoiceByIdResponse;
use UniPayment\Client\Model\QueryInvoiceRequest;
use UniPayment\Client\Model\QueryInvoiceResponse;
use UniPayment\Client\Model\ResponseInvoiceModel;

/**
 * UniPaymentClient Class
 */
class UniPaymentClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @param ClientInterface|null $client
     * @param Configuration|null $config
     * @param HeaderSelector|null $selector
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration   $config = null,
        HeaderSelector  $selector = null
    )
    {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation createInvoice
     *
     * @param CreateInvoiceRequest $body body (required)
     *
     * @return ResponseInvoiceModel
     * @throws \InvalidArgumentException
     * @throws ApiException|\GuzzleHttp\Exception\GuzzleException on non-2xx response
     */
    public function createInvoice($body)
    {
        list($response) = $this->createInvoiceWithHttpInfo($body);
        return $response;
    }

    /**
     * Operation createInvoiceWithHttpInfo
     *
     * @param CreateInvoiceRequest $body (required)
     *
     * @return array of ResponseInvoiceModel, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException|\GuzzleHttp\Exception\GuzzleException on non-2xx response
     */
    public function createInvoiceWithHttpInfo($body)
    {
        $returnType = '\UniPayment\Client\Model\CreateInvoiceResponse';
        $request = $this->createInvoiceRequest($body);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseInvoiceModel',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation createInvoiceAsync
     *
     *
     *
     * @param CreateInvoiceRequest $body (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function createInvoiceAsync($body)
    {
        return $this->createInvoiceAsyncWithHttpInfo($body)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation createInvoiceAsyncWithHttpInfo
     *
     *
     *
     * @param CreateInvoiceRequest $body (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function createInvoiceAsyncWithHttpInfo($body)
    {
        $returnType = 'ResponseInvoiceModel';
        $request = $this->createInvoiceRequest($body);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'createInvoice'
     *
     * @param CreateInvoiceRequest $body (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function createInvoiceRequest($body)
    {
        // verify the required parameter 'body' is set
        if ($body === null || (is_array($body) && count($body) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $body when calling createInvoice'
            );
        }

        $resourcePath = '/v1.0/invoices';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // body params
        $_tempBody = null;
        if (isset($body)) {
            $_tempBody = $body;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'POST';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getCurrencies
     *
     *
     * @return GetCurrenciesResponse
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getCurrencies()
    {
        list($response) = $this->getCurrenciesWithHttpInfo();
        return $response;
    }

    /**
     * Operation getCurrenciesWithHttpInfo
     *
     *
     * @return array of ResponseListString, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getCurrenciesWithHttpInfo()
    {
        $returnType = '\UniPayment\Client\Model\GetCurrenciesResponse';
        $request = $this->getCurrenciesRequest();

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseListString',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getCurrenciesAsync
     *
     *
     *
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getCurrenciesAsync()
    {
        return $this->getCurrenciesAsyncWithHttpInfo()
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getCurrenciesAsyncWithHttpInfo
     *
     *
     *
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getCurrenciesAsyncWithHttpInfo()
    {
        $returnType = 'ResponseListString';
        $request = $this->getCurrenciesRequest();

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getCurrencies'
     *
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function getCurrenciesRequest()
    {

        $resourcePath = '/v1.0/currencies';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getExchangeRateByCurrencyPair
     *
     * @param string $fiat_currency fiat_currency (required)
     * @param string $crypto_currency crypto_currency (required)
     *
     * @return GetExchangeRateByCurrencyPairResponse
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getExchangeRateByCurrencyPair($fiat_currency, $crypto_currency)
    {
        list($response) = $this->getExchangeRateByCurrencyPairWithHttpInfo($fiat_currency, $crypto_currency);
        return $response;
    }

    /**
     * Operation getExchangeRateByCurrencyPairWithHttpInfo
     *
     * @param string $fiat_currency (required)
     * @param string $crypto_currency (required)
     *
     * @return array of ResponseExchangeRate, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getExchangeRateByCurrencyPairWithHttpInfo($fiat_currency, $crypto_currency)
    {
        $returnType = '\UniPayment\Client\Model\GetExchangeRateByCurrencyPairResponse';
        $request = $this->getExchangeRateByCurrencyPairRequest($fiat_currency, $crypto_currency);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseExchangeRate',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getExchangeRateByCurrencyPairAsync
     *
     *
     *
     * @param string $fiat_currency (required)
     * @param string $crypto_currency (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getExchangeRateByCurrencyPairAsync($fiat_currency, $crypto_currency)
    {
        return $this->getExchangeRateByCurrencyPairAsyncWithHttpInfo($fiat_currency, $crypto_currency)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getExchangeRateByCurrencyPairAsyncWithHttpInfo
     *
     *
     *
     * @param string $fiat_currency (required)
     * @param string $crypto_currency (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getExchangeRateByCurrencyPairAsyncWithHttpInfo($fiat_currency, $crypto_currency)
    {
        $returnType = 'ResponseExchangeRate';
        $request = $this->getExchangeRateByCurrencyPairRequest($fiat_currency, $crypto_currency);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getExchangeRateByCurrencyPair'
     *
     * @param string $fiat_currency (required)
     * @param string $crypto_currency (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function getExchangeRateByCurrencyPairRequest($fiat_currency, $crypto_currency)
    {
        // verify the required parameter 'fiat_currency' is set
        if ($fiat_currency === null || (is_array($fiat_currency) && count($fiat_currency) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $fiat_currency when calling getExchangeRateByCurrencyPair'
            );
        }
        // verify the required parameter 'crypto_currency' is set
        if ($crypto_currency === null || (is_array($crypto_currency) && count($crypto_currency) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $crypto_currency when calling getExchangeRateByCurrencyPair'
            );
        }

        $resourcePath = '/v1.0/rates/{fiatCurrency}/{cryptoCurrency}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // path params
        if ($fiat_currency !== null) {
            $resourcePath = str_replace(
                '{' . 'fiatCurrency' . '}',
                ObjectSerializer::toPathValue($fiat_currency),
                $resourcePath
            );
        }
        // path params
        if ($crypto_currency !== null) {
            $resourcePath = str_replace(
                '{' . 'cryptoCurrency' . '}',
                ObjectSerializer::toPathValue($crypto_currency),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getExchangeRateByFiatCurrency
     *
     * @param string $fiat_currency fiat_currency (required)
     *
     * @return GetExchangeRateByFiatCurrencyResponse
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getExchangeRateByFiatCurrency($fiat_currency)
    {
        list($response) = $this->getExchangeRateByFiatCurrencyWithHttpInfo($fiat_currency);
        return $response;
    }

    /**
     * Operation getExchangeRateByFiatCurrencyWithHttpInfo
     *
     * @param string $fiat_currency (required)
     *
     * @return array of GetExchangeRateByFiatCurrencyResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getExchangeRateByFiatCurrencyWithHttpInfo($fiat_currency)
    {
        $returnType = '\UniPayment\Client\Model\GetExchangeRateByFiatCurrencyResponse';
        $request = $this->getExchangeRateByFiatCurrencyRequest($fiat_currency);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseListExchangeRate',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getExchangeRateByFiatCurrencyAsync
     *
     *
     *
     * @param string $fiat_currency (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getExchangeRateByFiatCurrencyAsync($fiat_currency)
    {
        return $this->getExchangeRateByFiatCurrencyAsyncWithHttpInfo($fiat_currency)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getExchangeRateByFiatCurrencyAsyncWithHttpInfo
     *
     *
     *
     * @param string $fiat_currency (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getExchangeRateByFiatCurrencyAsyncWithHttpInfo($fiat_currency)
    {
        $returnType = 'ResponseListExchangeRate';
        $request = $this->getExchangeRateByFiatCurrencyRequest($fiat_currency);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getExchangeRateByFiatCurrency'
     *
     * @param string $fiat_currency (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function getExchangeRateByFiatCurrencyRequest($fiat_currency)
    {
        // verify the required parameter 'fiat_currency' is set
        if ($fiat_currency === null || (is_array($fiat_currency) && count($fiat_currency) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $fiat_currency when calling getExchangeRateByFiatCurrency'
            );
        }

        $resourcePath = '/v1.0/rates/{fiatCurrency}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // path params
        if ($fiat_currency !== null) {
            $resourcePath = str_replace(
                '{' . 'fiatCurrency' . '}',
                ObjectSerializer::toPathValue($fiat_currency),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getInvoiceById
     *
     * @param string $invoice_id invoice_id (required)
     *
     * @return GetInvoiceByIdResponse
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getInvoiceById($invoice_id)
    {
        list($response) = $this->getInvoiceByIdWithHttpInfo($invoice_id);
        return $response;
    }

    /**
     * Operation getInvoiceByIdWithHttpInfo
     *
     * @param string $invoice_id (required)
     *
     * @return array of ResponseInvoiceDetailModel, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function getInvoiceByIdWithHttpInfo($invoice_id)
    {
        $returnType = '\UniPayment\Client\Model\GetInvoiceByIdResponse';
        $request = $this->getInvoiceByIdRequest($invoice_id);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseInvoiceDetailModel',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getInvoiceByIdAsync
     *
     *
     *
     * @param string $invoice_id (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getInvoiceByIdAsync($invoice_id)
    {
        return $this->getInvoiceByIdAsyncWithHttpInfo($invoice_id)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getInvoiceByIdAsyncWithHttpInfo
     *
     *
     *
     * @param string $invoice_id (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function getInvoiceByIdAsyncWithHttpInfo($invoice_id)
    {
        $returnType = 'ResponseInvoiceDetailModel';
        $request = $this->getInvoiceByIdRequest($invoice_id);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getInvoiceById'
     *
     * @param string $invoice_id (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function getInvoiceByIdRequest($invoice_id)
    {
        // verify the required parameter 'invoice_id' is set
        if ($invoice_id === null || (is_array($invoice_id) && count($invoice_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $invoice_id when calling getInvoiceById'
            );
        }

        $resourcePath = '/v1.0/invoices/{invoiceId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // path params
        if ($invoice_id !== null) {
            $resourcePath = str_replace(
                '{' . 'invoiceId' . '}',
                ObjectSerializer::toPathValue($invoice_id),
                $resourcePath
            );
        }

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation queryInvoices
     *
     * @param QueryInvoiceRequest $query_invoice_request query_invoice_request (required)
     *
     * @return QueryInvoiceResponse
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function queryInvoices($query_invoice_request)
    {
        list($response) = $this->queryInvoicesWithHttpInfo($query_invoice_request);
        return $response;
    }

    /**
     * Operation queryInvoicesWithHttpInfo
     *
     * @param QueryInvoiceRequest $query_invoice_request (required)
     *
     * @return array of ResponseQueryResultInvoiceModel, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function queryInvoicesWithHttpInfo($query_invoice_request)
    {
        $returnType = '\UniPayment\Client\Model\QueryInvoiceResponse';
        $request = $this->queryInvoicesRequest($query_invoice_request);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseQueryResultInvoiceModel',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation queryInvoicesAsync
     *
     *
     *
     * @param QueryInvoiceRequest $query_invoice_request (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function queryInvoicesAsync($query_invoice_request)
    {
        return $this->queryInvoicesAsyncWithHttpInfo($query_invoice_request)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation queryInvoicesAsyncWithHttpInfo
     *
     *
     *
     * @param QueryInvoiceRequest $query_invoice_request (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function queryInvoicesAsyncWithHttpInfo($query_invoice_request)
    {
        $returnType = 'QueryInvoiceResponse';
        $request = $this->queryInvoicesRequest($query_invoice_request);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'queryInvoices'
     *
     * @param QueryInvoiceRequest $query_invoice_request (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function queryInvoicesRequest(QueryInvoiceRequest $query_invoice_request)
    {
        // verify the required parameter 'query_invoice_request' is set
        if ($query_invoice_request === null || (is_array($query_invoice_request) && count($query_invoice_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $query_invoice_request when calling queryInvoices'
            );
        }

        $resourcePath = '/v1.0/invoices';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        foreach ($query_invoice_request->getContainer() as $key => $value) {
            if ($value != null) {
                $queryParams[$key] = $value;
            }
        }

        $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
        $queryParams['rd'] = $date_utc->format('YmdHisu');

        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation queryIps
     *
     *
     * @return ResponseListString
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function queryIps()
    {
        list($response) = $this->queryIpsWithHttpInfo();
        return $response;
    }

    /**
     * Operation queryIpsWithHttpInfo
     *
     *
     * @return array of QueryIpsResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException on non-2xx response
     */
    public function queryIpsWithHttpInfo()
    {
        $returnType = '\UniPayment\Client\Model\QueryIpsResponse';
        $request = $this->queryIpsRequest();

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseListString',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation queryIpsAsync
     *
     *
     *
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function queryIpsAsync()
    {
        return $this->queryIpsAsyncWithHttpInfo()
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation queryIpsAsyncWithHttpInfo
     *
     *
     *
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function queryIpsAsyncWithHttpInfo()
    {
        $returnType = 'ResponseListString';
        $request = $this->queryIpsRequest();

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'queryIps'
     *
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function queryIpsRequest()
    {

        $resourcePath = '/v1.0/ips';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'GET';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;


        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation checkIpnAsync
     *
     *
     *
     * @param ipn notify $body (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function checkIpnAsync($body)
    {
        return $this->checkIpnAsyncWithHttpInfo($body)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation checkIpnAsyncWithHttpInfo
     *
     *
     *
     * @param ipn notify $body (required)
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function checkIpnAsyncWithHttpInfo($body)
    {
        $returnType = 'ResponseCheckIpnResponse';
        $request = $this->checkIpnRequest($body);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

     /**
     * Operation checkIpn
     *
     * @param ipn notify $body body (required)
     *
     * @return CheckIpnResponse
     * @throws \InvalidArgumentException
     * @throws ApiException|\GuzzleHttp\Exception\GuzzleException on non-2xx response
     */
    public function checkIpn($body)
    {
        list($response) = $this->checkIpnWithHttpInfo($body);
        return $response;
    }

    /**
     * Operation checkIpnResponseWithHttpInfo
     *
     * @param ipn notify $body (required)
     *
     * @return code msg, HTTP status code, HTTP response headers (array of strings)
     * @throws \InvalidArgumentException
     * @throws ApiException|\GuzzleHttp\Exception\GuzzleException on non-2xx response
     */
    public function checkIpnWithHttpInfo($body)
    {
        $returnType = '\UniPayment\Client\Model\CheckIpnResponse';
        $request = $this->checkIpnRequest($body);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'ResponseCheckIpnResponse',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Create request for operation 'checkIpn'
     *
     * @param checkIpnRequest $body (required)
     *
     * @return \GuzzleHttp\Psr7\Request
     * @throws \InvalidArgumentException
     */
    protected function checkIpnRequest($body)
    {
        // verify the required parameter 'body' is set
        if ($body === null || (is_array($body) && count($body) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $body when calling checkIpn'
            );
        }

        $resourcePath = '/v1.0/ipn';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // body params
        $_tempBody = null;
        if (isset($body)) {
            $_tempBody = $body;
        }

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['*/*']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['*/*'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);

        $url = $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : '');
        $requestMethod = 'POST';
        $authSignature = $this->signRequest(
            $this->config->getClientId(),
            $this->config->getClientSecret(),
            $url,
            $requestMethod,
            $httpBody
        );
        $headers['Authorization'] = 'Hmac ' . $authSignature;

        return new Request(
            $requestMethod,
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @return array of http client options
     * @throws \RuntimeException on file opening failure
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }

    /**
     * Sign Request
     * @param $clientId - Client ID
     * @param $clientSecret - Client Secret
     * @param $uri - Request URI
     * @param $requestHttpMethod - HTTP Method
     * @param $body - Request Body
     * @return string Hash String
     */
    protected function signRequest($clientId, $clientSecret, $uri, $requestHttpMethod, $body)
    {
        $requestUri = urlencode(strtolower($uri));
        $requestContentBase64String = '';
        if ($body !== '') {
            $hashedBody = md5($body, true);
            $requestContentBase64String = base64_encode($hashedBody);
        }
        $requestTimeStamp = time();
        $nonce = str_replace('-', "", uniqid(32));
        $signatureRawData = $clientId . $requestHttpMethod . $requestUri . $requestTimeStamp . $nonce
            . $requestContentBase64String;
        $signature = hash_hmac('sha256', $signatureRawData, $clientSecret, true);
        return $clientId . ":" . base64_encode($signature) . ":" . $nonce . ":" . $requestTimeStamp;
    }
}
