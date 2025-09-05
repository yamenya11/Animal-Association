<?php

namespace App\Exports;

use App\Models\Ad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
class AdsReportExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      return Ad::select('id', 'title', 'price', 'status', 'approved_by', 'approved_at', 'created_at')
            ->get();
    }

      public function headings(): array
    {
        return ['ID', 'Title', 'Price', 'Status', 'Approved By', 'Approved At', 'Created At'];
    }
}
