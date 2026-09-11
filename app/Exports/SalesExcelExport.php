<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesExcelExport
{
    /**
     * Build and return the StreamedResponse containing the designed Excel report.
     *
     * @param  Collection<int, Order>  $orders
     * @param  Collection<int, OrderItem>  $itemSalesRecap
     * @param  array{totalRevenue: int, totalCost: int, totalProfit: int, totalOrdersCount: int, totalItemsSold: int, otsCount: int, poCount: int}  $metrics
     * @param  array{channel: string, dateFilter: string, periodLabel: string, search: ?string}  $filters
     */
    public static function download(
        Collection $orders,
        Collection $itemSalesRecap,
        array $metrics,
        array $filters
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet;

        // Set document metadata
        $spreadsheet->getProperties()
            ->setCreator('SIPA Merch POS System')
            ->setLastModifiedBy('SIPA Merch Admin')
            ->setTitle('Laporan Penjualan SIPA Festival')
            ->setSubject('Rekapitulasi Penjualan & Transaksi Merchandise')
            ->setDescription('Laporan penjualan resmi SIPA Merchandise Festival dengan desain format profesional.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Penjualan');
        $sheet->setShowGridLines(true);

        // Color Palette Constants
        $colorDarkNavy = '13161B';
        $colorDarkSlate = '1E293B';
        $colorCrimson = 'E63946';
        $colorEmerald = '10B981';
        $colorLightZebra = 'F8FAFC';
        $colorBorder = 'CBD5E1';
        $colorCardBorder = 'E2E8F0';

        // 1. HEADER BANNER (Row 1 - 2)
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'SIPA FESTIVAL 2026 — OFFICIAL MERCHANDISE');
        $sheet->getRowDimension(1)->setRowHeight(34);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 15,
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF'.$colorDarkNavy],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', 'LAPORAN & REKAPITULASI PENJUALAN KASIR POS');
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
                'bold' => true,
                'color' => ['argb' => 'FFFCA5A5'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. METADATA SECTION (Row 4 - 5)
        $sheet->getRowDimension(3)->setRowHeight(8);

        $sheet->setCellValue('A4', 'Periode Laporan:');
        $sheet->setCellValue('B4', $filters['periodLabel']);
        $sheet->mergeCells('B4:D4');

        $sheet->setCellValue('F4', 'Waktu Unduh:');
        $sheet->setCellValue('G4', Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm').' WIB');
        $sheet->mergeCells('G4:I4');

        $sheet->setCellValue('A5', 'Kanal Penjualan:');
        $channelText = match ($filters['channel']) {
            'ots' => 'On The Spot (OTS)',
            'po' => 'Pre-Order (PO)',
            default => 'Semua Kanal (OTS & PO)',
        };
        $sheet->setCellValue('B5', $channelText);
        $sheet->mergeCells('B5:D5');

        $sheet->setCellValue('F5', 'Kata Kunci:');
        $sheet->setCellValue('G5', ! empty($filters['search']) ? $filters['search'] : '(Semua Data)');
        $sheet->mergeCells('G5:I5');

        $sheet->getStyle('A4:A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
        ]);
        $sheet->getStyle('B4:D5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF0F172A']],
        ]);
        $sheet->getStyle('F4:F5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
        ]);
        $sheet->getStyle('G4:I5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF0F172A']],
        ]);

        // 3. EXECUTIVE KPI CARDS (Row 7 - 8)
        $sheet->getRowDimension(6)->setRowHeight(10);
        $sheet->getRowDimension(7)->setRowHeight(18);
        $sheet->getRowDimension(8)->setRowHeight(28);

        // Card 1: Total Omset (B7:C8)
        $sheet->mergeCells('B7:C7');
        $sheet->setCellValue('B7', 'TOTAL OMSET');
        $sheet->mergeCells('B8:C8');
        $sheet->setCellValue('B8', $metrics['totalRevenue']);
        self::styleKpiCard($sheet, 'B7:C8', 'B7', 'B8', 'FEE2E2', '991B1B', 'FEF2F2', $colorCrimson, true);

        // Card 2: Total HPP (D7:E8)
        $sheet->mergeCells('D7:E7');
        $sheet->setCellValue('D7', 'TOTAL HPP (MODAL)');
        $sheet->mergeCells('D8:E8');
        $sheet->setCellValue('D8', $metrics['totalCost']);
        self::styleKpiCard($sheet, 'D7:E8', 'D7', 'D8', 'F1F5F9', '475569', 'F8FAFC', '334155', true);

        // Card 3: Laba Kotor (F7:G8)
        $sheet->mergeCells('F7:G7');
        $sheet->setCellValue('F7', 'LABA KOTOR');
        $sheet->mergeCells('F8:G8');
        $sheet->setCellValue('F8', $metrics['totalProfit']);
        self::styleKpiCard($sheet, 'F7:G8', 'F7', 'F8', 'D1FAE5', '065F46', 'ECFDF5', '059669', true);

        // Card 4: Total Item Terjual (H7:I8)
        $sheet->mergeCells('H7:I7');
        $sheet->setCellValue('H7', 'TOTAL ITEM TERJUAL');
        $sheet->mergeCells('H8:I8');
        $sheet->setCellValue('H8', number_format($metrics['totalItemsSold'], 0, ',', '.').' Pcs');
        self::styleKpiCard($sheet, 'H7:I8', 'H7', 'H8', 'DBEAFE', '1E40AF', 'EFF6FF', '1D4ED8', false);

        // Card 5: Total Transaksi (J7:K8)
        $sheet->mergeCells('J7:K7');
        $sheet->setCellValue('J7', 'TOTAL TRANSAKSI');
        $sheet->mergeCells('J8:K8');
        $sheet->setCellValue('J8', $metrics['totalOrdersCount'].' ('.$metrics['otsCount'].' OTS / '.$metrics['poCount'].' PO)');
        self::styleKpiCard($sheet, 'J7:K8', 'J7', 'J8', 'F3E8FF', '6B21A8', 'FAF5FF', '7E22CE', false);

        // 4. SECTION 1: REKAPITULASI PENJUALAN PER PRODUK
        $sheet->getRowDimension(9)->setRowHeight(14);
        $sheet->setCellValue('A10', 'I. REKAPITULASI KUANTITI & OMSET PER MERCHANDISE');
        $sheet->getStyle('A10')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF'.$colorDarkNavy]],
        ]);

        $itemHeaders = [
            'A11' => 'NO',
            'B11' => 'NAMA MERCHANDISE',
            'C11' => 'QTY TERJUAL (PCS)',
            'D11' => 'TOTAL OMSET (RP)',
            'E11' => 'TOTAL HPP (RP)',
            'F11' => 'ESTIMASI LABA (RP)',
            'G11' => 'KONTRIBUSI OMSET',
        ];

        $sheet->getRowDimension(11)->setRowHeight(24);
        foreach ($itemHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A11:G11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF'.$colorDarkSlate],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF475569']],
            ],
        ]);

        $currentRow = 12;
        $totalSalesSum = max(1, (int) $metrics['totalRevenue']);

        if ($itemSalesRecap->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data barang terjual pada periode ini.');
            $sheet->getStyle("A{$currentRow}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8'], 'size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $currentRow++;
        } else {
            $no = 1;
            foreach ($itemSalesRecap as $recap) {
                $qty = (int) $recap->total_qty;
                $sales = (int) $recap->total_sales;
                $cost = (int) ($recap->total_cost ?? 0);
                $profit = (int) ($recap->total_profit ?? ($sales - $cost));
                $contribution = $sales / $totalSalesSum;

                $sheet->setCellValue("A{$currentRow}", $no);
                $sheet->setCellValue("B{$currentRow}", $recap->product_name);
                $sheet->setCellValue("C{$currentRow}", $qty);
                $sheet->setCellValue("D{$currentRow}", $sales);
                $sheet->setCellValue("E{$currentRow}", $cost);
                $sheet->setCellValue("F{$currentRow}", $profit);
                $sheet->setCellValue("G{$currentRow}", $contribution);

                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("G{$currentRow}")->getNumberFormat()->setFormatCode('0.0%');

                if ($no % 2 === 0) {
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF'.$colorLightZebra],
                    ]);
                }

                $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getBorders()->getAllBorders()->applyFromArray([
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF'.$colorBorder],
                ]);

                $no++;
                $currentRow++;
            }

            // Total Summary Row for Table 1
            $startDataRow = 12;
            $endDataRow = $currentRow - 1;

            $sheet->mergeCells("A{$currentRow}:B{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'TOTAL KESELURUHAN');
            $sheet->setCellValue("C{$currentRow}", "=SUM(C{$startDataRow}:C{$endDataRow})");
            $sheet->setCellValue("D{$currentRow}", "=SUM(D{$startDataRow}:D{$endDataRow})");
            $sheet->setCellValue("E{$currentRow}", "=SUM(E{$startDataRow}:E{$endDataRow})");
            $sheet->setCellValue("F{$currentRow}", "=SUM(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$currentRow}", '100.0%');

            $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF0F172A']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF94A3B8']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF475569']],
                ],
            ]);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $currentRow++;
        }

        // 5. SECTION 2: RINCIAN SELURUH TRANSAKSI KASIR
        $currentRow += 2;
        $sheet->setCellValue("A{$currentRow}", 'II. RINCIAN TRANSAKSI KASIR & PRE-ORDER');
        $sheet->getStyle("A{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF'.$colorDarkNavy]],
        ]);

        $currentRow++;
        $orderHeaders = [
            "A{$currentRow}" => 'NO',
            "B{$currentRow}" => 'NO. FAKTUR',
            "C{$currentRow}" => 'WAKTU',
            "D{$currentRow}" => 'KANAL',
            "E{$currentRow}" => 'NAMA PELANGGAN',
            "F{$currentRow}" => 'NO. WHATSAPP',
            "G{$currentRow}" => 'RINCIAN MERCHANDISE (ITEM x QTY)',
            "H{$currentRow}" => 'METODE',
            "I{$currentRow}" => 'STATUS BAYAR',
            "J{$currentRow}" => 'TOTAL TAGIHAN (RP)',
            "K{$currentRow}" => 'UANG DITERIMA (RP)',
            "L{$currentRow}" => 'KEMBALIAN (RP)',
            "M{$currentRow}" => 'CATATAN PESANAN / PO',
        ];

        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        foreach ($orderHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRow = $currentRow;
        $sheet->getStyle("A{$headerRow}:M{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF'.$colorDarkNavy],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF334155']],
            ],
        ]);

        $currentRow++;
        $startOrderRow = $currentRow;

        if ($orders->isEmpty()) {
            $sheet->mergeCells("A{$currentRow}:M{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'Belum ada data transaksi yang sesuai filter.');
            $sheet->getStyle("A{$currentRow}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8'], 'size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $currentRow++;
        } else {
            $no = 1;
            foreach ($orders as $order) {
                // Build readable item summary
                $itemStrings = [];
                foreach ($order->items as $item) {
                    $variantStr = $item->variant ? " ({$item->variant})" : '';
                    $itemStrings[] = "{$item->product_name}{$variantStr} x{$item->quantity}";
                }
                $itemSummary = implode('; ', $itemStrings);

                $sheet->setCellValue("A{$currentRow}", $no);
                $sheet->setCellValueExplicit("B{$currentRow}", '#'.$order->order_number, DataType::TYPE_STRING);
                $sheet->setCellValue("C{$currentRow}", $order->created_at->format('d/m/Y H:i'));
                $sheet->setCellValue("D{$currentRow}", strtoupper($order->channel));
                $sheet->setCellValue("E{$currentRow}", $order->customer_name ?: '-');
                $sheet->setCellValueExplicit("F{$currentRow}", $order->customer_phone ?: '-', DataType::TYPE_STRING);
                $sheet->setCellValue("G{$currentRow}", $itemSummary);
                $sheet->setCellValue("H{$currentRow}", strtoupper($order->payment_method));
                $sheet->setCellValue("I{$currentRow}", strtoupper($order->payment_status));
                $sheet->setCellValue("J{$currentRow}", (int) $order->total_price);
                $sheet->setCellValue("K{$currentRow}", (int) $order->amount_paid);
                $sheet->setCellValue("L{$currentRow}", (int) $order->change_amount);
                $sheet->setCellValue("M{$currentRow}", $order->customer_notes ?: '-');

                // Alignments
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("H{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$currentRow}:L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Formats
                $sheet->getStyle("J{$currentRow}:L{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

                // Zebra striping
                if ($no % 2 === 0) {
                    $sheet->getStyle("A{$currentRow}:M{$currentRow}")->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF'.$colorLightZebra],
                    ]);
                }

                // Borders
                $sheet->getStyle("A{$currentRow}:M{$currentRow}")->getBorders()->getAllBorders()->applyFromArray([
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF'.$colorBorder],
                ]);

                $no++;
                $currentRow++;
            }

            // Summary row for orders
            $endOrderRow = $currentRow - 1;
            $sheet->mergeCells("A{$currentRow}:I{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'TOTAL DARI RINCIAN TRANSAKSI DI ATAS');
            $sheet->setCellValue("J{$currentRow}", "=SUM(J{$startOrderRow}:J{$endOrderRow})");
            $sheet->setCellValue("K{$currentRow}", "=SUM(K{$startOrderRow}:K{$endOrderRow})");
            $sheet->setCellValue("L{$currentRow}", "=SUM(L{$startOrderRow}:L{$endOrderRow})");
            $sheet->setCellValue("M{$currentRow}", '');

            $sheet->getStyle("A{$currentRow}:M{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF0F172A']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF94A3B8']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF475569']],
                ],
            ]);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("J{$currentRow}:L{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        }

        // Auto-sizing columns with sensible padding
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set specific min widths for wide content
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(38);
        $sheet->getColumnDimension('M')->setWidth(26);

        // Prepare Streamed Response
        $writer = new Xlsx($spreadsheet);
        $cleanDate = Carbon::now()->format('Ymd_Hi');
        $filename = "Laporan_Penjualan_SIPA_Merch_{$cleanDate}.xlsx";

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Helper to style executive KPI cards.
     */
    private static function styleKpiCard(
        $sheet,
        string $range,
        string $headerCell,
        string $valueCell,
        string $headerBg,
        string $headerText,
        string $valueBg,
        string $valueText,
        bool $isCurrency
    ): void {
        // Header
        $sheet->getStyle($headerCell)->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF'.$headerText]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Value
        $sheet->getStyle($valueCell)->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF'.$valueText]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$valueBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        if ($isCurrency) {
            $sheet->getStyle($valueCell)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        }

        // Outer Border
        $sheet->getStyle($range)->getBorders()->getAllBorders()->applyFromArray([
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFE2E8F0'],
        ]);
    }
}
