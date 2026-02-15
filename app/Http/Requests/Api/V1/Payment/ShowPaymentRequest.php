<?php

namespace App\Http\Requests\Api\V1\Payment;

use App\Domain\Payment\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ShowPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $payment instanceof Payment && ($this->user()?->can('view', $payment) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
