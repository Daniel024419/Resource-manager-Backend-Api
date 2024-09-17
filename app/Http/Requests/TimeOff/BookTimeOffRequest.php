<?php

namespace App\Http\Requests\TimeOff;

use App\Enums\Roles;
use App\Models\V1\TimeOff\TimeOffType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class BookTimeOffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->leaveType();
        $proofRule = $this->proofRequired() ? ['required', 'file'] : [];

        $endDateRules = ['required', 'date'];

        if ($type && $type->duration !== null) {
            $startDate = $this->input('startDate');
            $maxEndDate = now()->parse($startDate)->addDays($type->duration)->toDateString();
            $endDateRules[] = "before_or_equal:$maxEndDate";
        }

        return [
            'leaveType' => ['required', 'exists:time_off_types,refId'],
            'startDate' => ['required', 'date'],
            'endDate' => $endDateRules,
            'details' => ['required', 'string'],
            'proof' => $proofRule,
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
            $leaveType = $this->leaveType();
            if ($leaveType) {
                $this->merge([
                    'leaveTypeId' => $leaveType->id,
                    'leaveTypeName' => $leaveType->name,
                    'requiresProve' => $leaveType->showProof
                ]);
            }
        });
        
    }

    /**
     * Get the leave type based on the provided type.
     *
     * @return \App\Models\V1\TimeOff\TimeOffType|null
     */
    protected function leaveType(): ?TimeOffType
    {
        return TimeOffType::where('refId', $this->input('leaveType'))->first();
    }

    /**
     * Dynamically determine if proof is required based on the type.
     *
     * @return bool
     */
    protected function proofRequired(): bool
    {
        $type = $this->leaveType();

        return $type ? $type->showProof : false;
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
