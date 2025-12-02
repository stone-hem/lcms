<?php

namespace App\Http\Controllers;

use App\Models\LegalCaseActivityType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LegalCase\CaseType;
use App\Models\LegalCase\NatureOfClaim;
use App\Models\Party\PartyType;
use App\Models\CaseActivity;
use App\Models\DocumentTypes;
use App\Models\CaseStage;
use App\Models\EventCategories;
use App\Models\ExpenseTypes;

class MiscController extends Controller
{
    // Main page - loads all data once
    public function index()
    {
        return Inertia::render('settings/Misc', [
            'misc' => [
                'case_types'       => CaseType::withTrashed()->get(),
                'nature_of_claims' => NatureOfClaim::withTrashed()->get(),
                'party_types'      => PartyType::withTrashed()->get(),
                'case_activity_types'  => LegalCaseActivityType::get(),
                'document_types'   => DocumentTypes::withTrashed()->get(),
                'case_stages'      => CaseStage::with('after')->withTrashed()->get(),
                'event_categories' => EventCategories::withTrashed()->get(),
                'expense_types'    => ExpenseTypes::withTrashed()->get(),
            ],
            'success' => session('success')
        ]);
    }

    private function success($message)
    {
        return redirect()->route('misc.index')->with('success', $message);
    }

    // ==================== CASE TYPES ====================
    public function store_case_type(Request $request)
    {
        $request->validate(['name' => 'required|min:2|unique:case_types,name']);
        CaseType::create($request->only('name', 'description'));
        return $this->success('Case Type added successfully');
    }

    public function update_case_type(Request $request, $id)
    {
        $request->validate([
            'name' => "required|min:2|unique:case_types,name,$id",
            'id'   => 'required'
        ]);
        CaseType::findOrFail($id)->update($request->only('name', 'description'));
        return $this->success('Case Type updated successfully');
    }

