<?php

namespace App\Http\Requests\AutoAssign;

use App\Enums\ProjectType;
use App\Models\V1\Client\Client;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AutoAssignRequest extends FormRequest
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
            'skills.*.skill' => ['required', 'string', function ($attribute, $value, $fail) {
                $exists = DB::table('skills')
                    ->where(DB::raw('LOWER(name)'), strtolower($value))
                    ->exists();

                if (!$exists) {
                    $fail("The skill '{$value}' does not match any existing skill.");
                }
            }],
            'skills.*.number' => ['required', 'integer'],
            'workingHours' => ['required', 'integer', 'max:8', 'min:1']
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
            'skills.*.skill.required' => 'Each skill must have a name.',
            'skills.*.skill.string' => 'The skill name must be a string.',
            'skills.*.skill.exists' => 'The selected skill does not match any existing skill.',
            'skills.*.number.required' => 'Please provide the number of people you want to assign to this project.',
            'skills.*.number.integer' => 'The number of people must be a whole number.',
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
