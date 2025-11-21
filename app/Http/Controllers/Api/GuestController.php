<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
        'nama' => 'required|string',
        'jabatan' => 'required|string',
        'asal_instansi' => 'required|string',
        'nik' => 'required|integer|unique:guests,nik', // Sementara, nunggu kebijakan dari Pak Wahyu
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        $guest = Guest::create($data);

        return response()->json([
            'message' => 'Welcome Guest created successfully.',
            'user' => $guest,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}
