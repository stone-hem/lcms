<?php

use App\Http\Controllers\CalenderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LegalCase\LegalCaseController;
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

    Route::get('/case_reports', [ReportController::class, 'cases_report'])
            ->name('reports.case_reports');

    Route::get('/cases_by_lawyer_report', [ReportController::class, 'cases_by_lawyer_report'])->name('reports.reports_by_lawyer');
    Route::get('/calendar/master', [CalenderController::class, 'get_master_calendar'])->name('calendar.master');
    Route::get('/calendar/mine', [CalenderController::class, 'get_my_calendar'])->name('calendar.mine');

    Route::get('/task/kanban', [TaskController::class, 'get_kanban_view'])->name('kanban.index');



    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');


    Route::get('/documents', [FileController::class, 'files'])->name('files.index');

    Route::get('/legal_cases', [LegalCaseController::class, 'index'])->name('case.index');
    Route::post('/legal_cases', [LegalCaseController::class, 'create'])->name('case.create');
    Route::put('/legal_cases/{id}', [LegalCaseController::class, 'update'])->name('case.update');
    Route::get('/legal_cases/{id}', [LegalCaseController::class, 'show'])->name('case.show');
    Route::delete('/legal_cases/{id}', [LegalCaseController::class, 'delete'])->name('case.delete');



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

    Route::get('/settings/case_types', [SettingsController::class, 'case_types'])->name('settings.case_types');
    Route::get('/settings/nature_of_claims', [SettingsController::class, 'nature_of_claims'])->name('settings.nature_of_claims');
    Route::get('/settings/party_types', [SettingsController::class, 'party_types'])->name('settings.party_types');
    Route::get('/settings/document_types', [SettingsController::class, 'document_types'])->name('settings.document_types');
    Route::get('/settings/case_stages', [SettingsController::class, 'case_stages'])->name('settings.case_stages');
    Route::get('/settings/event_categories', [SettingsController::class, 'event_categories'])->name('settings.event_categories');
    Route::get('/settings/case_activity_types', [SettingsController::class, 'case_activities'])->name('settings.case_activities');

});

require __DIR__.'/settings.php';
