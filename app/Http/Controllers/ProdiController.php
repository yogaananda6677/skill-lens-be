<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ProdiController extends Controller
{
    public function getDataProdi()
    {
        $prodi = DB::table('prodi as p')
            ->select(
                'p.nama_prodi'
            )
            ->get();

        return response()->json($prodi);
    }
}
