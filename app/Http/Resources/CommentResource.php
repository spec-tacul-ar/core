<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->sqid,
            'project_id' => $this->project_sqid,
            'commentable_type' => $this->commentable_type,
            'commentable_id' => $this->commentable_sqid,
            'commentable_name' => $this->commentable ? ucfirst($this->commentable->name) : null,
            'account_id' => $this->account_sqid,
            'account_name' => $this->account->name,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
