<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PemohonResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'gender' => $this->gender,
            'identity_number' => $this->identity_number,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'organization' => $this->organization,
            'job_title' => $this->job_title,
            'classification_pemohon_id' => $this->classification_pemohon_id,
            'classification' => $this->whenLoaded('classification', fn () => $this->classification?->name),
            'identity_card_file_path' => $this->identity_card_file_path,
            'legal_document_file_path' => $this->legal_document_file_path,
            'is_privacy_policy_accepted' => $this->is_privacy_policy_accepted,
            'status' => $this->status,
            'is_email_verified' => $this->is_email_verified,
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
