<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Area;
use App\Models\Alert;
use App\Models\SensorReading;
use App\Models\AlertThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ReportController extends Controller
{

    public function index()
    {
        $reports = Report::with('area')->orderBy('created_at', 'desc')->get();
        return response()->json($reports);
    }


    public function store(Request $request)
    {
        $request->validate([
            'report_type'   => 'required|string',
            'export_format' => 'required|string',
            'area_id'       => 'nullable',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date',
        ]);


        $query = Alert::query();

        // Standardize stringified 'null' evaluations sent via frontend forms
        $hasAreaId = $request->filled('area_id') && $request->area_id !== 'null' && $request->area_id !== '';

        if ($hasAreaId) {
            $query->where('area_id', $request->area_id);
        }

        $data = $query->with(['area', 'threshold', 'sensorReading'])
                      ->whereBetween('created_at', [
                          $request->from_date . " 00:00:00",
                          $request->to_date . " 23:59:59"
                      ])
                      ->orderBy('created_at', 'desc')
                      ->get();

        // FIX: Prevent fatal null property crashes by verifying record existence first
        $areaName = 'All Regions';
        if ($hasAreaId) {
            $area = Area::find($request->area_id);
            if ($area) {
                $areaName = $area->name;
            }
        }

        $format = strtoupper($request->export_format);
        $generatedAt = now()->format('Y-m-d H:i:s');

        $fileName = "Report_" . time();
        $filePath = "";
        $output = "";


        if ($format === 'PDF') {
            $filePath = "reports/" . $fileName . ".pdf";
            $pdfData = [
                'title'        => $request->report_type,
                'area_name'    => $areaName,
                'from_date'    => $request->from_date,
                'to_date'      => $request->to_date,
                'data'         => $data,
                'generated_at' => $generatedAt
            ];

            try {
                $pdf = Pdf::loadView('pdf.report', $pdfData);
                $output = $pdf->output();
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed compiling PDF view template.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        else if ($format === 'EXCEL' || $format === 'CSV') {

            $filePath = "reports/" . $fileName . ".xls";
            $output = '
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>
            <body>
                <table border="1">
                    <tr><th colspan="6" style="background-color: #eee; font-size: 16px;">' . e($request->report_type) . '</th></tr>
                    <tr><th colspan="6">Region: ' . e($areaName) . ' | Period: ' . e($request->from_date) . ' to ' . e($request->to_date) . '</th></tr>
                    <tr style="background-color: #1a1a1a; color: #ffffff; font-weight: bold;">
                        <th>Date/Time</th>
                        <th>Incident Type</th>
                        <th>Region</th>
                        <th>Severity</th>
                        <th>Water Level (m)</th>
                        <th>Rainfall (mm)</th>
                    </tr>';

            foreach ($data as $item) {
                $output .= '
                    <tr>
                        <td>' . e($item->created_at) . '</td>
                        <td>' . e($item->type) . '</td>
                        <td>' . e($item->area->name ?? "Global") . '</td>
                        <td>' . e(strtoupper($item->severity)) . '</td>
                        <td>' . e($item->sensorReading->water_level ?? "0") . '</td>
                        <td>' . e($item->sensorReading->rainfall ?? "0") . '</td>
                    </tr>';
            }
            $output .= '</table></body></html>';
        }

        try {
            // Check and build file structures cleanly prior to file placements
            if (!Storage::disk('public')->exists('reports')) {
                Storage::disk('public')->makeDirectory('reports');
            }

            Storage::disk('public')->put($filePath, $output);

            // Calculate sizing cleanly into Kilobytes (KB) to prevent fractional decimals breaking down
            $rawLength = strlen($output);
            $fileSizeKb = $rawLength > 0 ? round($rawLength / 1024, 2) : 0;

            $report = Report::create([
                'name'      => $request->report_type . " - " . date('M d, Y'),
                'type'      => $request->report_type,
                'format'    => $format,
                'area_id'   => $hasAreaId ? $request->area_id : null,
                'file_path' => $filePath,
                'file_size' => $fileSizeKb,
            ]);

            return response()->json([
                'message' => 'Report generated successfully!',
                'data'    => $report->load('area')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed saving report file or database entity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        $report = Report::findOrFail($id);


        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }


        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}
