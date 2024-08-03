<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
class BaseModel extends Model
{
    use HasFactory;
    use Metable;

    protected $metaTable = 'meta';
    
    // Optional: Specify the custom meta table name
    public function meta()
    {
        return $this->morphMany(Meta::class, 'metable');
    }



    public function getActivitylogOptions()
    {
        return LogOptions::defaults()
        ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}");
    }
}
