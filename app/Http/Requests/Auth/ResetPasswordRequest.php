<?php

namespace App\Http\Requests\Auth;

use App\Rules\Auth\CheckPassword;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password'         => '新しいパスワード',
            'confirm_password' => '新しいパスワード（もう一度入力下さい）',
        ];

    }//end attributes()


}//end class
