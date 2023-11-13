<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programmes extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'titre_pro',
        'lieu_pro',
        'date_pro',
    ];

    protected $hidden = [
        'info_id',
    ];

    protected $table = 'programmes';
    protected $primaryKey = 'id_prog';
}
