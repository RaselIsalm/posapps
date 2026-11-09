<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Spatie\Permission\Models\Role;

class ProductImportController extends Controller
{
    public function __construct(protected ProductImportService $importService) {}

    public function index()
    {
        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $units = Unit::where('is_active', 1)->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $brands = Brand::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $branchManagers = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name']);
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.product.import', compact(
            'branches',
            'units',
            'categories',
            'brands',
            'branchManagers',
            'accounts',
            'employeeRoles'
        ));
    }

    public function downloadSample()
    {
        return $this->importService->downloadSample();
    }

    public function import(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'branch_id.required' => 'Please select a branch.',
            'excel_file.required' => 'Please upload an Excel file.',
            'excel_file.mimes' => 'Only Excel files (.xlsx, .xls) are allowed.',
            'excel_file.max' => 'File size must not exceed 5MB.',
        ]);

        try {
            $result = $this->importService->import(
                $request->file('excel_file'),
                (int) $request->branch_id
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Import failed. Please use the sample file format and try again.');
        }

        $message = $result['imported'].' product(s) imported successfully.';
        if ($result['failed'] > 0) {
            $message .= ' '.$result['failed'].' row(s) failed.';
        }

        return back()->with([
            'success' => $message,
            'import_result' => $result,
        ]);
    }
}
