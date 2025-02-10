<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSalesReports extends ListRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    // Ambil data order yang difilter
                    $orders = $this->getFilteredTableQuery()->get();

                    // Store filter values in session if not available in request
                    $fromDate = session('from_date', request()->input('from_date'));
                    $toDate = session('to_date', request()->input('to_date'));

                    // Save to session for future use
                    session(['from_date' => $fromDate, 'to_date' => $toDate]);

                    // Hitung total orders
                    $totalOrders = $orders->count();

                    // Hitung total amount
                    $totalAmount = $orders->sum('gross_amount');

                    // Buat PDF
                    $pdf = Pdf::loadView('reports.sales', [
                        'orders' => $orders,
                        'total_sales' => $orders->sum('total_amount'),
                        'total_items' => $orders->sum(function ($order) {
                        return $order->items->sum('quantity');
                    }),
                        'totalOrders' => $totalOrders,
                        'totalAmount' => $totalAmount,
                        'dateRange' => ['from' => $fromDate, 'to' => $toDate],
                    ]);

                    // Generate nama file
                    $filename = 'sales_report_' . now()->format('YmdHis') . '.pdf';

                    // Langsung return response download
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->stream();
                    }, $filename);
                }),
        ];
    }
}
