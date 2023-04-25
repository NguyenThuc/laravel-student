<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolCreateRequest extends FormRequest
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
            'schoolName'   => 'required|max:255',
            'teacherName'  => 'required|max:255',
            'teacherEmail' => 'required|max:255|email:rfc,dns|unique:teachers,email,'.$this->id,
        ];

    }//end rules()


    public function attributes()
    {
        return [
            'schoolName'   => '教育機関名',
            'teacherName'  => '教育機関スタッフ名',
            'teacherEmail' => 'メールアドレス',
        ];

    }//end attributes()


}//end class
