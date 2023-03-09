<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationConfiguration extends Model
{
    protected $fillable = ['notification_name', 'notification_discription', 'notification_type', 'notification_status'];

    public function template()
    {
    	return $this->hasOne('App\ConfigurationTemplate', 'notification_configuration_id', 'id');
    }

    public function keywords(){
        return $this->hasMany('App\TemplateKeyword', 'notification_configuration_id', 'id');
    }
}
