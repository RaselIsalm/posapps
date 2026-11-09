<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesCategoryDataTable;
use App\Models\ExpensesCategory;
use Illuminate\Http\Request;

class ExpensesCategoryController extends Controller
{
    public function index(ExpensesCategoryDataTable $dataTable)
    {
        return $dataTable->render('backend.expenses_categories.index');
    }

    public function create()
    {
        return view('backend.expenses_categories.ajax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:expenses_categories,code',
        ], [
            'name.required' => 'The category name field is required.',
            'code.required' => 'The category code field is required.',
            'code.unique' => 'This category code has already been taken. Please use a different one.',
        ]);

        $category = ExpensesCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'status' => $request->has('is_active') ? 1 : 0,
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                    'status' => (bool) $category->status,
                ],
            ]);
        }

        return redirect()->route('expenses-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $category = ExpensesCategory::find($id);
            $categories = ExpensesCategory::where('id', '!=', $id)->get();

            return view('backend.expenses_categories.ajax.edit', compact('category', 'categories'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:expenses_categories,code,'.$id,
        ], [
            'name.required' => 'The category name is required.',
            'code.required' => 'The category code is required.',
            'code.unique' => 'This category code is already in use by another category.',
        ]);

        ExpensesCategory::where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code,
            'status' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('expenses-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $expenseCategory = ExpensesCategory::findOrFail($id);

        if ($expenseCategory->expenses()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Warning: This category cannot be deleted because it has existing expenses associated with it!'], 400);
            }
            return redirect()->back()
                ->with('error', 'Warning: This category cannot be deleted because it has existing expenses associated with it!');
        }

        $expenseCategory->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Expense Category deleted successfully.']);
        }

        return redirect()->route('expenses-categories.index')->with('success', 'Expense Category deleted successfully.');
    }
}
