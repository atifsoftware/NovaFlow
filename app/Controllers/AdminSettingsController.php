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
        $allowedKeys = [
            'site_name',
            'site_tagline',
            'contact_email',
            'contact_phone',
            'social_facebook',
            'social_youtube'
        ];

        $data = $this->allPost();
        
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $val = trim((string)$data[$key]);
                SettingModel::set($key, $val);
            }
        }

        flash('success', 'সেটিংস সফলভাবে আপডেট হয়েছে।');
        $this->redirect('/admin/settings');
    }
}
