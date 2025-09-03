<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(15); // paginate for convenience
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:vendors|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('vendors', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        Vendor::create($validated);

        return redirect()->route('admin.vendors.create')->with('success', 'Vendor added successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:vendors,name,' . $vendor->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Optional: delete old logo file here if needed

            $validated['logo'] = $request->file('logo')->store('vendors', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $vendor->update($validated);

        return redirect()->route('admin.vendors.edit', $vendor->id)->with('success', 'Vendor updated successfully.');
    }
    public function destroy(Vendor $vendor)
{
    // Optionally, delete logo file from storage
    if ($vendor->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($vendor->logo)) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($vendor->logo);
    }

    $vendor->delete();

    return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
}
}
