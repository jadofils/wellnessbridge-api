<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthProperty extends Model
{
    use HasFactory;

    protected $table = 'birth_properties';
    protected $primaryKey = 'bID';


    protected $fillable = [
        'childID', 'motherAge', 'fatherAge', 'numberOfChildren',
        'birthType', 'birthWeight', 'childCondition'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class, 'childID');
    }
}
