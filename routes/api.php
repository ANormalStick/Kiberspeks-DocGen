<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\DocumentsController;


Route::post('/ai/compliance', [AiController::class, 'compliance']);
Route::post('/ai/risks',      [AiController::class, 'risks']);
Route::post('/ai/chat',       [AiController::class, 'chat']);
Route::post('/policy/generate',[AiController::class, 'policy']);
Route::post('/policy/generate-pdf', [ReportController::class, 'policyToPdf']);
Route::post('/reports/onepager', [ReportController::class, 'onePager']);


Route::get('/health', fn () => ['status' => 'ok']);

Route::get('/nis2/summary', fn () => [
    'governance' => 'ok',
    'risk_management' => 'needs_improvement',
    'incident_reporting' => 'ok',
    'supply_chain' => 'partial',
]);

Route::post('/assessments', [AssessmentController::class, 'store']);

Route::get('/pdf-test-simple', function () {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<meta charset="UTF-8">Latviešu test: Ā Č Ņ Ž ķ š ģ ļ ī ē ū');
    return $pdf->download('test.pdf');
});


Route::post('/documents/generate', [DocumentsController::class, 'generate']);
Route::post('/documents/generate-pdf', [DocumentsController::class, 'generatePdf']);

