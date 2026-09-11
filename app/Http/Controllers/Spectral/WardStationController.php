<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\WardStation;
use Illuminate\Http\Request;

class WardStationController extends Controller
{
    public function index()
    {
        $wardStations = WardStation::with('barangay')->get();
        return view('spectral.wards.index', compact('wardStations'));
    }
}
