<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BulkPasswordImport implements WithMultipleSheets
{
    private array $invalidEmails = [];
    private int $totalRows = 0;

    public function sheets(): array
    {
        return [
            'Template Update' => new class($this) implements ToCollection, WithHeadingRow {
                private BulkPasswordImport $parent;

                public function __construct(BulkPasswordImport $parent)
                {
                    $this->parent = $parent;
                }

                public function collection(Collection $rows)
                {
                    if ($rows->isEmpty()) {
                        return;
                    }

                    foreach ($rows as $row) {
                        $this->parent->incrementTotalRows();

                        $email = trim($row['email'] ?? '');
                        $newPassword = trim($row['new_password'] ?? '');

                        if (!$email || !$newPassword) {
                            $this->parent->addInvalidEmail($email ?: '(email kosong)');
                            continue;
                        }

                        $user = User::where('email', $email)->first();

                        if (!$user) {
                            $this->parent->addInvalidEmail($email);
                            continue;
                        }

                        $user->password = bcrypt($newPassword);
                        $user->updated_by = auth('admin')->user()->name ?? 'system';
                        $user->save();
                    }
                }
            }
        ];
    }

    public function addInvalidEmail(string $email): void
    {
        $this->invalidEmails[] = $email;
    }

    public function incrementTotalRows(): void
    {
        $this->totalRows++;
    }

    public function getInvalidEmails(): array
    {
        return $this->invalidEmails;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }
}
