<?php

namespace App\Modules\HRM\Controllers;

use NovaFlow\Core\Controller;

class HRMController extends Controller
{
    public function index(): void
    {
        $this->view('HRM::hrm.index', [
            'title' => 'HRM Module Dashboard'
        ], 'main');
    }
}
