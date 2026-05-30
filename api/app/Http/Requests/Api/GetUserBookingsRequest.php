<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetUserBookingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }
}
