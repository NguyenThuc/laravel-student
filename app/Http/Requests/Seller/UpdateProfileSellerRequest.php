<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckEmail;

class UpdateProfileSellerRequest extends FormRequest
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
            'name'  => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                new CheckEmail(),
                "unique:sellers,email,$this->email,email,deleted_at,NULL",
            ],
        ];

    }//end rules()


    public function attributes()
    {
        return [
            'name'  => '氏名',
            'email' => 'メールアドレス',
        ];

    }//end attributes()


}//end class
