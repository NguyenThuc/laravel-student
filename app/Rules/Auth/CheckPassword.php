<?php
namespace App\Rules\Auth;

use Illuminate\Contracts\Validation\Rule;

class CheckPassword implements Rule
{


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //

    }//end __construct()


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $pattern = '/^(?=.*?[a-zA-Z])(?=.*?[0-9])[a-zA-Z0-9_#!%^&*-]{8,32}$|^(?=.*?)(?=.*?[a-zA-Z0-9])(?=.*?[_#!%^&*-])[a-zA-Z0-9_#!%^&*-]{8,32}$/';
        return preg_match($pattern, $value);

    }//end passes()


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'パスワードはアルファベット(半角英大文字、半角英小文字)、数字、記号記号!#%^&*-_の内、2種類以上の複合で作成してください';

    }//end message()


}//end class
