<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    protected $table = 'prodi';

    protected $primaryKey = 'id_prodi';

    protected $fillable = ['nama_prodi'];
}
