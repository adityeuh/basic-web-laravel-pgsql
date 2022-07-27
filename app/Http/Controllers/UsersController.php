<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UsersController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Display all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show form for creating user
     * 
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     * 
     * @param User $user
     * @param StoreUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(User $user, StoreUserRequest $request)
    {
        //For demo purposes only. When creating user or inviting a user
        // you should create a generated random password and email it to the user

        if ($gambar = $request->file('gambar')) {
            $ext = $gambar->getClientOriginalExtension();
            $url_img = 'img-users/' . date('YmdHis') . '.' . $ext;
            $source = public_path() . '/images/img-users';

            $gambar->move($source, $url_img);
        } else {
            $url_img = 'img-users/noimage.jpg';
        }

        $result = $user->create(array_merge($request->validated(), [
            'password' => (string)$request->input('password'),
            'url_img' => (string)$url_img
        ]));


        return redirect()->route('users.edit', $result->id)
            ->withSuccess(__('User created successfully.'));
    }

    /**
     * Show user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('users.show', [
            'user' => $user,
            'role' => ($user->roles->pluck('name')->toArray()) ? $user->roles->pluck('name')->toArray()[0] : '&mdash;'
        ]);
    }

    /**
     * Edit user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
            'userRole' => $user->roles->pluck('name')->toArray(),
            'roles' => Role::latest()->get()
        ]);
    }

    /**
     * Update user data
     * 
     * @param User $user
     * @param UpdateUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, UpdateUserRequest $request)
    {

        if ($gambar = $request->file('gambar')) {
            $ext = $gambar->getClientOriginalExtension();
            if (!$url_img = $user->url_img) { //cek jika sudah ada maka di replace saja
                $url_img = 'img-users/' . date('YmdHis') . '.' . $ext;
            }

            $source = public_path() . '/images/img-users';
            $gambar->move($source, $url_img);
        } else {
            $url_img = 'img-users/noimage.jpg';
        }

        if ($request->input('password')) {
            $user->update(array_merge($request->validated(), [
                'password' => (string)$request->input('password'),
                'url_img' => (string)$url_img
            ]));
        } else {
            $user->update(array_merge($request->validated(), ['url_img' => (string)$url_img]));
        }

        $user->syncRoles($request->get('role'));

        return redirect()->route('users.index')
            ->withSuccess(__('User updated successfully.'));
    }

    /**
     * Delete user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->withSuccess(__('User deleted successfully.'));
    }

    public function testing()
    {
        print_r('sinsi');
        exit;
    }
}
