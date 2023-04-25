<?php

namespace App\Services;

class RareJobTutorService extends RareJobBaseService
{
    private $api_login_tutor = "/tutors/login";
    private $api_create_tutor = "/tutors/signup";
    private $api_get_self_tutor = "/tutors/me";
    private $api_email_update_tutor = "/tutors/me/email";
    private $api_password_update_tutor = "/tutors/me/password";
    private $api_password_reset_tutor = "/tutors/reset/password";
    private $api_get_profile_info_tutor = "/tutors/tutor/{tutor_id}/profile";
    private $api_get_teaching_status_tutor = "/tutors/tutor/{tutor_id}/status";
    private $api_unlock_tutor = "/tutors/tutor/{tutor_id}/unlock";
    private $api_subscribe_tutor = "/tutors/tutor/{tutor_id}/subscribe";
    private $api_bank_details_tutor = "/tutors/tutor/{tutor_id}/bank/details";
    private $api_lessons_operator_tutor = "/tutors/tutor/{tutor_id}/lesson/operator";

    public function __construct()
    {
        $this->setBaseUrl(env('RAREJOB_TUTOR_BASE_URL'));
        parent::__construct();
    }

    public function find($tutor_id)
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        return $this->request('GET', str_replace("{tutor_id}", $tutor_id, $this->api_get_profile_info_tutor));
    }

    public function findTutorByLessonOperator($tutor_id)
    {
        $token = $this->generateToken($tutor_id, RareJobBaseService::CATEGORY_TUTOR);
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);

        return $this->request('GET', str_replace("{tutor_id}", $tutor_id, $this->api_lessons_operator_tutor));
    }

    public function getStatus($tutor_id)
    {
        $token = $this->getAuthToken();;
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;

        // remove json params for generate of token
        unset($this->options['json']);

        return $this->request('GET', str_replace("{tutor_id}", $tutor_id, $this->api_get_teaching_status_tutor));
    }

    public function provisional_registration($data)
    {
        $this->options['json'] = [
            'category_id' => self::CATEGORY_TUTOR,
            'product_id' => self::PRODUCT_ID,
            'group_id' => self::GROUP_ID,
            'email' => $data['email'],
            'password' => $data['password'],
            'name' => $data['name'],
            'birthdate' => $data['birthdate'],
            'gender' => $data['gender'],
            'country' => $data['country'],
            'nickname' => $data['nickname'],
            'skype_id' => $data['skype_id'],
            'address' => $data['address'],
            'school' => $data['school'],
            'major' => $data['major'],
            'company' => $data['company'],
            'operator' => $data['operator'],
            'category_id' => $data['category_id'],
            'group_id' => $data['group_id']
        ];

        return $this->request('POST', $this->api_create_tutor);
    }

    public function login($email, $password)
    {
        $this->options['json'] = [
            "category_id" => self::CATEGORY_TUTOR,
            "group_id" => self::GROUP_ID,
            "product_id" => self::RAREJOB_PRODUCT_ID,
            "email" => $email,
            "password" => $password,
            "company" => 1,
        ];

        return $this->request("POST", $this->api_login_tutor);
    }


    public function generateResetPasswordToken($email)
    {
        $this->options['json'] = [
            "email" => $email,
            "expired_at" => date('Y-m-d\TH:i:s\Z', strtotime(now(). ' + 2 days')),
        ];

        return $this->request("POST", $this->api_password_reset_tutor);
    }

    public function resetPassword($token, $passowrd)
    {
        $this->options['json'] = [
            "password" => $passowrd,
            "token" => $token,
        ];

        return $this->request("PUT", $this->api_password_reset_tutor);
    }

    public function getAuthTutor() 
    {
        $token = $this->getAuthToken();
        $this->options['headers']['Authorization'] = 'Bearer ' . $token;
        unset($this->options['json']);

        return $this->request('GET', $this->api_get_self_tutor);
    }
}