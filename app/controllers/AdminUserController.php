<?php

namespace App\Controllers;

use App\Models\UserModel;
use NovaFlow\Core\Controller;
use NovaFlow\Core\Flash;
use NovaFlow\Core\Security;

/**
 * AdminUserController
 * Full User Management CRUD for Admin Panel
 */
class AdminUserController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->checkAuth();
    }

    /**
     * List all users - GET /admin/users
     */
    public function index(): void
    {
        $page = (int) $this->get('page', 1);
        $perPage = 20;
        $search = $this->get('search', '');

        $query = UserModel::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $total = $query->count();
        $users = $query->orderBy('id', 'DESC')
                      ->limit($perPage, ($page - 1) * $perPage)
                      ->get();

        $this->view('admin/users/index', [
            'title' => 'ব্যবহারকারী হইসেস — NovaFlow',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search
        ], 'admin');
    }

    /**
     * Show create user form - GET /admin/users/create
     */
    public function create(): void
    {
        $this->view('admin/users/create', [
            'title' => 'নতুন ব্যবহারকারী — NovaFlow'
        ], 'admin');
    }

    /**
     * Store new user - POST /admin/users/store
     */
    public function store(): void
    {
        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $role = $this->post('role', 'user');
        $status = $this->post('status', 'active');

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            Flash::error('সবগুলো ঘর পূরণ করুন।');
            $this->redirect('/admin/users/create');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('সঠিক ইমেইল এড্রেস দিন।');
            $this->redirect('/admin/users/create');
        }

        if (UserModel::findByEmail($email)) {
            Flash::error('এই ইমেইল ইতিমধ্যে ব্যবহৃত হয়েছে।');
            $this->redirect('/admin/users/create');
        }

        $user = new UserModel();
        $user->name = $name;
        $user->email = $email;
        $user->password = Security::hashPassword($password);
        $user->role = $role;
        $user->status = $status;
        $user->save();

        Flash::success('ব্যবহারকারী সফলভাবে তৈরি হয়েছে!');
        $this->redirect('/admin/users');
    }

    /**
     * Show edit user form - GET /admin/users/edit/{id}
     */
    public function edit($id): void
    {
        $user = UserModel::query()->find($id);

        if (!$user) {
            Flash::error('ব্যবহারকারী পাওয়া যায়নি।');
            $this->redirect('/admin/users');
        }

        $this->view('admin/users/edit', [
            'title' => 'ব্যবহারকারী সম্পাদনা — NovaFlow',
            'user' => $user
        ], 'admin');
    }

    /**
     * Update user - POST /admin/users/update/{id}
     */
    public function update($id): void
    {
        $user = UserModel::query()->find($id);

        if (!$user) {
            Flash::error('ব্যবহারকারী পাওয়া যায়নি।');
            $this->redirect('/admin/users');
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $role = $this->post('role', $user->role);
        $status = $this->post('status', $user->status);

        if (empty($name) || empty($email)) {
            Flash::error('নাম এবং ইমেইল অবশ্যই।');
            $this->redirect('/admin/users/edit/' . $id);
        }

        // Check email uniqueness
        $existing = UserModel::findByEmail($email);
        if ($existing && $existing->id != $id) {
            Flash::error('এই ইমেইল ইতিমধ্যে ব্যবহৃত হয়েছে।');
            $this->redirect('/admin/users/edit/' . $id);
        }

        $user->name = $name;
        $user->email = $email;
        $user->role = $role;
        $user->status = $status;

        if (!empty($password)) {
            $user->password = Security::hashPassword($password);
        }

        $user->save();

        Flash::success('ব্যবহারকারী সফলভাবে আপডেট হয়েছে!');
        $this->redirect('/admin/users');
    }

    /**
     * Delete user - POST /admin/users/delete/{id}
     */
    public function delete($id): void
    {
        $user = UserModel::query()->find($id);

        if (!$user) {
            Flash::error('ব্যবহারকারী পাওয়া যায়নি।');
            $this->redirect('/admin/users');
        }

        // Prevent self-deletion
        if ($user->id == $_SESSION['user_id']) {
            Flash::error('আপনার নিজের অ্যাকাউন্ট ডিলিট করতে পারবেন না।');
            $this->redirect('/admin/users');
        }

        $user->delete();

        Flash::success('ব্যবহারকারী ডিলিট হয়েছে!');
        $this->redirect('/admin/users');
    }

    /**
     * Toggle user status - POST /admin/users/toggle-status/{id}
     */
    public function toggleStatus($id): void
    {
        $user = UserModel::query()->find($id);

        if (!$user) {
            Flash::error('ব্যবহারকারী পাওয়া যায়নি।');
            $this->redirect('/admin/users');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $statusText = $user->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        Flash::success("ব্যবহারকারী {$statusText} করা হয়েছে!");
        $this->redirect('/admin/users');
    }
}