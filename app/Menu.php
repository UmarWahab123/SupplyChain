<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    protected $fillable = ['title','parent_id','status','url','route','icon'];

    public function childs() {
        return $this->hasMany('App\Menu','parent_id','id') ;
    }
    public function parent() {
        return $this->belongsTo('App\Menu','parent_id','id') ;
    }
    public function rollmenus() {
        return $this->hasMany('App\RoleMenu','menu_id','id') ;
    }

    public function rollmenusdata() {
        return $this->hasMany('App\RoleMenu','parent_id','id') ;
    }
    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();

        static::deleting(function($menu) { // before delete() method call this
            $menu->childs()->delete();
            // do the rest of the cleanup...
        });
    }
}
