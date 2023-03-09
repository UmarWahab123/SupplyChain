<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportFileHistory extends Model
{
    protected $fillable = [
        'user_id', 'page_name', 'file_name',
    ];
    public static function insertRecordIntoDb($user_id, $page_name, $file){
        $fileNameToStore = '';
        if($file && !is_string($file) && $file->isValid())
        {       
            $fileNameWithExt = $file->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
            $path = $file->move('public/uploads/import_files/',$fileNameToStore);
        }
        return ImportFileHistory::create([
            'user_id' => $user_id,
            'page_name' => $page_name,
            'file_name' => $fileNameToStore,
        ]);
    }
    public function User(){
        return $this->belongsTo('App\User','user_id','id');
    }
}
