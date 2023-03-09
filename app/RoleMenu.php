<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    protected $with = ["roleMenus","childs","parent"];
    public function roleMenus() {
        return $this->belongsTo('App\Menu','menu_id','id') ;
    }
    public function childs() {
        return $this->hasMany('App\Menu','id','parent_id') ;
    }
    public function parent() {
        return $this->belongsTo('App\Menu','parent_id','id') ;
    }
    public function get_menus()
    {
        return $this->belongsTo('App\Menu','menu_id','id') ;
    }

    public function record() {
        return $this->hasMany('App\RoleMenu', 'parent_id','parent_id');
    }
    protected $fillable = ['role_id','menu_id'];

}
