<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitations extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'contact_inv',
        'type_inv',
        'etat_inv',
    ];

    protected $hidden = [
        'info_id',
    ];

    protected $table = 'invitations';
    protected $primaryKey = 'id_inv';
}
