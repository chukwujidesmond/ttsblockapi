<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

     public function index()
    {
        

        $user = auth('sanctum')->user();
        return response()->json([
            'user' => $user,
          
        ], 200);
    }
}
