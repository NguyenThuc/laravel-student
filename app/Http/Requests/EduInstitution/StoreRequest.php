<?php

namespace App\Http\Requests\EduInstitution;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckEmail;

class StoreRequest extends FormRequest
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
            'name'                   => 'required|max:255',
            'phoneNumber'            => 'required|numeric',
            'mstTextbookCourseIds'   => 'array|required',
            'mstTextbookCourseIds.*' => 'exists:mst_textbook_courses,id',
            'startDate'              => 'required',
            'endDate'                => 'required',
            'teacherEmail'           => [
                'required',
                'max:255',
                new CheckEmail(),
                'unique:educational_staffs,email',
            ],
            'teacherName'            => 'required|max:255',
        ];

    }//end rules()


}//end class
