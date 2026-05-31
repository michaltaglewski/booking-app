<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomCollection extends ResourceCollection
{
    public $collects = RoomResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
