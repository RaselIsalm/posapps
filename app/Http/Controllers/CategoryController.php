<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Helpers\ImageHelper;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(CategoryDataTable $dataTable)
    {
        return $dataTable->render('backend.category.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            $categories = Category::active()->get(['id', 'name']);

            return view('backend.category.ajax.create', compact('categories'));
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
        ]);

        $slug = str_replace(' ', '-', strtolower($request->name));
        $isActive = $request->has('is_active') ? 1 : 0;

        $category = new Category;
        $category->name = $request->name;
        $category->slug = $slug;
        $category->parent_id = $request->parent_id;
        $category->description = $request->description;
        $category->status = $isActive;
        $category->created_by = Auth::user()->id;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'category_'.$slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::uploadImage($filename, $file, 'category');
            $category->image = $filename;
        }
        $category->save();

        activity_log('added category', 'category name: '.$request->name);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category added successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $category = Category::find($id);
            $categories = Category::where('status', 1)->where('id', '!=', $id)->get(['id', 'name']);

            return view('backend.category.ajax.edit', compact('category', 'categories'));
        }

        return abort(404);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$id],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
        ]);

        $isActive = $request->has('is_active') ? 1 : 0;
        $slug = str_replace(' ', '-', strtolower($request->name));

        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = $slug;
        $category->parent_id = $request->parent_id;
        $category->status = $isActive;
        $category->description = $request->description;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $oldImage = $category->image;
            $filename = 'category_'.$slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::updateImage($filename, $file, 'category', $oldImage);
            $category->image = $filename;
        } elseif ($request->input('remove_image') === '1') {
            ImageHelper::deleteImage($category->image, 'category');
            $category->image = null;
        }
        $category->save();

        activity_log('updated category', 'category name: '.$request->name);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.'
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warning: This category cannot be deleted because it contains products! Please reassign or delete the products first.'
                ], 403);
            }
            return redirect()->route('categories.index')
                ->with('error', 'Warning: This category cannot be deleted because it contains products! Please reassign or delete the products first.');
        }

        ImageHelper::deleteImage($category->image, 'category');
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);
        $categoryName = $category->name;
        $category->delete();

        activity_log('deleted category', 'category name: '.$categoryName);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
