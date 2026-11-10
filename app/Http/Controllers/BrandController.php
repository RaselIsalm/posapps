<?php

namespace App\Http\Controllers;

use App\DataTables\BrandDataTable;
use App\Helpers\ImageHelper;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    public function index(BrandDataTable $dataTable)
    {
        return $dataTable->render('backend.brand.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            return view('backend.brand.ajax.create');
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
        ]);

        $slug = str_replace(' ', '-', strtolower($request->name));
        $isActive = $request->is_active == 'on' ? 1 : 0;

        $brand = new Brand;
        $brand->name = $request->name;
        $brand->slug = $slug;
        $brand->description = $request->description;
        $brand->created_by = Auth::user()->id;
        $brand->status = $isActive;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'brand_'.$slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::uploadImage($filename, $file, 'brand');
            $brand->image = $filename;
        }
        $brand->save();

        activity_log('added brand', 'brand name: '.$request->name);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand added successfully.',
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ],
            ]);
        }

        return redirect()->route('brands.index')->with('success', 'Brand added successfully.');
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $brand = Brand::find($id);

            return view('backend.brand.ajax.edit', compact('brand'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name,'.$id],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
        ]);

        $slug = str_replace(' ', '-', strtolower($request->name));
        $isActive = $request->is_active == 'on' ? 1 : 0;

        $brand = Brand::find($id);
        $brand->name = $request->name;
        $brand->slug = $slug;
        $brand->description = $request->description;
        $brand->status = $isActive;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $oldImage = $brand->image;
            $filename = 'brand_'.$slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::updateImage($filename, $file, 'brand', $oldImage);
            $brand->image = $filename;
        } elseif ($request->input('remove_image') === '1') {
            ImageHelper::deleteImage($brand->image, 'brand');
            $brand->image = null;
        }
        $brand->save();

        activity_log('updated brand', 'brand name: '.$request->name);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully.'
            ]);
        }

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        if ($brand->products()->exists()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warning: This brand cannot be deleted because there are products associated with it! Please reassign or delete those products first.'
                ], 403);
            }
            return redirect()->route('brands.index')
                ->with('error', 'Warning: This brand cannot be deleted because there are products associated with it! Please reassign or delete those products first.');
        }

        ImageHelper::deleteImage($brand->image, 'brand');
        $brandName = $brand->name;
        $brand->delete();

        activity_log('deleted brand', 'brand name: '.$brandName);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully.'
            ]);
        }

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
