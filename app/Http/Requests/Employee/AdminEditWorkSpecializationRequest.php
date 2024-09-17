<?php

namespace App\Http\Requests\Employee;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AdminEditWorkSpecializationRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'refId' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:12288'],
            'specialization' => ['required', 'string', 'max:12288'],
            'skills' => ['required', 'array', 'max:12288'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([$validator->errors()], JsonResponse::HTTP_BAD_REQUEST)
        );
    }


    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->hasFile('profilePicture') && !$this->file('profilePicture')->isValid()) {
                    $validator->errors()->add(
                        'profilePicture',
                        'Please choose a valid profile picture.'
                    );
                } elseif ($this->hasFile('profilePicture') && !in_array($this->file('profilePicture')->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                    $validator->errors()->add(
                        'profilePicture',
                        'Invalid file format. Please choose a valid image (jpg, jpeg, png).'
                    );
                }
            }
        ];
    }
}