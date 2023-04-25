<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Jobs\ChangePasswordJob;
use App\Jobs\ForgotPasswordJob;
use App\Models\EducationalStaff;
use App\Models\Seller;
use App\Services\ActivityLogService;
use App\Services\SellerService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    private $sellerService;

    const TITLE = 'Edule';


    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;

    }//end __construct()


    /**
     * Render login page
     *
     * @return void
     */
    public function getLogin()
    {
        return view('auth.login', ['title' => self::TITLE]);

    }//end getLogin()


    /**
     * Handle login
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postLogin(Request $request)
    {
        try {
            $credentials = ([
                'email'    => $request->email,
                'password' => $request->password,
            ]);
            $auth = Auth::guard('seller')->attempt($credentials);

            if ($auth) {
                Auth::logoutOtherDevices($request->password);
                $this->limiter()->clear(sha1($request->email));
                ActivityLogService::info('login', Auth('seller')->user()->id, 'Login successful');
                $seller = collect(Auth::user())->except(['password']);
                return $this->responseSuccess($seller);
            }

            $this->incrementLoginAttempts($request);
            return $this->responseError(trans('auth.failed_login'), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            ActivityLogService::error('login', $request->email, $e->getMessage());
            return $this->responseError($e->getMessage());
        }//end try

    }//end postLogin()


    /**
     * Render change password page
     *
     * @return void
     */
    public function getChangePassword()
    {
        $this->authorize('view', Auth::user());
        return view('auth.change-password', ['title' => self::TITLE]);

    }//end getChangePassword()


    /**
     * Render create password page
     *
     * @return void
     */
    public  function getCreatePassword()
    {
        return view('auth.change-password', ['title' => self::TITLE]);

    }//end getCreatePassword()


    /**
     * Render create password page
     *
     * @return void
     */
    public function postCreatePassword(Request $request)
    {
        try {
            $this->sellerService->createPassword($request);
            return $this->responseJson(Response::HTTP_OK, 'success');
        } catch (\Exception $exception) {
            return $this->responseJson(Response::HTTP_NOT_FOUND, 'error', $exception->getMessage());
        }

    }//end postCreatePassword()


    /**
     * Handle active done
     *
     * @return void
     */
    public function activeDone()
    {
        return view('auth.active-done', ['title' => self::TITLE]);

    }//end activeDone()


    /**
     * Handle change password
     *
     * @param ChangePasswordRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postChangePassword(ChangePasswordRequest $request)
    {
        $id = Auth::id();
        $seller = $this->sellerService->findById($id);
        if ($seller) {
            $seller->password = Hash::make($request->password);
            $seller->save();
            // refresh session with new credential
            Auth::login($seller);
            ChangePasswordJob::dispatch($seller);
            return $this->responseSuccess();
        }

        return $this->responseError();

    }//end postChangePassword()


    /**
     * Render change password page
     *
     * @return void
     */
    public function getForgotPassword()
    {
        return view('auth.forgot-password', ['title' => self::TITLE]);

    }//end getForgotPassword()


    /**
     * Handle forgot password
     *
     * @return void
     */
    public function postForgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request['email'];

            $token = Str::random(64).$email.time();

            $response = $this->sellerService->forgotPassword($email, $token);

            if ($response) {
                $seller = Seller::where('email', $email)->first();
                $urlRedirect = route('get-reset-password')."?token=$token";
                ForgotPasswordJob::dispatch($email, $seller, $urlRedirect);
            }

            ActivityLogService::info('forgot password', $email, 'Request forgot password');
            return $this->responseSuccess();
        } catch (\Exception $exception) {
            ActivityLogService::error('forgot password', $email, $exception->getMessage());
            return $this->responseError($exception->getMessage());
        }//end try

    }//end postForgotPassword()


    /**
     * Render reset password page
     *
     * @return void
     */
    public function getResetPassword(Request $request)
    {
        $checkToken = $this->sellerService->checkTokenValid($request);

        if (!$checkToken) {
            return view('auth.login', ['title' => self::TITLE]);
        }

        return view('auth.reset-password', ['title' => self::TITLE]);

    }//end getResetPassword()


    /**
     * Render active seller
     *
     * @return void
     */
    public function getActiveSeller(Request $request)
    {
        $checkToken = $this->sellerService->checkTokenValid($request);
        if (!$checkToken) {
            return view('auth.login', ['title' => self::TITLE]);
        }

        return view('auth.create-password', ['title' => self::TITLE]);

    }//end getActiveSeller()


    /**
     * Handle reset password
     *
     * @return void
     */
    public function postResetPassword(Request $request)
    {
        try {
            $this->sellerService->resetPassword($request);
            ActivityLogService::info('reset password', $request->email, 'Password reset successful');
            return $this->responseSuccess();
        } catch (\Exception $exception) {
            ActivityLogService::error('reset password', $request->email, $exception->getMessage());
            return $this->responseError($exception->getMessage());
        }

    }//end postResetPassword()


    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        ActivityLogService::info('logout', Auth('seller')->user()->id, 'Logout account');

        Auth()->guard('seller')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(route('login'));

    }//end logout()


}//end class
