<?php

namespace App\Modules\Ticket\Presentation\Http\Requests;

use App\Modules\Ticket\Application\DTO\UpdateTicketData;
use App\Domain\Ticket\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && ($this->user()?->can('update', $ticket) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'max:50'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function toDto(): UpdateTicketData
    {
        return UpdateTicketData::fromArray($this->validated());
    }
}
