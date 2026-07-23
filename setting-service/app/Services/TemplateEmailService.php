<?php

namespace App\Services;

use App\Models\TemplateEmail;
use App\Services\DTOs\TemplateEmailDTO;
use Illuminate\Support\Facades\DB;

class TemplateEmailService
{
    public function index()
    {
        return DB::table('template_emails')->orderBy('id')->get();
    }

    public function detail($id)
    {
        return TemplateEmail::findOrFail($id);
    }

    public function update($id, TemplateEmailDTO $dto)
    {
        $template = TemplateEmail::findOrFail($id);
        $template->content = $dto->content;
        $template->subject = $dto->subject;
        $template->updated_by = $dto->updated_by;
        $template->save();

        return $template;
    }

    public function updateAll(array $data, string $user)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $value) {
                $template = TemplateEmail::findOrFail($value['id']);
                $template->content = $value['content'];
                $template->subject = $value['subject'] ?? '';
                $template->updated_by = $user;
                $template->save();
            }

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
