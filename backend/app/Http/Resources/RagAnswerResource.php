<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RagAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'answer' => $this['answer'],
            'reasoning_path' => $this['reasoning_path'],
            'source_nodes' => $this['source_nodes'],
            'pages' => $this['source_pages'],
            'confidence' => round($this['confidence'] / 100, 2),
        ];
    }
}
