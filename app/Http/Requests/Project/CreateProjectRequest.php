<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectType;
use App\Models\V1\Client\Client;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:255'],
            'clientId' => ['required', 'string', 'exists:clients,clientId'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'projectType' => ['required', 'string', function ($attribute, $value, $fail) {
                try {
                    ProjectType::validateType($value);
                } catch (InvalidArgumentException $e) {
                    $fail($e->getMessage());
                }
            }],
            'billable' => ['required', 'boolean'],
            'skills' => ['required', 'array', 'max:12288'],
            'skills.*.name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $lowercaseValue = strtolower($value);
                    if (!DB::table('skills')->whereRaw('LOWER(name) = ?', [$lowercaseValue])->exists()) {
                        $fail('The selected skill ' . $value . ' does not match any skill.');
                    }
                },
            ],
        ];
    }


    /**
     * Perform additional actions after validation.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clientId = $this->input('clientId');
            $client = Client::where('clientId', $clientId)->first();

            if ($client) {
                $this->merge([
                    'clientId' => $client->id,
                ]);
            }
        });
    }



    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clientId.exists' => 'Client not found, please try again with a different client.',
            'skills.required' => 'Please provide at least one skill.',
            'skills.array' => 'The skills must be provided as an array.',
            'skills.max' => 'The maximum number of skills allowed is :max.',
            'skills.*.name.required' => 'Each skill must have a name.',
            'skills.*.name.string' => 'The skill name must be a string.',
            'skills.*.name.max' => 'The skill name may not exceed :max characters.',
            'skills.*.name.exists' => 'The selected skill does not match any skill.',
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
