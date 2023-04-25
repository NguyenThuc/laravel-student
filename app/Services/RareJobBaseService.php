<?php

namespace App\Services;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class RareJobBaseService
{
    const CATEGORY_TUTOR = '02';
    const CATEGORY_STUDENT = '01';
    const CATEGORY_TEACHER = '04';
    const CATEGORY_ADMIN = '90';
    const GROUP_ID = '01';
    const GROUP_ID_ADMIN = '01';
    const PRODUCT_ID = '006';
    const EU_PRODUCT_ID = '006';
    const RAREJOB_PRODUCT_ID = "004";
    const ROLE_STUDENT = 'student';
    const ROLE_TUTOR = 'tutor';
    const ROLE_ADMIN = 'admin';

    protected $client;
    protected $base_url;
    protected $options = [];

    protected $api_generate_token = "/token";
    protected $api_auth = "/auth";

    public function __construct()
    {
        $this->client = new Client();
        $this->options['headers'] = ['X-Api-Key' => env('RAREJOB_API_KEY')];
    }

    protected function getAuthToken()
    {
        return Session::get("authToken");
    }

    public function generateToken($actor_id, $category, $product_id = null)
    {
        // create auth
        $role = '';
        $groupId ='';
        if ($category == self::CATEGORY_STUDENT) {
            $role = self::ROLE_STUDENT;
            $groupId = self::GROUP_ID;
        } else if ($category == self::CATEGORY_TUTOR) {
            $role = self::ROLE_TUTOR;
            $groupId = self::GROUP_ID;
        } else if ($category == self::CATEGORY_TEACHER) {
            $role = self::ROLE_ADMIN;
            $groupId = self::GROUP_ID_ADMIN;
        }else if ($category == self::CATEGORY_ADMIN) {
            $role = self::ROLE_ADMIN;
            $groupId = self::GROUP_ID_ADMIN;
        }

        $uuid = $category . '-' . $groupId . '-' . $actor_id;
        $this->options['json'] = ['uuid' => $uuid, 'role' => $role];
        $authUri = env('RAREJOB_AUTH_BASE_URL') . $this->api_auth;
        $res = $this->client->request('POST', $authUri, $this->options);

        if ($res->getStatusCode() != 201) {
            return false;
        }

        // generate token
        $this->options['json'] = [
            "product_id" => $product_id ?? self::PRODUCT_ID,
            "actor_user_category_id" => $category,
            "actor_user_group_id" => $groupId,
            "actor_user_id" => "$actor_id"
        ];

        $generateTokenUri = env('RAREJOB_AUTH_BASE_URL') . $this->api_generate_token;
        $res = $this->client->request('POST', $generateTokenUri, $this->options);
        $res = json_decode($res->getBody());

        return $res->token;
    }

    protected function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }

    protected function request($method, $api)
    {
        // generate transaction ID
        $this->options['headers']['X-Transaction-Id'] = Str::random(11);

        $logData = $this->maskSensitveData([
            'api' => $method . ' ' . $this->base_url . $api,
            'request' => $this->options
        ]);

        try {
            $res = $this->client->request($method, $this->base_url . $api, $this->options);
            $responseCode = $res->getStatusCode();
            $res = json_decode($res->getBody());

            if (($responseCode != 200 || $responseCode != 201) && isset($res->errors)) {
                $logData['error'] = implode(', ', $res->errors);
                $logData['code'] = $res->code;
                $logData['response'] = $res;

                Log::error(json_encode($logData));
            }

            Log::info(json_encode($logData));
            return $res;
        } catch (RequestException $e) {
            $logData['error'] = $e->getMessage();
            $logData['response'] = json_decode($e->getResponse()->getBody()->getContents());
            Log::error(json_encode($logData));

            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents());
            }
        }
    }

    protected function maskSensitveData($logData)
    {
        if (isset($logData['request']['json']['email'])) {
            $logData['request']['json']['email'] = maskEmailAddress($logData['request']['json']['email']);
        }

        if (isset($logData['request']['json']['password'])) {
            $logData['request']['json']['password'] = maskPassword($logData['request']['json']['password']);
        }

        if (isset($logData['request']['headers']['Authorization'])) {
            unset($logData['request']['headers']['Authorization']);
        }

        // remove API key
        unset($logData['request']['headers']['X-Api-Key']);



        return $logData;
    }
}