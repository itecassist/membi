<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'type'                => $this->type,
            'title'               => $this->title,
            'article_category_id' => $this->article_category_id,
            'page_title'          => $this->page_title,
            'seo_name'            => $this->seo_name,
            'content'             => $this->content,
            'summary'             => $this->summary,
            'seo_description'     => $this->seo_description,
            'featured'            => $this->featured,
            'live'                => $this->live,
            'popularity'          => $this->popularity,
            'category'            => new ArticleCategoryResource($this->whenLoaded('category')),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
