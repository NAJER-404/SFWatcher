<?php

namespace App\Http\Controllers\Spectral;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::with('barangay')->get();
        return view('spectral.resources.index', compact('resources'));
    }
}
