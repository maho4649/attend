<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required',
                       'string', 
                       'max:255',
                       function ($attribute, $value, $fail) {
                         if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                          $fail('お名前を入力してください');
                          }
                       },
                    
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class),
                function ($attribute, $value, $fail) {
                   if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                   $fail('メールアドレスを入力してください');
                  }
                 },
                
            ],
            
            'password' => ['required', 
                           'string', 
                           'min:8', 
                           'confirmed',
                           function ($attribute, $value, $fail) use ($input) {

                             if (isset($input['email']) && $value === $input['email']) {
                             $fail('パスワードを入力してください');
                             }
                             if (isset($input['name']) && stripos($value, $input['name']) !== false) {
                             $fail('パスワードを入力してください');
                             }
                            }
            ],
        ], [
              'name.required' => 'お名前を入力してください',
              'email.required' => 'メールアドレスを入力してください',
              'email.email' => 'メールアドレスを入力してください',
              'email.unique' => 'このメールアドレスは既に使われています',
              'password.required' => 'パスワードを入力してください',
              'password.min' => 'パスワードは8文字以上で入力してください',
              'password.confirmed' => 'パスワードと一致しません',])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
