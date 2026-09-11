<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Library::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                  ->orWhere('Author', 'like', "%{$search}%")
                  ->orWhere('Genre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('Genre', $request->genre);
        }

        $librarys = $query->orderBy('id', 'desc')->get();

        $totalBooks = Library::count();
        $totalQuantity = Library::sum('Quantity') ?? 0;
        $totalValue = Library::selectRaw('SUM(Quantity * Price) as total_val')->value('total_val') ?? 0;
        $genres = Library::select('Genre')->distinct()->pluck('Genre');

        return view('library.index', compact('librarys', 'totalBooks', 'totalQuantity', 'totalValue', 'genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('library.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Author' => 'required|string|max:255',
            'Genre' => 'required|string|max:100',
            'Quantity' => 'required|numeric|min:0',
            'Price' => 'required|numeric|min:0',
        ]);

        Library::create($validated);

        return redirect()->route('librarys.index')->with('success', 'Book added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Library $library)
    {
        return view('library.show', compact('library'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Library $library)
    {
        return view('library.edit', compact('library'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Library $library)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Author' => 'required|string|max:255',
            'Genre' => 'required|string|max:100',
            'Quantity' => 'required|numeric|min:0',
            'Price' => 'required|numeric|min:0',
        ]);

        $library->update($validated);

        return redirect()->route('librarys.index')->with('success', 'Book updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Library $library)
    {
        $library->delete();

        return redirect()->route('librarys.index')->with('success', 'Book deleted successfully!');
    }
}
