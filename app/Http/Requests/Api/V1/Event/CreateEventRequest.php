<?php

namespace App\Http\Requests\Api\V1\Event;

use App\Application\Event\DTO\CreateEventData;
use App\Domain\Event\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
        ];
    }

    public function toDto(): CreateEventData
    {
        return CreateEventData::fromArray($this->validated());
    }
}
