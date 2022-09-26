<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\zeroMove;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('savings_transactions', [zeroMove::class, 'savings_transactions']);
Route::get('target_transactions', [zeroMove::class, 'target_transactions']);
Route::get('wallet_transactions', [zeroMove::class, 'wallet_transactions']);
Route::get('share_holding_transactions', [zeroMove::class, 'share_holding_transactions']);
Route::get('procurement_repayment', [zeroMove::class, 'procurement_repayment']);
Route::get('loan_repayment', [zeroMove::class, 'loan_repayment']);
Route::get('membership_registration', [zeroMove::class, 'membership_registration']);
Route::get('membership_registration_fee', [zeroMove::class, 'membership_registration_fee']);
Route::get('enable_disable', [zeroMove::class, 'enable_disable']);


