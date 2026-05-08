<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $nama = $request->input('nama', '');

        $mahasiswa = DB::table('mahasiswa as m')
            ->join('prodi as p', 'm.id_prodi', '=', 'p.id_prodi')
            ->select(
                'm.nim',
                'm.nama',
                'p.nama_prodi',
                'm.image'
            )
            ->when($nama != '', function ($query) use ($nama) {
                return $query->where('m.nama', 'like', '%'.$nama.'%');
            })
            ->get()
            ->map(function ($item) {
                if ($item->image != '') {
                    $item->image = asset('images/'.$item->image);
                } else {
                    $item->image = '';
                }

                return $item;
            });

        return response()->json($mahasiswa);
    }

    public function getNamaProdi()
    {
        $prodi = DB::table('prodi')
            ->select('nama_prodi')
            ->orderBy('nama_prodi', 'asc')
            ->get();

        return response()->json($prodi);
    }

    public function queryUpdDelIns(Request $request)
    {
        try {
            $mode = $request->input('mode');

            if ($mode == 'insert') {
                return $this->insertMahasiswa($request);
            } elseif ($mode == 'update') {
                return $this->updateMahasiswa($request);
            } elseif ($mode == 'delete') {
                return $this->deleteMahasiswa($request);
            } else {
                return response()->json([
                    'kode' => 'GAGAL',
                    'pesan' => 'Mode tidak valid',
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'kode' => 'GAGAL',
                'pesan' => $e->getMessage(),
            ]);
        }
    }

    private function insertMahasiswa(Request $request)
    {
        $nim = $request->input('nim');
        $nama = $request->input('nama');
        $namaProdi = $request->input('nama_prodi');

        $idProdi = $this->getIdProdiByNama($namaProdi);

        if ($idProdi == null) {
            return response()->json([
                'kode' => 'GAGAL',
                'pesan' => 'Nama prodi tidak ditemukan',
            ]);
        }

        $cekNim = DB::table('mahasiswa')
            ->where('nim', $nim)
            ->first();

        if ($cekNim) {
            return response()->json([
                'kode' => 'GAGAL',
                'pesan' => 'NIM sudah ada',
            ]);
        }

        $namaFile = $this->simpanImageBase64($request);

        DB::table('mahasiswa')->insert([
            'nim' => $nim,
            'nama' => $nama,
            'id_prodi' => $idProdi,
            'image' => $namaFile,
        ]);

        return response()->json([
            'kode' => 'BERHASIL',
            'pesan' => 'Data berhasil disimpan',
        ]);
    }

    private function updateMahasiswa(Request $request)
    {
        $nim = $request->input('nim');
        $nama = $request->input('nama');
        $namaProdi = $request->input('nama_prodi');

        $idProdi = $this->getIdProdiByNama($namaProdi);

        if ($idProdi == null) {
            return response()->json([
                'kode' => 'GAGAL',
                'pesan' => 'Nama prodi tidak ditemukan',
            ]);
        }

        $dataUpdate = [
            'nama' => $nama,
            'id_prodi' => $idProdi,
        ];

        $namaFile = $this->simpanImageBase64($request);

        if ($namaFile != '') {
            $dataUpdate['image'] = $namaFile;
        }

        DB::table('mahasiswa')
            ->where('nim', $nim)
            ->update($dataUpdate);

        return response()->json([
            'kode' => 'BERHASIL',
            'pesan' => 'Data berhasil diupdate',
        ]);
    }

    private function deleteMahasiswa(Request $request)
    {
        $nim = $request->input('nim');

        DB::table('mahasiswa')
            ->where('nim', $nim)
            ->delete();

        return response()->json([
            'kode' => 'BERHASIL',
            'pesan' => 'Data berhasil dihapus',
        ]);
    }

    private function getIdProdiByNama($namaProdi)
    {
        $prodi = DB::table('prodi')
            ->whereRaw('LOWER(TRIM(nama_prodi)) = ?', [
                strtolower(trim($namaProdi)),
            ])
            ->first();

        if ($prodi) {
            return $prodi->id_prodi;
        }

        return null;
    }

    private function simpanImageBase64(Request $request)
    {
        $imageBase64 = $request->input('image');
        $namaFile = $request->input('file');

        if ($imageBase64 == null || $imageBase64 == '') {
            return '';
        }

        if ($namaFile == null || $namaFile == '') {
            $namaFile = time().'.jpg';
        }

        $imageBase64 = str_replace('data:image/jpeg;base64,', '', $imageBase64);
        $imageBase64 = str_replace('data:image/png;base64,', '', $imageBase64);
        $imageBase64 = str_replace(' ', '+', $imageBase64);

        $imageData = base64_decode($imageBase64);

        if ($imageData == false) {
            return '';
        }

        $path = public_path('images');

        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path.'/'.$namaFile, $imageData);

        return $namaFile;
    }

    public function checkService()
    {
        return response()->json([
            'kode' => 'BERHASIL',
            'pesan' => 'Service aktif',
        ]);
    }
}
