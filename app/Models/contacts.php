<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'nom_cont',
        'tel_cont',
    ];

    protected $hidden = [
        'client_id',
    ];

    protected $table = 'personne_contact';
    protected $primaryKey = 'id_cont';
}
