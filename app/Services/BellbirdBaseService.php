<?php

namespace App\Services;
use GuzzleHttp\Client;

class BellbirdBaseService
{
    const SCOPE_CREATE_MEETING ='createMeetingBookings editMeetingBookings viewMaterials viewMeetingBookings';
    const CREATE_MEETING_BOOKING ='/meeting_bookings';
    
    public function generateToken()
    {
        $this->client = new Client([
            'auth' => [env('BELLBIRD_CLIENT_ID'), env('BELLBIRD_SECRET')]
        ]);

        $this->options['json'] = [
           'grant_type' => env('BELLBIRD_GRANT_TYPE'),
           'scope' => self::SCOPE_CREATE_MEETING
        ];

        $generateTokenUri = env('BELLBIRD_TOKEN_URL');
        $res = $this->client->request('POST', $generateTokenUri, $this->options);
        $res = json_decode($res->getBody());

        return $res;
    }

}