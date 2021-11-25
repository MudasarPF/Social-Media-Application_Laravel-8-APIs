<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);

        //With Resource
        return [
            'ID' => $this->id,
            'User ID' => $this->user_id,
            'Post ID' => $this->post_id,
            'Content' => $this->content,
            'Created_at' => $this->created_at, 
        ];
    }
}
