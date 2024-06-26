<?php

namespace App\Http\Controllers;

use App\Models\Company;

class CompaniesController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', Company::class);

            $companies = Company::with('companies')
            // ->whereHas('companies')
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($companies);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $this->authorize('create', Company::class);

            request()->validate([
                'company_id' => 'nullable|integer|exists:companies,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'required|string|min:1|max:255',
                'currency' => 'required|string|min:1|max:255',
            ]);

            $company = Company::create([
                'company_id' => request('company_id'),
                'label' => request('label'),
                'description' => request('description'),
                'currency' => request('currency'),
            ]);

            insert_in_history_table('created', $company->id, $company->getTable());

            return response()->json(['message' => 'Company created successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $company = Company::with('company', 'companies', 'roles.permissions')->findOrFail($id);

            $this->authorize('view', $company);

            return response()->json($company);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'required|string|min:1|max:255',
                'currency' => 'required|string|min:1|max:255',
            ]);

            $company = Company::findOrFail($id);

            $this->authorize('update', $company);

            $company->company_id = request('company_id');
            $company->label = request('label');
            $company->description = request('description');
            $company->currency = request('currency');

            $company->save();

            insert_in_history_table('updated', $company->id, $company->getTable());

            return response()->json(['message' => 'Company updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);

            $this->authorize('delete', $company);

            $company->companies()->delete();
            $company->roles()->detach();
            $company->users()->delete();
            $company->categories()->delete();
            $company->tags()->delete();
            $company->trainings()->delete();

            $company->delete();

            insert_in_history_table('deleted', $company->id, $company->getTable());

            return response()->json(['message' => 'Company deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function restore($id)
    {
        try {
            $company = Company::findOrFail($id);

            $this->authorize('restore', $company);

            $company->restore();

            insert_in_history_table('restored', $company->id, $company->getTable());

            return response()->json(['message' => 'Company restored successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function force_delete($id)
    {
        try {
            $company = Company::findOrFail($id);

            $this->authorize('forceDelete', $company);

            $company->forceDelete();

            insert_in_history_table('force_deleted', $company->id, $company->getTable());

            return response()->json(['message' => 'Company force deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
