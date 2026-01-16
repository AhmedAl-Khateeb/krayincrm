<?php

namespace Webkul\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $contactNumbers = $this->contact_numbers;

        if (is_string($contactNumbers)) {
            $decoded = json_decode($contactNumbers, true);
            $contactNumbers = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        $emails = $this->emails;

        if (is_string($emails)) {
            $decoded = json_decode($emails, true);
            $emails = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'emails'          => $emails,
            'contact_numbers' => $contactNumbers,
            'organization'    => new OrganizationResource($this->organization),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
