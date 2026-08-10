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
        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'role' => $this->post('role', 'user'),
            'status' => $this->post('status', 'active')
        ];

        // Validation using Validator class
        $validator = \NovaFlow\Core\Validator::make($data, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive'
        ], [], [
            'name' => 'নাম',
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড',
            'role' => 'ভূমিকা',
            'status' => 'অবস্থা'
        ]);

        if (!$validator->validate()) {
            Flash::error($validator->firstError(array_key_first($validator->errors())));
            $this->redirect('/admin/users/create');
        }

        $user = new UserModel();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Security::hashPassword($data['password']);
        $user->role = $data['role'];
        $user->status = $data['status'];
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

        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'role' => $this->post('role', $user->role),
            'status' => $this->post('status', $user->status)
        ];

        // Add password only if provided
        $password = $this->post('password');
        if (!empty($password)) {
            $data['password'] = $password;
            $data['password_confirmation'] = $this->post('password_confirmation');
        }

        // Validation rules
        $rules = [
            'name' => 'required|min:3|max:100',
            'email' => "required|email|unique:users,email,{$id},id",
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive'
        ];

        // Add password validation only if password is being changed
        if (!empty($password)) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        $validator = \NovaFlow\Core\Validator::make($data, $rules, [], [
            'name' => 'নাম',
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড',
            'role' => 'ভূমিকা',
            'status' => 'অবস্থা'
        ]);

        if (!$validator->validate()) {
            Flash::error($validator->firstError(array_key_first($validator->errors())));
            $this->redirect('/admin/users/edit/' . $id);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->status = $data['status'];

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