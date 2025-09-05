<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\FinancialReportService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdsPaymentsReportExport;
use App\Models\Ad;
use App\Exports\AdsReportExport;


class FinancialReportController extends Controller
{
   public function exportAds()
    {
        $ads = Ad::where('status', 'approved')->get();

        // المجموع وعدد الإعلانات
        $totalAds = $ads->count();
        $totalRevenue = $ads->sum('price');

        // إرسال الملف مع معلومات المجموع في الملف نفسه
        return Excel::download(new AdsReportExport, 'ads-report.xlsx');
    }
}
