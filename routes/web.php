<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Master\BudgetSourceController;
use App\Http\Controllers\Master\BudgetYearController;
use App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\Master\DestitutionKkController;
use App\Http\Controllers\Master\DestitutionNikController;
use App\Http\Controllers\Master\OrganizationController;
use App\Http\Controllers\Master\ProgramTemplateController;
use App\Http\Controllers\Master\UserAccessController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\System\DataController;
use App\Http\Controllers\System\FileController;
use App\Http\Controllers\System\ImportController;
use App\Http\Controllers\System\LogActivityController;
use App\Http\Controllers\System\OptionController;
use App\Http\Controllers\System\PreferenceController;
use App\Http\Controllers\System\ToolController;
use App\Http\Controllers\Transaction\BnbaController;
use App\Http\Controllers\Transaction\DashboardController as ChartController;
use App\Http\Controllers\Transaction\DownloadController;
use App\Http\Controllers\Transaction\GalleryController;
use App\Http\Controllers\Transaction\ProgramController;
use App\Http\Controllers\Transaction\ProgramRealizationController;
use App\Http\Controllers\Website\MainController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MainController::class, 'index'])->name('web.home');

Route::group(['prefix' => env('ADMIN_LOGIN', 'administrator')], function () {

    Auth::routes([
        // 'register' => false, // Registration Routes...
        // 'reset' => false, // Password Reset Routes...
        // 'verify' => false, // Email Verification Routes...
    ]);

    Route::get('/forgot-password', [LoginController::class, 'forgot'])->name('forgot');
    Route::post('/forgot-password', [LoginController::class, 'forgotPass'])->name('go.forgot');
    Route::get('/forgot-password/token/{token}', [LoginController::class, 'forgotPassRecovery'])->name('go.forgot.reset');
    Route::post('/forgot-password/token/{token}/recovery', [LoginController::class, 'recoveryPass'])->name('go.recovery');

    Route::get('/reload-captcha', [LoginController::class, 'reloadCaptcha']);
    Route::middleware(['auth'])->group(function () {
        // Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Master User Access
        Route::get('/master-user-access', [UserAccessController::class, 'list'])->name('master.user_access.list')->middleware('access.module:mt_user_access');
        Route::get('/master-user-access/insert', [UserAccessController::class, 'insert'])->name('master.user_access.insert')->middleware('access.module:mt_user_access');
        Route::post('/master-user-access/save', [UserAccessController::class, 'store'])->name('master.user_access.store')->middleware('access.module:mt_user_access');
        Route::get('/master-user-access/edit/{id}', [UserAccessController::class, 'edit'])->name('master.user_access.edit')->middleware('access.module:mt_user_access');
        Route::post('/master-user-access/get_data', [UserAccessController::class, 'get_data'])->name('master.user_access.get_data')->middleware('access.module:mt_user_access');
        Route::put('/master-user-access/update/{id}', [UserAccessController::class, 'update'])->name('master.user_access.update')->middleware('access.module:mt_user_access');
        Route::delete('/master-user-access/delete', [UserAccessController::class, 'destroy'])->name('master.user_access.delete')->middleware('access.module:mt_user_access');
        Route::get('/master-user-access/edit/{id}/modules', [UserAccessController::class, 'edit_modules'])->name('master.user_access.edit_modules')->middleware('access.module:mt_user_access');
        Route::put('/master-user-access/update/{id}/modules', [UserAccessController::class, 'update_modules'])->name('master.user_access.update_modules')->middleware('access.module:mt_user_access');

        // Master User
        Route::get('/master-user', [UserController::class, 'list'])->name('master.user.list')->middleware('access.module:mt_user');
        Route::get('/master-user/insert', [UserController::class, 'insert'])->name('master.user.insert')->middleware('access.module:mt_user');
        Route::post('/master-user/save', [UserController::class, 'store'])->name('master.user.store')->middleware('access.module:mt_user');
        Route::get('/master-user/edit/{id}', [UserController::class, 'edit'])->name('master.user.edit')->middleware('access.module:mt_user');
        Route::post('/master-user/get_data', [UserController::class, 'get_data'])->name('master.user.get_data')->middleware('access.module:mt_user');
        Route::put('/master-user/update/{id}', [UserController::class, 'update'])->name('master.user.update')->middleware('access.module:mt_user');
        Route::put('/master-user/update-budget-year/{id}', [UserController::class, 'update_budget_year'])->name('master.user.update_budget_year');
        Route::delete('/master-user/delete', [UserController::class, 'destroy'])->name('master.user.delete')->middleware('access.module:mt_user');
        Route::post('/master-user/reset', [UserController::class, 'reset'])->name('master.user.reset')->middleware('access.module:mt_user');
        Route::get('/password/edit', [UserController::class, 'edit_password'])->name('master.user.edit_password');
        Route::put('/password/update', [UserController::class, 'update_password'])->name('master.user.update_password');

        // Dashboard
        Route::get('/master-dashboard', [DashboardController::class, 'list'])->name('master.dashboard.list')->middleware('access.module:mt_dashboard');
        Route::get('/master-dashboard/insert', [DashboardController::class, 'insert'])->name('master.dashboard.insert')->middleware('access.module:mt_dashboard');
        Route::post('/master-dashboard/save', [DashboardController::class, 'store'])->name('master.dashboard.store')->middleware('access.module:mt_dashboard');
        Route::get('/master-dashboard/edit/{id}', [DashboardController::class, 'edit'])->name('master.dashboard.edit')->middleware('access.module:mt_dashboard');
        Route::post('/master-dashboard/get_data', [DashboardController::class, 'get_data'])->name('master.dashboard.get_data')->middleware('access.module:mt_dashboard');
        Route::put('/master-dashboard/update/{id}', [DashboardController::class, 'update'])->name('master.dashboard.update')->middleware('access.module:mt_dashboard');
        Route::delete('/master-dashboard/delete', [DashboardController::class, 'destroy'])->name('master.dashboard.delete')->middleware('access.module:mt_dashboard');
        Route::get('/master-dashboard/edit/{id}/properties', [DashboardController::class, 'edit_properties'])->name('master.dashboard.edit_properties')->middleware('access.module:mt_dashboard');
        Route::put('/master-dashboard/update/{id}/properties', [DashboardController::class, 'update_properties'])->name('master.dashboard.update_properties')->middleware('access.module:mt_dashboard');

        // Destitution NIK
        Route::get('/master-destitution-nik', [DestitutionNikController::class, 'list'])->name('master.destitution_nik.list')->middleware('access.module:mt_destitution_nik');
        Route::get('/master-destitution-nik/insert', [DestitutionNikController::class, 'insert'])->name('master.destitution_nik.insert')->middleware('access.module:mt_destitution_nik');
        Route::post('/master-destitution-nik/save', [DestitutionNikController::class, 'store'])->name('master.destitution_nik.store')->middleware('access.module:mt_destitution_nik');
        // Route::get('/master-destitution-nik/edit/{id}', [DestitutionNikController::class, 'edit'])->name('master.destitution_nik.edit')->middleware('access.module:mt_destitution_nik');
        Route::post('/master-destitution-nik/get_data', [DestitutionNikController::class, 'get_data'])->name('master.destitution_nik.get_data')->middleware('access.module:mt_destitution_nik');
        // Route::put('/master-destitution-nik/update/{id}', [DestitutionNikController::class, 'update'])->name('master.destitution_nik.update')->middleware('access.module:mt_destitution_nik');
        Route::get('/master-destitution-nik/detail/{id}', [DestitutionNikController::class, 'detail'])->name('master.destitution_nik.detail')->middleware('access.module:mt_destitution_nik');
        // Route::delete('/master-destitution-nik/delete', [DestitutionNikController::class, 'destroy'])->name('master.destitution_nik.delete')->middleware('access.module:mt_destitution_nik');
        Route::post('/master-destitution-nik/bnba/{id}/get_data', [DestitutionNikController::class, 'bnba_get_data'])->name('master.destitution_nik.bnba.get_data')->middleware('access.module:mt_destitution_nik');

        // Budget Source
        Route::get('/master-budget-source', [BudgetSourceController::class, 'list'])->name('master.budget_source.list')->middleware('access.module:mt_budget_source');
        Route::get('/master-budget-source/insert', [BudgetSourceController::class, 'insert'])->name('master.budget_source.insert')->middleware('access.module:mt_budget_source');
        Route::post('/master-budget-source/save', [BudgetSourceController::class, 'store'])->name('master.budget_source.store')->middleware('access.module:mt_budget_source');
        Route::get('/master-budget-source/edit/{id}', [BudgetSourceController::class, 'edit'])->name('master.budget_source.edit')->middleware('access.module:mt_budget_source');
        Route::post('/master-budget-source/get_data', [BudgetSourceController::class, 'get_data'])->name('master.budget_source.get_data')->middleware('access.module:mt_budget_source');
        Route::put('/master-budget-source/update/{id}', [BudgetSourceController::class, 'update'])->name('master.budget_source.update')->middleware('access.module:mt_budget_source');
        Route::delete('/master-budget-source/delete', [BudgetSourceController::class, 'destroy'])->name('master.budget_source.delete')->middleware('access.module:mt_budget_source');

        // Organization
        Route::get('/master-organization', [OrganizationController::class, 'list'])->name('master.organization.list')->middleware('access.module:mt_organization');
        Route::get('/master-organization/insert', [OrganizationController::class, 'insert'])->name('master.organization.insert')->middleware('access.module:mt_organization');
        Route::post('/master-organization/save', [OrganizationController::class, 'store'])->name('master.organization.store')->middleware('access.module:mt_organization');
        Route::get('/master-organization/edit/{id}', [OrganizationController::class, 'edit'])->name('master.organization.edit')->middleware('access.module:mt_organization');
        Route::post('/master-organization/get_data', [OrganizationController::class, 'get_data'])->name('master.organization.get_data')->middleware('access.module:mt_organization');
        Route::put('/master-organization/update/{id}', [OrganizationController::class, 'update'])->name('master.organization.update')->middleware('access.module:mt_organization');
        Route::delete('/master-organization/delete', [OrganizationController::class, 'destroy'])->name('master.organization.delete')->middleware('access.module:mt_organization');

        // Destitution KK
        Route::get('/master-destitution-kk', [DestitutionKkController::class, 'list'])->name('master.destitution_kk.list')->middleware('access.module:mt_destitution_kk');
        Route::get('/master-destitution-kk/insert', [DestitutionKkController::class, 'insert'])->name('master.destitution_kk.insert')->middleware('access.module:mt_destitution_kk');
        Route::post('/master-destitution-kk/save', [DestitutionKkController::class, 'store'])->name('master.destitution_kk.store')->middleware('access.module:mt_destitution_kk');
        // Route::get('/master-destitution-kk/edit/{id}', [DestitutionKkController::class, 'edit'])->name('master.destitution_kk.edit')->middleware('access.module:mt_destitution_kk');
        Route::post('/master-destitution-kk/get_data', [DestitutionKkController::class, 'get_data'])->name('master.destitution_kk.get_data')->middleware('access.module:mt_destitution_kk');
        // Route::put('/master-destitution-kk/update/{id}', [DestitutionKkController::class, 'update'])->name('master.destitution_kk.update')->middleware('access.module:mt_destitution_kk');
        Route::get('/master-destitution-kk/detail/{id}', [DestitutionKkController::class, 'detail'])->name('master.destitution_kk.detail')->middleware('access.module:mt_destitution_kk');
        // Route::delete('/master-destitution-kk/delete', [DestitutionKkController::class, 'destroy'])->name('master.destitution_kk.delete')->middleware('access.module:mt_destitution_kk');
        Route::post('/master-destitution-kk/bnba/{id}/get_data', [DestitutionKkController::class, 'bnba_get_data'])->name('master.destitution_kk.bnba.get_data')->middleware('access.module:mt_destitution_kk');

        // Budget Year
        Route::get('/master-budget-year', [BudgetYearController::class, 'list'])->name('master.budget_year.list')->middleware('access.module:mt_budget_year');
        Route::get('/master-budget-year/insert', [BudgetYearController::class, 'insert'])->name('master.budget_year.insert')->middleware('access.module:mt_budget_year');
        Route::post('/master-budget-year/save', [BudgetYearController::class, 'store'])->name('master.budget_year.store')->middleware('access.module:mt_budget_year');
        Route::get('/master-budget-year/edit/{id}', [BudgetYearController::class, 'edit'])->name('master.budget_year.edit')->middleware('access.module:mt_budget_year');
        Route::post('/master-budget-year/get_data', [BudgetYearController::class, 'get_data'])->name('master.budget_year.get_data')->middleware('access.module:mt_budget_year');
        Route::put('/master-budget-year/update/{id}', [BudgetYearController::class, 'update'])->name('master.budget_year.update')->middleware('access.module:mt_budget_year');
        Route::delete('/master-budget-year/delete', [BudgetYearController::class, 'destroy'])->name('master.budget_year.delete')->middleware('access.module:mt_budget_year');

        // Prgram Template
        Route::get('/master-program-template', [ProgramTemplateController::class, 'list'])->name('master.program_template.list')->middleware('access.module:mt_program_template');
        Route::get('/master-program-template/insert/{type}', [ProgramTemplateController::class, 'insert'])->name('master.program_template.insert')->middleware('access.module:mt_program_template');
        Route::post('/master-program-template/save/{type}', [ProgramTemplateController::class, 'store'])->name('master.program_template.store')->middleware('access.module:mt_program_template');
        Route::get('/master-program-template/edit/{id}/{type}', [ProgramTemplateController::class, 'edit'])->name('master.program_template.edit')->middleware('access.module:mt_program_template');
        Route::post('/master-program-template/get_data', [ProgramTemplateController::class, 'get_data'])->name('master.program_template.get_data')->middleware('access.module:mt_program_template');
        Route::put('/master-program-template/update/{id}/{type}', [ProgramTemplateController::class, 'update'])->name('master.program_template.update')->middleware('access.module:mt_program_template');
        Route::delete('/master-program-template/delete', [ProgramTemplateController::class, 'destroy'])->name('master.program_template.delete')->middleware('access.module:mt_program_template');

        // File
        Route::get('/file', [FileController::class, 'list'])->name('system.file.list')->middleware('access.module:sy_file');
        Route::get('/file/insert', [FileController::class, 'insert'])->name('system.file.insert')->middleware('access.module:sy_file');
        Route::post('/file/save', [FileController::class, 'store'])->name('system.file.store')->middleware('access.module:sy_file');
        Route::get('/file/edit/{id}', [FileController::class, 'edit'])->name('system.file.edit')->middleware('access.module:sy_file');
        Route::post('/file/get_data', [FileController::class, 'get_data'])->name('system.file.get_data')->middleware('access.module:sy_file');
        Route::put('/file/update/{id}', [FileController::class, 'update'])->name('system.file.update')->middleware('access.module:sy_file');
        Route::delete('/file/delete', [FileController::class, 'destroy'])->name('system.file.delete')->middleware('access.module:sy_file');
        Route::post('/file/import/{id}', [FileController::class, 'import'])->name('system.file.import')->middleware('access.module:sy_file');

        // Data
        Route::get('/data', [DataController::class, 'list'])->name('system.data.list')->middleware('access.module:sy_data');
        Route::get('/data/insert', [DataController::class, 'insert'])->name('system.data.insert')->middleware('access.module:sy_data');
        Route::post('/data/save', [DataController::class, 'store'])->name('system.data.store')->middleware('access.module:sy_data');
        Route::get('/data/edit/{id}', [DataController::class, 'edit'])->name('system.data.edit')->middleware('access.module:sy_data');
        Route::post('/data/get_data', [DataController::class, 'get_data'])->name('system.data.get_data')->middleware('access.module:sy_data');
        Route::put('/data/update/{id}', [DataController::class, 'update'])->name('system.data.update')->middleware('access.module:sy_data');
        Route::delete('/data/delete', [DataController::class, 'destroy'])->name('system.data.delete')->middleware('access.module:sy_data');

        // Option
        Route::get('/option', [OptionController::class, 'list'])->name('system.option.list')->middleware('access.module:sy_option');
        Route::get('/option/insert', [OptionController::class, 'insert'])->name('system.option.insert')->middleware('access.module:sy_option');
        Route::post('/option/save', [OptionController::class, 'store'])->name('system.option.store')->middleware('access.module:sy_option');
        Route::get('/option/edit/{id}', [OptionController::class, 'edit'])->name('system.option.edit')->middleware('access.module:sy_option');
        Route::post('/option/get_data', [OptionController::class, 'get_data'])->name('system.option.get_data')->middleware('access.module:sy_option');
        Route::put('/option/update/{id}', [OptionController::class, 'update'])->name('system.option.update')->middleware('access.module:sy_option');
        Route::delete('/option/delete', [OptionController::class, 'destroy'])->name('system.option.delete')->middleware('access.module:sy_option');

        // Preference
        Route::get('/preference', [PreferenceController::class, 'list'])->name('system.preference.list')->middleware('access.module:sy_preference');
        Route::get('/preference/insert', [PreferenceController::class, 'insert'])->name('system.preference.insert')->middleware('access.module:sy_preference');
        Route::post('/preference/save', [PreferenceController::class, 'store'])->name('system.preference.store')->middleware('access.module:sy_preference');
        Route::get('/preference/edit/{id}', [PreferenceController::class, 'edit'])->name('system.preference.edit')->middleware('access.module:sy_preference');
        Route::post('/preference/get_data', [PreferenceController::class, 'get_data'])->name('system.preference.get_data')->middleware('access.module:sy_preference');
        Route::put('/preference/update/{id}', [PreferenceController::class, 'update'])->name('system.preference.update')->middleware('access.module:sy_preference');
        Route::delete('/preference/delete', [PreferenceController::class, 'destroy'])->name('system.preference.delete')->middleware('access.module:sy_preference');

        // Chart
        Route::get('/chart', [ChartController::class, 'list'])->name('dashboard.list')->middleware('access.module:tr_dashboard');
        Route::get('/chart/insert', [ChartController::class, 'insert'])->name('dashboard.insert')->middleware('access.module:tr_dashboard');
        Route::post('/chart/save', [ChartController::class, 'store'])->name('dashboard.store')->middleware('access.module:tr_dashboard');
        Route::get('/chart/edit/{id}', [ChartController::class, 'edit'])->name('dashboard.edit')->middleware('access.module:tr_dashboard');
        Route::post('/chart/get_data', [ChartController::class, 'get_data'])->name('dashboard.get_data')->middleware('access.module:tr_dashboard');
        Route::put('/chart/update/{id}', [ChartController::class, 'update'])->name('dashboard.update')->middleware('access.module:tr_dashboard');
        Route::delete('/chart/delete', [ChartController::class, 'destroy'])->name('dashboard.delete')->middleware('access.module:tr_dashboard');

        // Gallery
        Route::get('/gallery', [GalleryController::class, 'list'])->name('gallery.list')->middleware('access.module:tr_gallery');
        Route::get('/gallery/insert', [GalleryController::class, 'insert'])->name('gallery.insert')->middleware('access.module:tr_gallery');
        Route::post('/gallery/save', [GalleryController::class, 'store'])->name('gallery.store')->middleware('access.module:tr_gallery');
        Route::get('/gallery/edit/{id}', [GalleryController::class, 'edit'])->name('gallery.edit')->middleware('access.module:tr_gallery');
        Route::post('/gallery/get_data', [GalleryController::class, 'get_data'])->name('gallery.get_data')->middleware('access.module:tr_gallery');
        Route::put('/gallery/update/{id}', [GalleryController::class, 'update'])->name('gallery.update')->middleware('access.module:tr_gallery');
        Route::delete('/gallery/delete', [GalleryController::class, 'destroy'])->name('gallery.delete')->middleware('access.module:tr_gallery');

        // Program
        Route::get('/program', [ProgramController::class, 'list'])->name('program.list')->middleware('access.module:tr_program');
        Route::get('/program/insert', [ProgramController::class, 'insert'])->name('program.insert')->middleware('access.module:tr_program');
        Route::post('/program/save', [ProgramController::class, 'store'])->name('program.store')->middleware('access.module:tr_program');
        Route::get('/program/edit/{id}', [ProgramController::class, 'edit'])->name('program.edit')->middleware('access.module:tr_program');
        Route::post('/program/get_data', [ProgramController::class, 'get_data'])->name('program.get_data')->middleware('access.module:tr_program');
        Route::put('/program/update/{id}', [ProgramController::class, 'update'])->name('program.update')->middleware('access.module:tr_program');
        Route::delete('/program/delete', [ProgramController::class, 'destroy'])->name('program.delete')->middleware('access.module:tr_program');
        Route::post('/program/confirmation', [ProgramController::class, 'confirmation'])->name('program.confirmation')->middleware('access.module:tr_program');
        Route::post('/program/cancel', [ProgramController::class, 'cancel'])->name('program.cancel')->middleware('access.module:tr_program');

        Route::get('/program/budget/{id}', [ProgramController::class, 'budget_list'])->name('program.budget.list')->middleware('access.module:tr_program');
        Route::get('/program/budget/{id}/insert', [ProgramController::class, 'budget_insert'])->name('program.budget.insert')->middleware('access.module:tr_program');
        Route::post('/program/budget/{id}/save', [ProgramController::class, 'budget_store'])->name('program.budget.store')->middleware('access.module:tr_program');
        Route::delete('/program/budget/{id}/delete', [ProgramController::class, 'budget_destroy'])->name('program.budget.delete')->middleware('access.module:tr_program');
        Route::post('/program/budget/{id}/get_data', [ProgramController::class, 'budget_get_data'])->name('program.budget.get_data')->middleware('access.module:tr_program');

        // Program Realization
        Route::get('/program-realization', [ProgramRealizationController::class, 'list'])->name('program.realization.list')->middleware('access.module:tr_program_realization');
        Route::get('/program-realization/insert', [ProgramRealizationController::class, 'insert'])->name('program.realization.insert')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/save', [ProgramRealizationController::class, 'store'])->name('program.realization.store')->middleware('access.module:tr_program_realization');
        Route::get('/program-realization/edit/{id}', [ProgramRealizationController::class, 'edit'])->name('program.realization.edit')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/get_data/{source}', [ProgramRealizationController::class, 'get_data'])->name('program.realization.get_data')->middleware('access.module:tr_program_realization');
        Route::put('/program-realization/update/{id}', [ProgramRealizationController::class, 'update'])->name('program.realization.update')->middleware('access.module:tr_program_realization');
        Route::delete('/program-realization/delete', [ProgramRealizationController::class, 'destroy'])->name('program.realization.delete')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/confirmation', [ProgramRealizationController::class, 'confirmation'])->name('program.realization.confirmation')->middleware('access.module:tr_program_realization');

        // Download Data
        Route::get('/download', [DownloadController::class, 'list'])->name('download.list')->middleware('access.module:tr_download');
        Route::get('/download/report/{report_name}', [DownloadController::class, 'report'])->name('download.report')->middleware('access.module:tr_download');
        Route::post('/download/progress', [DownloadController::class, 'progress'])->name('download.progress')->middleware('access.module:tr_download');

        // Tool
        Route::get('/tool/program', [ToolController::class, 'program_list'])->name('tool.program.list')->middleware('access.module:to_program');

        Route::get('/program-realization/attachment/{id}', [ProgramRealizationController::class, 'attachment_list'])->name('program.realization.attachment.list')->middleware('access.module:tr_program_realization');
        Route::get('/program-realization/attachment/{id}/insert', [ProgramRealizationController::class, 'attachment_insert'])->name('program.realization.attachment.insert')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/attachment/{id}/save', [ProgramRealizationController::class, 'attachment_store'])->name('program.realization.attachment.store')->middleware('access.module:tr_program_realization');
        Route::delete('/program-realization/attachment/{id}/delete', [ProgramRealizationController::class, 'attachment_destroy'])->name('program.realization.attachment.delete')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/attachment/{id}/get_data', [ProgramRealizationController::class, 'attachment_get_data'])->name('program.realization.attachment.get_data')->middleware('access.module:tr_program_realization');
        Route::get('/program-realization/attachment/{id}/download/{attachment_id}', [ProgramRealizationController::class, 'attachment_download'])->name('program.realization.attachment.download')->middleware('access.module:tr_program_realization');

        Route::get('/program-realization/bnba/{id}', [ProgramRealizationController::class, 'bnba_list'])->name('program.realization.bnba.list')->middleware('access.module:tr_program_realization');
        Route::get('/program-realization/bnba/{id}/insert', [ProgramRealizationController::class, 'bnba_insert'])->name('program.realization.bnba.insert')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/bnba/{id}/save', [ProgramRealizationController::class, 'bnba_store'])->name('program.realization.bnba.store')->middleware('access.module:tr_program_realization');
        Route::delete('/program-realization/bnba/{id}/delete', [ProgramRealizationController::class, 'bnba_destroy'])->name('program.realization.bnba.delete')->middleware('access.module:tr_program_realization');
        Route::post('/program-realization/bnba/{id}/get_data', [ProgramRealizationController::class, 'bnba_get_data'])->name('program.realization.bnba.get_data')->middleware('access.module:tr_program_realization');

        // BNBA
        Route::get('/bnba/{source}', [BnbaController::class, 'detail'])->name('bnba.detail');

        // System Log Activity
        Route::get('/log-activity', [LogActivityController::class, 'list'])->name('system.log_activity.list')->middleware('access.module:sy_log_activity');
        Route::post('/log-activity/get_data', [LogActivityController::class, 'get_data'])->name('system.log_activity.get_data')->middleware('access.module:sy_log_activity');

        // System Log Activity
        Route::get('/import', [ImportController::class, 'list'])->name('system.import.list')->middleware('access.module:sy_import');
        Route::post('/import/get_data', [ImportController::class, 'get_data'])->name('system.import.get_data')->middleware('access.module:sy_import');

        // Route for ajax
        Route::post('backend-ajax/upload', [AjaxController::class, 'upload'])->name('ajax.upload');
        // Route::post('backend-ajax/permalink', [AjaxController::class, 'permalink'])->name('ajax.permalink');
        Route::post('backend-ajax/data-select/', [AjaxController::class, 'data_select'])->name('ajax.data_select');
        Route::post('backend-ajax/chart/', [AjaxController::class, 'chart'])->name('ajax.chart');
        Route::post('backend-ajax/chart-by-type/', [AjaxController::class, 'chartByType'])->name('ajax.chart_by_type');
        Route::post('backend-ajax/data-program', [AjaxController::class, 'data_program'])->name('ajax.data_program');
    });
});
