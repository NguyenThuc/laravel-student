<?php

namespace App\Http\Requests\EduInstitution;

use App\Models\EducationalInstitution;
use App\Models\Seller;
use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (auth()->user('seller')->role == Seller::ROLE_ADMIN) {
            return true;
        }

        $id = $this->route('id');
        $eduInstitution = EducationalInstitution::find($id);
        if ($eduInstitution) {
            return $this->user()->can('educational_institutions.update', $eduInstitution);
        }

        return false;

    }//end authorize()


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];

    }//end rules()


}//end class
