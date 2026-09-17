<?php
namespace skeeks\cms\shop\gpd;

use yii\httpclient\Client;

/** Separate additive transport; the existing v1 component is never mutated. */
final class CatalogTransport implements CatalogTransportInterface
{
    private $url;
    private $key;
    public function __construct(string $url, string $key)
    {
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || $key === '') {
            throw new ProtocolException('invalid_connection_configuration');
        }
        $this->url = rtrim($url, '/');
        $this->key = $key;
    }
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $allowed = ['status'=>'GET', 'bootstrap'=>'POST', 'manifest'=>'GET', 'changes'=>'GET', 'batch'=>'POST'];
        if (($allowed[$endpoint] ?? null) !== $method) throw new ProtocolException('invalid_request');
        try {
            $client = new Client();
            $request = $client->createRequest()->setMethod($method)->setUrl($this->url.'/sync/'.$endpoint)
                ->setHeaders(['Authorization'=>$this->key, 'Accept'=>'application/json'])
                ->setFormat(Client::FORMAT_JSON)->setOptions(['timeout'=>20, 'maxRedirects'=>0])->setData($data);
            $response = $request->send();
            if (strlen($response->content) > 5 * 1024 * 1024) throw new ProtocolException('response_too_large');
            $body = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);
            if (!$response->isOk) {
                $reason = $body['error']['code'] ?? '';
                $known = ['cursor_expired','snapshot_expired','publication_pending','catalog_not_ready','too_many_bootstrap_sessions','invalid_cursor','batch_too_large'];
                throw new ProtocolException(in_array($reason, $known, true) ? $reason : 'http_'.(int)$response->statusCode);
            }
            if (!is_array($body) || isset($body['error'])) throw new ProtocolException('invalid_response');
            return $body;
        } catch (ProtocolException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // The underlying HTTP exception can contain Authorization or a cursor URL.
            throw new ProtocolException('transport_failed');
        }
    }
}
