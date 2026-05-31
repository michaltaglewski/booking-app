<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostCreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $roomCapacity = Room::query()
            ->whereKey($this->input('room_id'))
            ->value('capacity');

        $participantsRules = ['required', 'integer', 'min:1'];

        if ($roomCapacity !== null) {
            $participantsRules[] = 'max:' . $roomCapacity;
        }

        return [
            'room_id' => ['required', 'uuid', 'exists:rooms,id'],
            'starts_at' => ['required', 'after_or_equal:now'],
            'ends_at' => ['required', 'after_or_equal:starts_at'],
            'participants_count' => $participantsRules,
        ];
    }
}
