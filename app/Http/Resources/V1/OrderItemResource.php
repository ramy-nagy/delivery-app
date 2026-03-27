<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'name' => $this->name_snapshot,
            'unit_price_cents' => $this->unit_price_cents,
            'quantity' => $this->quantity,
            'line_total_cents' => $this->line_total_cents,
            'options' => $this->options_snapshot ?? [],
        ];
    }
}
