<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Trait Exportable
 *
 * Provides export functionality for PDF, Excel, and CSV
 */
trait Exportable
{
    /**
     * Export data to Excel format
     *
     * @param array $data
     * @param array $headers
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    protected function exportToExcel(array $data, array $headers, string $filename = 'export')
    {
        try {
            // Create Excel file using Laravel Excel
            $filename = $filename . '-' . date('Ymd');
            
            return Excel::download(new class($data, $headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
                private $data;
                private $headers;

                public function __construct($data, $headers)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                }

                public function array(): array
                {
                    return $this->data;
                }

                public function headings(): array
                {
                    return $this->headers;
                }

                public function map($row): array
                {
                    return $row;
                }
            }, $filename . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data to PDF format
     *
     * @param array $data
     * @param string $view
     * @param string $filename
     * @param array $options
     * @return \Illuminate\Http\Response
     */
    protected function exportToPdf(array $data, string $view, string $filename = 'export', array $options = [])
    {
        try {
            $pdf = Pdf::loadView($view, $data);
            
            // Apply options
            if (isset($options['paper'])) {
                $pdf->setPaper($options['paper'], $options['orientation'] ?? 'portrait');
            }
            
            $filename = $filename . '-' . date('Ymd') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data to CSV format
     *
     * @param array $data
     * @param array $headers
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function exportToCsv(array $data, array $headers = [], string $filename = 'export')
    {
        return Response::streamDownload(function () use ($data, $headers) {
            $output = fopen('php://output', 'w');

            // Write BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write headers
            if (!empty($headers)) {
                fputcsv($output, $headers);
            }

            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename . '-' . date('Ymd') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '-' . date('Ymd') . '.csv"',
        ]);
    }

    /**
     * Download template file
     *
     * @param string $type
     * @param string $template
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    protected function downloadTemplate(string $type, string $template)
    {
        $templatePath = storage_path('app/templates/' . $template . '.' . $type);

        if (file_exists($templatePath)) {
            return Response::download($templatePath);
        }

        return response()->json([
            'success' => false,
            'message' => 'Template not found'
        ], 404);
    }

    /**
     * Generate Excel export response (streaming)
     *
     * @param string $exportClass
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function streamExcel(string $exportClass, string $filename = 'export')
    {
        return Excel::download(new $exportClass(), $filename . '-' . date('Ymd') . '.xlsx');
    }

    /**
     * Generate PDF with custom HTML
     *
     * @param string $html
     * @param string $filename
     * @param array $options
     * @return \Illuminate\Http\Response
     */
    protected function renderPdf(string $html, string $filename = 'export', array $options = [])
    {
        try {
            $pdf = Pdf::loadHTML($html);
            
            if (isset($options['paper'])) {
                $pdf->setPaper($options['paper'], $options['orientation'] ?? 'portrait');
            }
            
            return $pdf->download($filename . '-' . date('Ymd') . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PDF generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
