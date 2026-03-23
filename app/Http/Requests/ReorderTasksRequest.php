<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'task_ids'   => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'exists:tasks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'task_ids.required'  => 'Task order is required.',
            'task_ids.array'     => 'Task IDs must be an array.',
            'task_ids.*.exists'  => 'One or more tasks do not exist.',
        ];
    }
}
