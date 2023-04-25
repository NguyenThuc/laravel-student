<?php

namespace App\Http\Requests\Seller;

use App\Models\Seller;
use App\Services\EducationalStaffService;
use App\Services\SellerService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ScreenEditorRequest extends FormRequest
{

    protected $sellerService;


    public function __construct(SellerService $sellerService)
    {
        parent::__construct();
        $this->sellerService = $sellerService;

    }//end __construct()


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $sellerId = $this->route('seller_id');
        if ($sellerId) {
            return $this->user()->can('update', Auth::user());
        }

        return $this->user()->can('create', Auth::user());

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
