<?php
namespace App\Services;

use GuzzleHttp\Client;

/**
 * Class RareJobCommonService
 * @package App\Services
 * References
 * https://git.rarejob.com/app/common-api/wikis/Api-Specification-English
 * http://api.rarejob.org/public/v1.html#specialized-tutor-list-tutor-specialized-get
 */
class RareJobCommonService
{
    public function generateToken()
    {
        $client = new Client();
        $request = ['form_params' => [
            'grant_type' => 'client_credentials',
            'client_id' => 'enviz   ion_edule_tutor',
            'client_secret' => 'projectEU'
        ]];

        $response = $client->request('POST', 'https://stg-api.rarejob.com/token', $request);
        echo $response->getBody();
    }

    public function specialized_tutor()
    {
        $client = new Client();
        $params = [
            'specialized_identifier' => 'SL'
        ];

        $request = [
            'query' => [
                'request' => json_encode($params)
            ],
            'headers' => [
                'Authorization' => 'Bearer 4aa49a254d4b78088645bc8f2800da4e0d13338e'
            ]
        ];

        $response = $client->request('GET', 'https://stg-api.rarejob.com/v1/list/tutor/specialized', $request);
        echo $response->getBody();
    }
}
