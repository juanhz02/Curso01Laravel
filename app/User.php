<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password) //hacer algo por defecto en un campo antes de guardar
    {
      $this->attributes['password'] = bcrypt($password);
    }

    public function roles()
    {
      return $this->belongsToMany('App\Role','assigned_roles');
    }

    public function hasRoles(array $roles)
    {    
      return $this->roles->pluck('name')->intersect($roles)->count();    
    }

    public function isAdmin()
    {
      return $this->hasRoles(['admin']);
    }

    public function messages()
    {
      return $this->hasMany(Message::class);
    }

     /**
     * Relacion polimorfica
     */
    public function note()
    {
        return $this->morphOne(Note::class,'notable'); //se agrega el prefijo de la migracion
    }

    /**
     * Relacion polimorfica - muchos a muchos
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class,'taggable')->withTimestamps();
    }

}
