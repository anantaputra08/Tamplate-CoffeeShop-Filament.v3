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
                    // Ambil data transaction yang difilter
                    $transactions = $this->getFilteredTableQuery()->with('items')->get();

                    // Store filter values in session if not available in request
                    $fromDate = session('from_date', request()->input('from_date'));
                    $toDate = session('to_date', request()->input('to_date'));

                    // Save to session for future use
                    session(['from_date' => $fromDate, 'to_date' => $toDate]);

                    // Hitung total orders
                    $totalOrders = $transactions->count();

                    // Hitung total amount
                    $totalAmount = $transactions->sum('gross_amount');

                    // Hitung total items dari tabel `transaction_items`
                    $totalItems = $transactions->sum(fn ($transaction) => $transaction->items->sum('quantity'));

                    // Buat PDF
                    $pdf = Pdf::loadView('sales', [
                        'transactions' => $transactions,
                        'total_sales' => $totalAmount,
                        'total_items' => $totalItems, // Tambahkan total item
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
