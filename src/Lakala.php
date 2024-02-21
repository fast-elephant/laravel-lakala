<?php

namespace FastElephant\LaravelLakala;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Lakala
{
    private $client;

    private $sign;

    private $authorization;

    private $timestamp;

    private $nonce;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function post(string $url, array $data)
    {
        if (!Str::startsWith($url, 'http')) {
            $url = config('lakala.api_url_prefix') . $url;
        }

        $this->timestamp = time();

        $this->nonce = Str::random(12);

        $this->sign = $this->makeSign($data);

        $this->authorization = $this->makeAuthorization();

        $response = $this->client->post($url, [
            'headers' => [
                'Authorization' => $this->authorization,
                'Accept' => 'application/json',
            ],
            'json' => $data,
        ]);

        return $response->getBody()->getContents();
    }

    public function valid(Request $request)
    {
        $authorization = $request->header('Authorization');
        $authorization = str_replace(config('lakala.schema') . ' ', '', $authorization);
        $authorization = str_replace(',', '&', $authorization);
        $authorization = str_replace('"', '', $authorization);
        $authorization = parse_str($authorization, $output);

        $output['signature'] = base64_decode($output['signature']);

        $body = $request->getContent();
        $message = $output['timestamp'] . "\n" . $output['nonce_str'] . "\n" . $body . "\n";
        $key = openssl_get_publickey(file_get_contents(config('lakala.api_cert')));
        $flag = openssl_verify($message, $output['signature'], $key, OPENSSL_ALGO_SHA256);
        if (!$flag) {
            throw new \Exception('签名验证失败');
        }

        return $request->all();
    }

    public function success()
    {
        return response()->json(['code' => 'SUCCESS', 'msg' => '执行成功']);
    }

    public function fail($msg = '执行失败')
    {
        return response()->json(['code' => 'FAIL', 'msg' => $msg]);
    }

    private function makeSign(array $data)
    {
        $preSignData = config('lakala.app_id') . "\n" . config('lakala.serial_no') . "\n" . time() . "\n" . $this->nonce . "\n" . json_encode($data) . "\n";
        $priKey = file_get_contents(config('lakala.api_private_key'));
        $pkeyId = openssl_get_privatekey($priKey);
        openssl_sign($preSignData, $signature, $pkeyId, 'SHA256');
        $sign = base64_encode($signature);
        return $sign;
    }

    private function makeAuthorization()
    {
        return sprintf(
            '%s appid="%s",serial_no="%s",timestamp="%s",nonce_str="%s",signature="%s"',
            'LKLAPI-SHA256withRSA',
            config('lakala.app_id'),
            config('lakala.serial_no'),
            $this->timestamp,
            $this->nonce,
            $this->sign
        );
    }
}
