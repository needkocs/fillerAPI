<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fields' => FieldResource::collection($this->fields),
            'players' => PlayerResourse::collection($this->players),
            'currentPlayerId' => $this->currentPlayerId,
            'winnerPlayerId' => $this->winnerPlayerId,
        ];
    }
}
