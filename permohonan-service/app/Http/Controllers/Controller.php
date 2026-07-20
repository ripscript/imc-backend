<?php

namespace App\Http\Controllers;

use App\Services\EmailTemplateService;
use App\Services\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $queryService, $emailService;

    public function __construct()
    {
        $this->queryService = new QueryService();
        $this->emailService = new EmailTemplateService();
    }
}
