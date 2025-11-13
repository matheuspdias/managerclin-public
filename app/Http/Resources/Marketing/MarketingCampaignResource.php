<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketingCampaignResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'message'          => $this->message,
            'media_type'       => $this->media_type,
            'media_url'        => $this->media_url,
            'media_filename'   => $this->media_filename,
            'local_media_path' => $this->local_media_path,
            'status'           => $this->status,
            'target_audience'  => $this->target_audience,
            'target_filters'   => $this->target_filters,
            'scheduled_at'     => $this->scheduled_at?->toIso8601String(),
            'sent_at'          => $this->sent_at?->toIso8601String(),
            'total_recipients' => $this->total_recipients,
            'sent_count'       => $this->sent_count,
            'failed_count'     => $this->failed_count,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
