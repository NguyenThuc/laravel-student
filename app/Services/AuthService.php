<?php

namespace App\Services;

use Carbon\Carbon;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Services\CMCEmailService;
use App\Models\Email;

class AuthService
{
    const CHANGE_STATUS = 0;

    private $cmcEmailService;

    public function __construct(CMCEmailService $cmcEmailService)
    {
        $this->cmcEmailService = $cmcEmailService;
    }

    public function doLogin($credentials)
    {
        return Auth::guard('teacher')->attempt($credentials);
    }

    public function checkExpiration($token)
    {
        $password_resets = DB::table('password_resets')
                ->where('token', $token)
                ->first();

        $now = Carbon::now();

        if($now >= $password_resets->expires_at) {
            return false;
        }

        return true;
    }

    public function storeRequest(Request $request)
    {
        $receiver = $request->email;
        $token = Str::random(64);
        $expiration = Carbon::now()->addHours(1);

        $chnageStatus = Teacher::where('email', $receiver)->get();
        if (!$chnageStatus->isEmpty()){
            $status = $chnageStatus[0]->password_status;
        } else {
            $status = self::CHANGE_STATUS;
        }

        $email = new Email(
            array($receiver),
            "Password Reset Link", 
            "Click this link ". env("APP_URL") ."/password/reset/". $token . "/". $status ." to reset your password."
        );
        $result = $this->cmcEmailService->sendEmail($email->getObjectProperties());

        return DB::table('password_resets')->insert([
            'email' => $receiver, 
            'token' => $token, 
            'expires_at' => $expiration,
            'created_at' => Carbon::now()
        ]);
    }

    public function checkToken($token)
    {
        return  DB::table('password_resets')
            ->where(['token' => $token])
            ->first();
    }

    public function resetPassword($email, $password)
    {
        return Teacher::where('email', $email)
            ->update([
                'password' => Hash::make($password),
                'password_status' => 1
            ]);
    }

    public function removeRequest($email)
    {
        return DB::table('password_resets')
            ->where(['email'=> $email])
            ->delete();
    }
}