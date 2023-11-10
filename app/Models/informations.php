<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informations extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'prenom_garcon',
        'prenom_fille',
        'message',
        'date_mariage',
        'couleur',
    ];

    protected $hidden = [
        'client_id',
    ];

    protected $table = 'informations';
    protected $primaryKey = 'id_info';
}
