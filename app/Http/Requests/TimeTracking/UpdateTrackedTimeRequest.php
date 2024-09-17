<?php

namespace App\Http\Requests\TimeTracking;

use App\Models\V1\Project\EmployeeProject;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Form request for updating tracked time.
 */
class UpdateTrackedTimeRequest extends FormRequest
{
    /**
     * Information about the employee project.
     *
     * @var EmployeeProject
     */
    protected $employeeProjectInfo;

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
     * @return array
     */
    public function rules(): array
    {
        $employeeProject = EmployeeProject::findOrFail($this->input('id'));
        $this->employeeProjectInfo = $employeeProject;
        return [
            'id' => ['required', 'exists:employee_projects,id'],
            'task' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'startTime' => ['required', 'date_format:H:i:s'],
            'endTime' => ['required', 'date_format:H:i:s', 'after:startTime'],
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @param array|null $keys
     * @return array
     */
    public function all($keys = null): array
    {
        $data = parent::all($keys);

        $moreInfo = EmployeeProject::find($data['id']);
        $data['workHours'] = $moreInfo->workHours;

        return $data;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'The date must be on or after the project start date (' . Carbon::parse($this->employeeProjectInfo->project->startDate)->format('jS F Y') . ').',
            'date.before_or_equal' => 'The date must be on or before the project end date (' . Carbon::parse($this->employeeProjectInfo->project->endDate)->format('jS F Y') . ').',
            'endTime.after' => 'The end time must be after the start time.',
            'endTime.required' => 'The end time field is required.',
            'startTime.required' => 'The start time field is required.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors()->all(), JsonResponse::HTTP_BAD_REQUEST)
        );
    }
}
