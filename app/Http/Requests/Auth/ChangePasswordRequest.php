<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Controllers\Controller;
use App\Rules\Auth\CheckPassword;

class ChangePasswordRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;

    }//end authorize()


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => 'current_password:seller',
            'password'         => [
                'required',
                'min:8',
                'max:32',
                'same:confirm_password',
                new CheckPassword,
            ],
            'confirm_password' => 'required|min:8|max:32',
        ];

    }//end rules()


    public function attributes()
    {
        return [
            'current_password' => '現在のパスワード',
            'password'         => '新しいパスワード',
            'confirm_password' => '新しいパスワード（もう一度入力下さい）',
        ];

    }//end attributes()


    public function messages()
    {
        return ['current_password.current_password' => __('auth.incorrect_password')];

    }//end messages()


}//end class
