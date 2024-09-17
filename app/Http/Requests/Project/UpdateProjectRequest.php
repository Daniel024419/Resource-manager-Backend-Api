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

class UpdateProjectRequest extends FormRequest
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
            'projectId' => ['required', 'exists:projects,projectId'],
            'details' => ['required', 'string', 'max:255'],
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
            'clientId' => ['required', 'exists:clients,clientId'],
            'skills' => ['array', 'max:12288'],
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
