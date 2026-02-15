<?php

namespace App\Http\Requests\Api\V1\Ticket;

use App\Domain\Ticket\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && ($this->user()?->can('delete', $ticket) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
