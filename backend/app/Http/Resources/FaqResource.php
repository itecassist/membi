<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'faq_category_id' => $this->faq_category_id,
            'question'        => $this->question,
            'answer'          => $this->answer,
            'sort_order'      => $this->sort_order,
            'display_on_help' => $this->display_on_help,
            'paused'          => $this->paused,
            'category'        => new FaqCategoryResource($this->whenLoaded('category')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
