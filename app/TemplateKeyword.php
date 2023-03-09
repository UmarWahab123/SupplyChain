<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TemplateKeyword extends Model
{
    protected $fillable = [ 'notification_configuration_id',  'notification_type',  'keywords'];
}
