<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:brands,name|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        Brand::create($validated);

         return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand created successfully.');
       }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
     public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {
            // delete old logo
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($validated);

        return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand updated successfully.');
       }


    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Brand $brand)
    {
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();

        return redirect()
        ->route('admin.brands.index')
        ->with('success', 'Brand deleted successfully.');
     }
}
