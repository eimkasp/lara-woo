<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'meta'; // Your meta table

    protected $fillable = ['key', 'value'];

    // Polymorphic relationship
    public function metable()
    {
        return $this->morphTo();
    }
}
