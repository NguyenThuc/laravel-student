<?php

namespace App\Http\Requests\EducationalStaff;

use App\Models\EducationalStaff;
use App\Services\EducationalStaffService;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{

    protected $educationalStaffService;


    public function __construct(EducationalStaffService $educationalStaffService)
    {
        parent::__construct();
        $this->educationalStaffService = $educationalStaffService;

    }//end __construct()


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $staffId = $this->route('educational_staff_id');
        $staff = $this->educationalStaffService->findById($staffId);
        if ($staff) {
            return $this->user()->can('educational_staff.view', $staff);
        }

        abort(404);

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
