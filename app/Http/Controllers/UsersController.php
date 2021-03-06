<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateUserRequest;

class UsersController extends Controller
{

    function __construct()
    {
        $this->middleware('auth',['except' => ['show']]);
        $this->middleware('roles:admin', ['except' => ['edit','update','show'] ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$users = User::all();

        /**
       * Optimizacion de tiempos de consultas - eager loading [precargar modelos]
       */

        $users = User::with(['roles','note','tags'])->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck("display_name","id");
        return view('users.create')->with('roles',$roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\CreateUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->attach($request->roles); //Como es un usuario nuevo no hay problemas de duplicacion

        return redirect()->route('usuarios.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('users.show')->with('user',$user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('edit',$user);

        $roles = Role::pluck("display_name","id");

        return view('users.edit')->with(
            ["user" => $user,
            "roles" => $roles
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        
        
        $user = User::findOrFail($id);
        $this->authorize('update',$user);
        $user->update($request->only('name','email')); //esto aplica para evitar actualizar el password
        //$user->roles()->attach($request->roles); //se agregar los valores continuamente
        $user->roles()->sync($request->roles); //se agregar los valores y se auto identifican los valores repetidos y no los agrega
        return back()->with('info','Usuario actualizado');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('destroy',$user);

        $user->delete();

        return back();
    }
}
