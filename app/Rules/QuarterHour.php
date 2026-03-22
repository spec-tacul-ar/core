<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class QuarterHour implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return fmod((float) $value, 0.25) === 0.0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be made from quarter hour segments.';
    }
}