    public function activate_case_type($id)     { CaseType::withTrashed()->findOrFail($id)->restore(); return $this->success('Case Type activated'); }
    public function deactivate_case_type($id)   { CaseType::findOrFail($id)->delete(); return $this->success('Case Type deactivated'); }
    public function delete_case_type($id)       { CaseType::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Case Type permanently deleted'); }

    // ==================== NATURE OF CLAIMS ====================
    public function store_nature_of_claim(Request $request)
    {
        $request->validate(['claim' => 'required|min:2|unique:nature_of_claims,claim']);
        NatureOfClaim::create($request->only('claim', 'description'));
        return $this->success('Nature of Claim added successfully');
    }

    public function update_nature_of_claim(Request $request, $id)
    {
        $request->validate(['claim' => "required|min:2|unique:nature_of_claims,claim,$id"]);
        NatureOfClaim::findOrFail($id)->update($request->only('claim', 'description'));
        return $this->success('Nature of Claim updated successfully');
    }

    public function activate_nature_of_claim($id)   { NatureOfClaim::withTrashed()->findOrFail($id)->restore(); return $this->success('Nature of Claim activated'); }
    public function deactivate_nature_of_claim($id) { NatureOfClaim::findOrFail($id)->delete(); return $this->success('Nature of Claim deactivated'); }
    public function delete_nature_of_claim($id)     { NatureOfClaim::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Nature of Claim permanently deleted'); }

    // ==================== PARTY TYPES ====================
    public function store_party_type(Request $request)
    {
        $request->validate(['name' => 'required|min:2|unique:party_types,name']);
        PartyType::create($request->only('name', 'description'));
        return $this->success('Party Type added successfully');
    }

    public function update_party_type(Request $request, $id)
    {
        $request->validate(['name' => "required|min:2|unique:party_types,name,$id"]);
        PartyType::findOrFail($id)->update($request->only('name', 'description'));
        return $this->success('Party Type updated successfully');
    }

    public function activate_party_type($id)   { PartyType::withTrashed()->findOrFail($id)->restore(); return $this->success('Party Type activated'); }
    public function deactivate_party_type($id) { PartyType::findOrFail($id)->delete(); return $this->success('Party Type deactivated'); }
    public function delete_party_type($id)     { PartyType::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Party Type permanently deleted'); }

    // ==================== CASE ACTIVITIES ====================
    public function store_case_activity(Request $request)
    {
        $request->validate(['name' => 'required|min:2|unique:case_activities,name']);
        CaseActivity::create($request->only('name', 'description', 'after', 'fields'));
        return $this->success('Case Activity added successfully');
    }

    public function update_case_activity(Request $request, $id)
    {
        $request->validate(['name' => "required|min:2|unique:case_activities,name,$id"]);
        CaseActivity::findOrFail($id)->update($request->only('name', 'description', 'after', 'fields'));
        return $this->success('Case Activity updated successfully');
    }

    public function activate_case_activity($id)   { CaseActivity::withTrashed()->findOrFail($id)->restore(); return $this->success('Case Activity activated'); }
    public function deactivate_case_activity($id) { CaseActivity::findOrFail($id)->delete(); return $this->success('Case Activity deactivated'); }
    public function delete_case_activity($id)     { CaseActivity::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Case Activity permanently deleted'); }

    // ==================== DOCUMENT TYPES ====================
    public function store_document_type(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|unique:document_types,name',
            'abbreviation' => 'nullable|unique:document_types,abbreviation'
        ]);
        DocumentTypes::create($request->only('name', 'abbreviation', 'description'));
        return $this->success('Document Type added successfully');
    }

    public function update_document_type(Request $request, $id)
    {
        $request->validate([
            'name' => "required|min:2|unique:document_types,name,$id",
            'abbreviation' => "nullable|unique:document_types,abbreviation,$id"
        ]);
        DocumentTypes::findOrFail($id)->update($request->only('name', 'abbreviation', 'description'));
        return $this->success('Document Type updated successfully');
    }

    public function activate_document_type($id)   { DocumentTypes::withTrashed()->findOrFail($id)->restore(); return $this->success('Document Type activated'); }
    public function deactivate_document_type($id) { DocumentTypes::findOrFail($id)->delete(); return $this->success('Document Type deactivated'); }
    public function delete_document_type($id)     { DocumentTypes::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Document Type permanently deleted'); }

    // ==================== CASE STAGES ====================
    public function store_case_stage(Request $request)
    {
        $request->validate(['name' => 'required|min:2|unique:case_stages,name']);
        CaseStage::create($request->only('name', 'description', 'order_after'));
        return $this->success('Case Stage added successfully');
    }

    public function update_case_stage(Request $request, $id)
    {
        $request->validate(['name' => "required|min:2|unique:case_stages,name,$id"]);
        CaseStage::findOrFail($id)->update($request->only('name', 'description', 'order_after'));
        return $this->success('Case Stage updated successfully');
    }

    public function activate_case_stage($id)   { CaseStage::withTrashed()->findOrFail($id)->restore(); return $this->success('Case Stage activated'); }
    public function deactivate_case_stage($id) { CaseStage::findOrFail($id)->delete(); return $this->success('Case Stage deactivated'); }
    public function delete_case_stage($id)     { CaseStage::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Case Stage permanently deleted'); }

    // ==================== EVENT CATEGORIES ====================
    public function store_event_category(Request $request)
    {
        $request->validate(['category' => 'required|min:2|unique:event_categories,category']);
        EventCategories::create($request->only('category', 'description'));
        return $this->success('Event Category added successfully');
    }

    public function update_event_category(Request $request, $id)
    {
        $request->validate(['category' => "required|min:2|unique:event_categories,category,$id"]);
        EventCategories::findOrFail($id)->update($request->only('category', 'description'));
        return $this->success('Event Category updated successfully');
    }

    public function activate_event_category($id)   { EventCategories::withTrashed()->findOrFail($id)->restore(); return $this->success('Event Category activated'); }
    public function deactivate_event_category($id) { EventCategories::findOrFail($id)->delete(); return $this->success('Event Category deactivated'); }
    public function delete_event_category($id)     { EventCategories::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Event Category permanently deleted'); }

    // ==================== EXPENSE TYPES ====================
    public function store_expense_type(Request $request)
    {
        $request->validate(['type' => 'required|min:2|unique:expense_types,type']);
        ExpenseTypes::create($request->only('type', 'description'));
        return $this->success('Expense Type added successfully');
    }

    public function update_expense_type(Request $request, $id)
    {
        $request->validate(['type' => "required|min:2|unique:expense_types,type,$id"]);
        ExpenseTypes::findOrFail($id)->update($request->only('type', 'description'));
        return $this->success('Expense Type updated successfully');
    }

    public function activate_expense_type($id)   { ExpenseTypes::withTrashed()->findOrFail($id)->restore(); return $this->success('Expense Type activated'); }
    public function deactivate_expense_type($id) { ExpenseTypes::findOrFail($id)->delete(); return $this->success('Expense Type deactivated'); }
    public function delete_expense_type($id)     { ExpenseTypes::withTrashed()->findOrFail($id)->forceDelete(); return $this->success('Expense Type permanently deleted'); }
}
