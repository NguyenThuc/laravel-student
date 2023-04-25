<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;


class RareJobStudentService extends RareJobBaseService
{
    private $api_get_student = "/students/student/{student_id}";
    private $api_create_student = "/students/signup";
    private $api_login_student = "/students/login";
    private $api_provisional_reg = "/students/provisional_registration";
    private $api_provisional_reg_verify = "/students/provisional_registration/verification";
    private $api_student_profile = "/students/student/{student_id}/profile";
    private $api_subscribe_student = "/students/student/{student_id}/subscribe";
    private $api_password__reset = "/students/reset/password";
    private $api_auth_student = "/students/me";
    private $date_format_utc = "Y-m-d\TH:i:s\Z";
    private $student_profile_keys = [ "lastName", "firstName", "nickname"];

    public function __construct()
    {
        $this->setBaseUrl(env('RAREJOB_STUDENT_BASE_URL'));
        parent::__construct();
    }

    public function find($student_id)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        return $this->request('GET', str_replace("{student_id}", $student_id, $this->api_get_student));
    }

    public function provisional_registration($data)
    {
        $this->options['json'] = [
            'category_id' => self::CATEGORY_STUDENT,
            'product_id' => parent::EU_PRODUCT_ID,
            'group_id' => self::GROUP_ID,
            'email' => $data['email'],
            'password' => $data['password'],
            'name' => $data['name']
        ];
        return $this->request('POST', $this->api_provisional_reg);
    }

    public function provisional_registration_verify($verifyToken)
    {
        $this->options['headers']['X-Provisional-Token'] = $verifyToken;

        return $this->request('POST', $this->api_provisional_reg_verify);
    }

    public function create($email, $password)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        $this->options['json'] = [
            'category_id' => self::CATEGORY_STUDENT,
            'group_id' => self::GROUP_ID,
            'email' => $email,
            'password' => $password
        ];
       
        return $this->request('POST', $this->api_create_student);
    }

    public function subscribe($student_id)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        // subscribe api params
        $this->options['json'] = ['product_id' => parent::EU_PRODUCT_ID];

        $subscribeApi = str_replace('{student_id}', $student_id, $this->api_subscribe_student);
        return $this->request('POST', $subscribeApi);
    }


    public function unsubscribe($student_id)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        // subscribe api params
        $this->options['json'] = ['product_id' => parent::EU_PRODUCT_ID];

        $subscribeApi = str_replace('{student_id}', $student_id, $this->api_subscribe_student);
        return $this->request('DELETE', $subscribeApi);
    }

    public function login($email, $password)
    {
        $this->options['json'] = [
            'category_id' => self::CATEGORY_STUDENT,
            'group_id' => self::GROUP_ID,
            'product_id' => self::PRODUCT_ID,
            'email' => $email,
            'password' => $password
        ];

        return $this->request('POST', $this->api_login_student);
    }

    public function getCurrentStudent($studentId)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);
        return $this->request('GET', $this->api_auth_student);
    }

    public function resetLink($email)
    {
        $expire_date = date($this->date_format_utc, strtotime(date($this->date_format_utc) . ' +1 day'));
        $this->options["json"] = [
            "expired_at" => $expire_date,
            "email" => $email
        ];

        return $this->request('POST', $this->api_password__reset);
    }

    public function resetPassword($password, $token)
    {
        $this->options["json"] = [
            "password" => $password,
            "token" => $token
        ];
        return $this->request('PUT', $this->api_password__reset);
    }

    public function getStudentById($studentId)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);
        return $this->request('GET', "/students/student/" . $studentId);
    }

    public function getStudentProfile($studentId, $productId = self::PRODUCT_ID, $token = NULL)
    {
        $productId = $productId == null ? self::PRODUCT_ID : $productId;
        $token = $token ? $token : $this->generateToken($studentId, RareJobBaseService::CATEGORY_STUDENT, $productId);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);

        $params = [
            "product_id" => $productId
        ];

        $queryString = http_build_query($params);

        return $this->request('GET', str_replace('{student_id}', $studentId, $this->api_student_profile . "?"  . $queryString));
    }


    public function updateStudentProfile($request, $id, $token = null)
    {   
        $changes = 0;
        $json = array();
        $studentRarejobProfile = $this->getStudentProfile($id);
        $studentProfile = $studentRarejobProfile?->student_profile;
        $token = $token ? $token : $this->generateToken($id, RareJobBaseService::CATEGORY_STUDENT);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        $gender = $studentProfile?->gender ?? null;
        

        if($gender != $request->gender) {
            $changes += 1;
            $gender = (int)$request->gender;
        }

        $profile = $studentProfile?->profile ?? json_encode(array([]));
        foreach($this->student_profile_keys as $key) {
            $currentPropVal = property_exists($profile, $key) ? $profile->$key : ""; 
            $changes += $currentPropVal != $request[$key] ? 1 : 0;
            $json = array_merge($json, [ $key => $request[$key] ]);
        }  

        $this->options['json'] = [
            "gender" =>  $gender,
            "product_code" => self::PRODUCT_ID,
            "profile" => $json
        ];
        
        if($changes > 0) {
            return $this->request('PUT', str_replace('{student_id}', $id, $this->api_student_profile));;
        }
        return false;
    }

    public function updateEmail($email, $id)
    {
        $studentRarejobInfo = $this->getCurrentStudent($id); 
        if($studentRarejobInfo?->credential?->email != $email) {
            $token = $this->generateToken($id, RareJobBaseService::CATEGORY_STUDENT);
            $this->options['headers']['Authorization'] = 'Bearer ' . $token;
            $this->options['json'] = [
                "email" => $email
            ];
            return $this->request('PUT', $this->api_auth_student ."/email");
        }

        return false;
    }
}