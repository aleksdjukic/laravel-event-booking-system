<?php

namespace App\Modules\Event\Presentation\Http\Requests;

use App\Modules\Event\Application\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event && ($this->user()?->can('update', $event) ?? false);
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

    public function toDto(): UpdateEventData
    {
        return UpdateEventData::fromArray($this->validated());
    }
}
