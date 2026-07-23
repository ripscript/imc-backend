<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueSoftDelete implements Rule
{
    protected $table;

    protected $column;

    protected $ignore;

    public function __construct($table, $column = 'name', $ignore = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignore = $ignore;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->whereNull('deleted_at');

        if ($this->ignore) {
            $query->where('id', '<>', $this->ignore);
        }

        return $query->doesntExist();
    }

    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
