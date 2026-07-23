<?php

namespace App\Controllers;

use App\Models\SettingModel;
use NovaFlow\Core\Controller;

/**
 * AdminSettingsController
 * Manage Global Site Settings
 */
class AdminSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): void
    {
        $this->view('admin.settings.index', [
            'title'    => 'সাইট সেটিংস — NovaFlow',
            'settings' => SettingModel::getAll()
        ], 'admin');
    }

    public function update(): void
    {
        $data = $this->post();
        
        foreach ($data as $key => $value) {
            if ($key === 'csrf_token') continue;
            SettingModel::set($key, $value);
        }

        flash('success', 'সেটিংস সফলভাবে আপডেট হয়েছে।');
        $this->redirect('/admin/settings');
    }
}
