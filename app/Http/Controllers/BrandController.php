<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('brands.index', compact('brands'));
    }

    public function edit($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            return response()->json([
                'id' => $brand->id,
                'name' => $brand->name,
                'is_featured' => $brand->is_featured,
                'logo' => $brand->logo ? asset('storage/' . $brand->logo) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_featured' => 'nullable|boolean',
        ]);

        try {
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('brands', 'public');
            }

            $brand = Brand::create([
                'name' => $request->name,
                'logo' => $logoPath,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully',
                'brand' => $brand
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_featured' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'is_featured' => $request->boolean('is_featured'),
            ];

            if ($request->hasFile('logo')) {
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }
                $data['logo'] = $request->file('logo')->store('brands', 'public');
            }

            $brand->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);

            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand'
            ], 500);
        }
    }
}
