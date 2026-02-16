<?php

namespace App\Modules\Event\Presentation\Http\Requests;

use App\Modules\Event\Application\DTO\ListEventsData;
use Illuminate\Foundation\Http\FormRequest;

class ListEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): ListEventsData
    {
        return ListEventsData::fromArray($this->validated());
    }
}
