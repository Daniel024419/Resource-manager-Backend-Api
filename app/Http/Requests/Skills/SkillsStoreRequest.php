<?php

namespace App\Http\Requests\Skills;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class SkillsStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'skills' => ['required', 'array', 'max:12288'],
            'skills.*.specializationId' => ['required', 'exists:specializations,id'],
            'skills.*.name' => ['required', 'string', 'max:255'],
            'skills.*.rating' => ['required', 'numeric', 'min:0', 'max:5']
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'skills.required' => 'Please provide at least one skill.',
            'skills.array' => 'The skills must be provided as an array.',
            'skills.max' => 'The maximum number of skills allowed is :max.',
            'skills.*.specializationId.required' => 'Each skill must be associated with a specific specialization.',
            'skills.*.specializationId.exists' => 'The selected specialization does not exist.',
            'skills.*.name.required' => 'Each skill must have a name.',
            'skills.*.name.string' => 'The skill name must be a string.',
            'skills.*.name.max' => 'The skill name may not exceed :max characters.',
            'skills.*.rating.required' => 'Each skill must have a rating.',
            'skills.*.rating.numeric' => 'The skill rating must be a number.',
            'skills.*.rating.min' => 'The skill rating must be at least :min.',
            'skills.*.rating.max' => 'The skill rating may not exceed :max.',
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
}
