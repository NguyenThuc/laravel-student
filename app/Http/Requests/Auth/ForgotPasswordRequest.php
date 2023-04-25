<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
        return ['email' => 'required|email|exists:sellers'];

    }//end rules()


    public function attributes()
    {
        return ['email' => 'メールアドレス'];

    }//end attributes()


    public function messages()
    {
        return ['email.exists' => __('auth.email_not_exists')];

    }//end messages()


}//end class
