<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeries extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'photo_gal',
        'libelle_gal',
        'type_gal',
    ];

    protected $hidden = [
        'info_id',
    ];

    protected $table = 'galeries';
    protected $primaryKey = 'id_gal';
}
