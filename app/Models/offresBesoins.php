<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffresBesoins extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'contact_client_invite',
        'montant_libre',
    ];

    protected $hidden = [
        'besoin_id',
        'client_id',
    ];

    protected $table = 'offres_besoins';
    protected $primaryKey = 'id_off_beso';
}
