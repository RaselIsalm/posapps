<?php

namespace App\Http\Controllers;

use App\DataTables\BranchDataTable;
use App\Helpers\ImageHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class BranchController extends Controller
{
    public function index(BranchDataTable $dataTable)
    {
        return $dataTable->render('backend.branch.index');
    }

    public function create()
    {
        $accounts = Account::active()->orderBy('name')->get();
        $employees = User::orderBy('name')->get();
        $roles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.branch.create', compact('accounts', 'employees', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'account_id' => 'required|exists:accounts,id',
            'manager_id' => 'required|exists:users,id',
            'phone' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:50',
            'bin_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'branch_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $branch = Branch::create([
            'name' => $request->name,
            'manager_id' => $request->manager_id,
            'account_id' => $request->account_id,
            'phone' => $request->phone,
            'telephone' => $request->telephone,
            'bin_number' => $request->bin_number,
            'email' => $request->email,
            'website' => $this->normalizeWebsite($request->website),
            'address' => $request->address,
            'status' => $request->has('is_active'),
            'created_by' => Auth::user()->id,
        ]);

        if ($request->hasFile('branch_logo') && $request->file('branch_logo')->isValid()) {
            $file = $request->file('branch_logo');
            $filename = 'branch_'.$request->name.'_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::uploadImage($filename, $file, 'branch');
            $branch->branch_logo = $filename;
            $branch->save();
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully.',
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'status' => (bool) $branch->status,
                ],
            ]);
        }

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        $accounts = Account::active()->orderBy('name')->get();
        $employees = User::orderBy('name')->get();
        $roles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.branch.edit', compact('branch', 'accounts', 'employees', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'account_id' => 'required|exists:accounts,id',
            'manager_id' => 'required|exists:users,id',
            'phone' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:50',
            'bin_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'branch_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->name = $request->name;
        $branch->manager_id = $request->manager_id;
        $branch->account_id = $request->account_id;
        $branch->phone = $request->phone;
        $branch->telephone = $request->telephone;
        $branch->bin_number = $request->bin_number;
        $branch->email = $request->email;
        $branch->website = $this->normalizeWebsite($request->website);
        $branch->address = $request->address;
        $branch->status = $request->has('is_active');
        $branch->save();

        if ($request->hasFile('branch_logo') && $request->file('branch_logo')->isValid()) {
            $file = $request->file('branch_logo');
            $filename = 'branch_'.$request->name.'_'.time().'.'.$file->getClientOriginalExtension();
            $oldImage = $branch->branch_logo;
            $filename = ImageHelper::updateImage($filename, $file, 'branch', $oldImage);
            $branch->branch_logo = $filename;
            $branch->save();
        } elseif ($request->input('remove_branch_logo') === '1') {
            ImageHelper::deleteImage($branch->branch_logo, 'branch');
            $branch->branch_logo = null;
            $branch->save();
        }

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->sales()->exists() || $branch->purchases()->exists()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Warning: This branch cannot be deleted because it has existing sales or purchases!'], 400);
            }

            return redirect()->route('branches.index')
                ->with('error', 'Warning: This branch cannot be deleted because it has existing sales or purchases!');
        }

        $branch->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Branch deleted successfully.',
            ]);
        }

        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }

    protected function normalizeWebsite(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return $url;
    }
}
