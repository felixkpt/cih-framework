<?php

namespace Cih\Framework\Rules;

use App\Models\Core\PasswordHistory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IsValidPassword implements Rule
{
    /**
     * Determine if the Length Validation Rule passes.
     *
     * @var boolean
     */
    public $lengthPasses = true;
    /**
     * Determine if the Length Validation Rule passes.
     *
     * @var boolean
     */
    public $doesNotmatchPreviousPassword = true;

    /**
     * Determine if the Uppercase Validation Rule passes.
     *
     * @var boolean
     */
    public $uppercasePasses = true;

    /**
     * Determine if the Numeric Validation Rule passes.
     *
     * @var boolean
     */
    public $numericPasses = true;

    /**
     * Determine if the Special Character Validation Rule passes.
     *
     * @var boolean
     */
    public $specialCharacterPasses = true;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->lengthPasses = (Str::length($value) >= 8);
//        $this->lengthPasses = (Str::length($value) >= 8 && Str::length($value) <= 15);
        $this->uppercasePasses = (Str::lower($value) !== $value);
        $this->numericPasses = ((bool)preg_match('/[0-9]/', $value));
        $this->specialCharacterPasses = ((bool)preg_match('/[^A-Za-z0-9]/', $value));
        $this->doesNotmatchPreviousPassword = $this->matchesPreviousPassword($value);
        return ($this->lengthPasses && $this->uppercasePasses && $this->numericPasses && $this->specialCharacterPasses && $this->doesNotmatchPreviousPassword);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch (true) {
            case !$this->doesNotmatchPreviousPassword:
                return 'The :attribute should not match your previous passwords.';
            case !$this->lengthPasses:
                return 'The :attribute must have between 8-15  characters';

            case !$this->uppercasePasses
                && $this->numericPasses
                && $this->specialCharacterPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one uppercase character.';

            case !$this->numericPasses
                && $this->uppercasePasses
                && $this->specialCharacterPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one number.';

            case !$this->specialCharacterPasses
                && $this->uppercasePasses
                && $this->numericPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one special character.';

            case !$this->uppercasePasses
                && !$this->numericPasses
                && $this->specialCharacterPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one uppercase character and one number.';

            case !$this->uppercasePasses
                && !$this->specialCharacterPasses
                && $this->numericPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one uppercase character and one special character.';

            case !$this->uppercasePasses
                && !$this->numericPasses
                && !$this->specialCharacterPasses
                && $this->doesNotmatchPreviousPassword:
                return 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, and one special character.';

            default:
                return 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, one special character and should not match your previous passwords.';
        }
    }

    function matchesPreviousPassword($password)
    {
        $state = true;
        $passwordHistories = PasswordHistory::where('user_id', auth()->id())->orderBy('created_at','DESC')->limit(10)->get();
        if (count($passwordHistories) > 0) {
            foreach ($passwordHistories as $history) {
                if ((Hash::check($password, $history->password))) {
                    $state = false;
                }
            }
        } else {
            if (\auth()->check())
                if ((Hash::check($password, auth()->user()->password))) {
                    $state = false;
                }
        }

        return $state;
    }
}
