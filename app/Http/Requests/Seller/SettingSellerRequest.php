<?php

namespace App\Http\Requests\Seller;

use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckEmail;
use Illuminate\Support\Facades\Auth;

class SettingSellerRequest extends FormRequest
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
        return [
            'name'              => 'required|max:255',
            'role'              => 'required',
            'email'             => [
                'required',
                'max:255',
                new CheckEmail(),
                "unique:sellers,email,$this->id,id,deleted_at,NULL",
            ],
            'educationalInsIds' => 'array',
        ];

    }//end rules()


    public function attributes()
    {
        return [
            'name'              => '氏名',
            'role'              => '権限',
            'email'             => 'メールアドレス',
            'educationalInsIds' => '教育機関・教室',
        ];

    }//end attributes()


}//end class
