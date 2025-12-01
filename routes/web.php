<?php

use App\Http\Controllers\CalenderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LegalCase\LegalCaseController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PermissionsAndRolesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserMgt\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;


Route::get('/', function (Request $request) {
    return Inertia::render('auth/Login', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'canRegister' => false,
    'status' => $request->session()->get('status'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'my_analytics'])
        ->name('dashboard');

    Route::get('/reports/case-reports', [ReportController::class, 'cases_report'])
            ->name('reports.case_reports');

    Route::get('/cases_by_lawyer_report', [ReportController::class, 'cases_by_lawyer_report'])->name('reports.reports_by_lawyer');
    
    Route::get('/calendar/master', [CalenderController::class, 'get_master_calendar'])->name('calendar.master');
    Route::get('/calendar/mine', [CalenderController::class, 'get_my_calendar'])->name('calendar.mine');
    Route::post('/calender/store/event', [CalenderController::class, 'store_event']);
    Route::put('calender/edit/event/{id}', [CalenderController::class, 'edit_event']);
    Route::delete('calender/delete/event/{id}', [CalenderController::class, 'delete_event']);

    Route::get('/task/kanban', [TaskController::class, 'get_kanban_view'])->name('kanban.index');



    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/favourite', [TaskController::class, 'change_favourite_status'])
        ->name('tasks.favourite');
    
    Route::patch('/tasks/{task}/status/{status}', [TaskController::class, 'update_task_status'])
        ->name('tasks.status.update');
        
    Route::get('/documents', [FileController::class, 'files'])->name('files.index');
    
    // Legal Cases Routes
    Route::prefix('legal_cases')->group(function () {
        // Display routes (return pages)
        Route::get('/', [LegalCaseController::class, 'index'])->name('case.index');
        Route::get('/{id}', [LegalCaseController::class, 'show'])->name('case.show');
        
        // CRUD operations (redirect back with messages)
        Route::post('/', [LegalCaseController::class, 'create'])->name('case.create');
        Route::put('/{id}', [LegalCaseController::class, 'update'])->name('case.update');
        Route::patch('/{id}/activate', [LegalCaseController::class, 'activate'])->name('case.activate');
        Route::patch('/{id}/deactivate', [LegalCaseController::class, 'deactivate'])->name('case.deactivate');
        Route::delete('/{id}', [LegalCaseController::class, 'delete'])->name('case.delete');
        
        // Case Stages
        Route::post('/update-case-stages', [LegalCaseController::class, 'updatecasestages'])->name('case.update-case-stages');
        
        // Case Activities
        Route::post('/case-activities', [LegalCaseController::class, 'create_case_activity'])->name('case.create-case-activity');
        Route::put('/case-activities/{id}', [LegalCaseController::class, 'update_case_activity'])->name('case.update-case-activity');
        Route::delete('/case-activities/{id}', [LegalCaseController::class, 'delete_case_activity'])->name('case.delete-case-activity');
        
        // Case Attachments
        Route::post('/{id}/attachments', [LegalCaseController::class, 'store_case_attachments'])->name('case.store-case-attachments');
        Route::put('/{id}/attachments', [LegalCaseController::class, 'edit_case_attachments'])->name('case.edit-case-attachments');
        Route::delete('/{legal_case_id}/attachments/{attachment_id}', [LegalCaseController::class, 'delete_case_attachment'])->name('case.delete-case-attachment');
        
        // Case Notes
        Route::post('/case-notes', [LegalCaseController::class, 'create_case_note'])->name('case.create-case-note');
        Route::put('/case-notes/{id}', [LegalCaseController::class, 'update_case_note'])->name('case.update-case-note');
        Route::delete('/case-notes/{id}', [LegalCaseController::class, 'delete_case_note'])->name('case.delete-case-note');
    });
    
    
    // Individual Party Routes
    Route::post('/parties/individual', [PartyController::class, 'createIndividual']);
    Route::put('/parties/individual', [PartyController::class, 'updateIndividual']);
    Route::delete('/parties/individual', [PartyController::class, 'destroyIndividual']);
    
    // Firm Party Routes
    Route::post('/parties/firm', [PartyController::class, 'createFirm']);
    Route::put('/parties/firm', [PartyController::class, 'updateFirm']);
    Route::delete('/parties/firm', [PartyController::class, 'destroyFirm']);



    Route::get('/external_council', [SettingsController::class, 'external_firms'])->name('council.external');
    Route::get('/external_council', [SettingsController::class, 'external_firms']);
    Route::post('/external_council', [SettingsController::class, 'store_external_firm']);
    Route::put('/external_council/{id}', [SettingsController::class, 'update_external_firm']);
    Route::put('/external_council/activate/{id}', [SettingsController::class, 'activate_external_firm']);
    Route::put('/external_council/deactivate/{id}', [SettingsController::class, 'deactivate_external_firm']);
    Route::delete('/external_council/{id}', [SettingsController::class, 'delete_external_firm']);

    Route::get('/internal_council', [UserController::class, 'internal_counsel'])->name('council.internal');

    Route::get('/knowledge_base/document_templates', [KnowledgeBaseController::class, 'document_templates'])->name('knowledge.templates.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/update_user_details', [UserController::class, 'update_user_details']);
    Route::get('/user/{id}', [UserController::class, 'user']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::put('/user/activate/{id}', [UserController::class, 'activate']);
    Route::put('/user/deactivate/{id}', [UserController::class, 'deactivate']);
    Route::delete('/user/{id}', [UserController::class, 'delete_account']);

    Route::get('/roles', [PermissionsAndRolesController::class, 'index'])->name('roles.index');
    Route::post('/roles', [PermissionsAndRolesController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [PermissionsAndRolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [PermissionsAndRolesController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/{role}/restore', [PermissionsAndRolesController::class, 'restore'])->name('roles.restore');
    Route::post('/roles/{role}/permissions', [PermissionsAndRolesController::class, 'assignPermissions'])
        ->name('roles.permissions.assign');

    // Main Misc Settings Page
    Route::get('/setting/misc', [MiscController::class, 'index'])->name('index');
        
    // ==================== CASE TYPES ====================
    Route::post('/case-types', [MiscController::class, 'store_case_type'])->name('store_case_type');
    Route::put('/case-types/{id}', [MiscController::class, 'update_case_type'])->name('update_case_type');
    Route::post('/case-types/{id}/activate', [MiscController::class, 'activate_case_type'])->name('activate_case_type');
    Route::post('/case-types/{id}/deactivate', [MiscController::class, 'deactivate_case_type'])->name('deactivate_case_type');
    Route::post('/case-types/{id}/delete', [MiscController::class, 'delete_case_type'])->name('delete_case_type');
        
    // ==================== NATURE OF CLAIMS ====================
    Route::post('/nature-of-claims', [MiscController::class, 'store_nature_of_claim'])->name('store_nature_of_claim');
    Route::put('/nature-of-claims/{id}', [MiscController::class, 'update_nature_of_claim'])->name('update_nature_of_claim');
    Route::post('/nature-of-claims/{id}/activate', [MiscController::class, 'activate_nature_of_claim'])->name('activate_nature_of_claim');
    Route::post('/nature-of-claims/{id}/deactivate', [MiscController::class, 'deactivate_nature_of_claim'])->name('deactivate_nature_of_claim');
    Route::post('/nature-of-claims/{id}/delete', [MiscController::class, 'delete_nature_of_claim'])->name('delete_nature_of_claim');
        
    // ==================== PARTY TYPES ====================
    Route::post('/party-types', [MiscController::class, 'store_party_type'])->name('store_party_type');
    Route::put('/party-types/{id}', [MiscController::class, 'update_party_type'])->name('update_party_type');
    Route::post('/party-types/{id}/activate', [MiscController::class, 'activate_party_type'])->name('activate_party_type');
    Route::post('/party-types/{id}/deactivate', [MiscController::class, 'deactivate_party_type'])->name('deactivate_party_type');
    Route::post('/party-types/{id}/delete', [MiscController::class, 'delete_party_type'])->name('delete_party_type');
        
    // ==================== CASE ACTIVITIES ====================
    Route::post('/case-activities', [MiscController::class, 'store_case_activity'])->name('store_case_activity');
    Route::put('/case-activities/{id}', [MiscController::class, 'update_case_activity'])->name('update_case_activity');
    Route::post('/case-activities/{id}/activate', [MiscController::class, 'activate_case_activity'])->name('activate_case_activity');
    Route::post('/case-activities/{id}/deactivate', [MiscController::class, 'deactivate_case_activity'])->name('deactivate_case_activity');
    Route::post('/case-activities/{id}/delete', [MiscController::class, 'delete_case_activity'])->name('delete_case_activity');
        
    // ==================== DOCUMENT TYPES ====================
    Route::post('/document-types', [MiscController::class, 'store_document_type'])->name('store_document_type');
    Route::put('/document-types/{id}', [MiscController::class, 'update_document_type'])->name('update_document_type');
    Route::post('/document-types/{id}/activate', [MiscController::class, 'activate_document_type'])->name('activate_document_type');
    Route::post('/document-types/{id}/deactivate', [MiscController::class, 'deactivate_document_type'])->name('deactivate_document_type');
    Route::post('/document-types/{id}/delete', [MiscController::class, 'delete_document_type'])->name('delete_document_type');
        
    // ==================== CASE STAGES ====================
    Route::post('/case-stages', [MiscController::class, 'store_case_stage'])->name('store_case_stage');
    Route::put('/case-stages/{id}', [MiscController::class, 'update_case_stage'])->name('update_case_stage');
    Route::post('/case-stages/{id}/activate', [MiscController::class, 'activate_case_stage'])->name('activate_case_stage');
    Route::post('/case-stages/{id}/deactivate', [MiscController::class, 'deactivate_case_stage'])->name('deactivate_case_stage');
    Route::post('/case-stages/{id}/delete', [MiscController::class, 'delete_case_stage'])->name('delete_case_stage');
        
    // ==================== EVENT CATEGORIES ====================
    Route::post('/event-categories', [MiscController::class, 'store_event_category'])->name('store_event_category');
    Route::put('/event-categories/{id}', [MiscController::class, 'update_event_category'])->name('update_event_category');
    Route::post('/event-categories/{id}/activate', [MiscController::class, 'activate_event_category'])->name('activate_event_category');
    Route::post('/event-categories/{id}/deactivate', [MiscController::class, 'deactivate_event_category'])->name('deactivate_event_category');
    Route::post('/event-categories/{id}/delete', [MiscController::class, 'delete_event_category'])->name('delete_event_category');
        
    // ==================== EXPENSE TYPES ====================
    Route::post('/expense-types', [MiscController::class, 'store_expense_type'])->name('store_expense_type');
    Route::put('/expense-types/{id}', [MiscController::class, 'update_expense_type'])->name('update_expense_type');
    Route::post('/expense-types/{id}/activate', [MiscController::class, 'activate_expense_type'])->name('activate_expense_type');
    Route::post('/expense-types/{id}/deactivate', [MiscController::class, 'deactivate_expense_type'])->name('deactivate_expense_type');
    Route::post('/expense-types/{id}/delete', [MiscController::class, 'delete_expense_type'])->name('delete_expense_type');

});

require __DIR__.'/settings.php';
