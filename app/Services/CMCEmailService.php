<?php
namespace App\Services;

use GuzzleHttp\Client;

class CMCEmailService 
{
    private $client;
    protected $base_url;
    protected $request = [];

    public function __construct()
    {
        $this->client = new Client();
        $this->base_url = env('CMC_BASE_URL');
        $this->request["json"] = [
            "api_user" => env('CMC_API_USER'),
            "api_key" => env('CMC_API_KEY')
        ];

    }

    public function sendEmail($email)
    {
        $this->request["json"]["from"] = $email['from'];
        $this->request["json"]["to"] = $email['to'];
        $this->request["json"]["subject"] = $email['subject'];
        $this->request["json"]["text"] = $email['content'];

        try {
            $res = $this->client->request("POST", $this->base_url . "/emails/send.json", $this->request);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents());
            }
        }
    }

}

?>