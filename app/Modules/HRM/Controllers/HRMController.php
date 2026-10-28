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

    public function create(): void
    {
        $this->view('HRM::hrm.create', ['title' => 'Add Employee — HRM'], 'main');
    }

    public function store(): void
    {
        flash('success', 'HRM record created successfully!');
        $this->redirect('/hrm/hrms');
    }

    public function show($id): void
    {
        $this->view('HRM::hrm.show', ['id' => $id, 'title' => 'Employee Details'], 'main');
    }

    public function edit($id): void
    {
        $this->view('HRM::hrm.edit', ['id' => $id, 'title' => 'Edit Employee'], 'main');
    }

    public function update($id): void
    {
        flash('success', 'HRM record updated successfully!');
        $this->redirect('/hrm/hrms');
    }

    public function destroy($id): void
    {
        flash('success', 'HRM record deleted successfully!');
        $this->redirect('/hrm/hrms');
    }
}
