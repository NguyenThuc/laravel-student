<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckEmail implements Rule
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
        $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        return preg_match($pattern, $value);

    }//end passes()


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'メールアドレスには正しいメールアドレスを入力してください';

    }//end message()


}//end class
