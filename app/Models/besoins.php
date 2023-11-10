<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Besoins extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'photo_beso',
        'libelle_beso',
        'prix_beso',
        'type_beso',
    ];

    protected $hidden = [
        'client_id',
    ];

    protected $table = 'besoins';
    protected $primaryKey = 'id_beso';
}
