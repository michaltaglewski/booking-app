<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetRoomsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'starts_at' => ['nullable'],
            'ends_at' => ['nullable', 'after_or_equal:starts_at'],
        ];
    }
}
