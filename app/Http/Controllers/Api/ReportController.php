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

class ReportController extends Controller
{
    /**
     * 1. කලින් සෑදූ වාර්තා Archive එක පෙන්වීමට
     */
    public function index()
    {
        $reports = Report::with('area')->orderBy('created_at', 'desc')->get();
        return response()->json($reports);
    }

    /**
     * 2. වාර්තාවක් සාදා (PDF/Excel) Database එකේ Store කිරීම
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_type'   => 'required|string',
            'export_format' => 'required|string',
            'area_id'       => 'nullable', 
            'from_date'     => 'required|date',
            'to_date'       => 'required|date',
        ]);

        // --- 1. දත්ත ලබා ගැනීම (Data Query) ---
        $query = Alert::query();

        if ($request->filled('area_id') && $request->area_id !== 'null' && $request->area_id !== '') {
            $query->where('area_id', $request->area_id);
        }

        $data = $query->with(['area', 'threshold', 'sensorReading'])
                      ->whereBetween('created_at', [
                          $request->from_date . " 00:00:00", 
                          $request->to_date . " 23:59:59"
                      ])
                      ->orderBy('created_at', 'desc')
                      ->get();

        $areaName = $request->area_id ? Area::find($request->area_id)->name : 'All Regions';
        $format = strtoupper($request->export_format);
        $generatedAt = now()->format('Y-m-d H:i:s');
        
        $fileName = "Report_" . time();
        $filePath = "";
        $output = "";

        // --- 2. තෝරාගත් Format එක අනුව ගොනුව සැකසීම ---
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
            $pdf = Pdf::loadView('pdf.report', $pdfData);
            $output = $pdf->output();
        } 
        else if ($format === 'EXCEL' || $format === 'CSV') {
            // Excel/XLS සඳහා HTML Table ව්‍යුහය භාවිතා කිරීම (Professional Method)
            $filePath = "reports/" . $fileName . ".xls";
            $output = '
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>
            <body>
                <table border="1">
                    <tr><th colspan="6" style="background-color: #eee; font-size: 16px;">' . $request->report_type . '</th></tr>
                    <tr><th colspan="6">Region: ' . $areaName . ' | Period: ' . $request->from_date . ' to ' . $request->to_date . '</th></tr>
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
                        <td>' . $item->created_at . '</td>
                        <td>' . $item->type . '</td>
                        <td>' . ($item->area->name ?? "Global") . '</td>
                        <td>' . strtoupper($item->severity) . '</td>
                        <td>' . ($item->sensorReading->water_level ?? "0") . '</td>
                        <td>' . ($item->sensorReading->rainfall ?? "0") . '</td>
                    </tr>';
            }
            $output .= '</table></body></html>';
        }

        // --- 3. Storage එකේ Save කිරීම ---
        Storage::disk('public')->put($filePath, $output);

        // --- 4. Report Table එකේ Record එකක් දැමීම ---
        $report = Report::create([
            'name'      => $request->report_type . " - " . date('M d, Y'),
            'type'      => $request->report_type,
            'format'    => $format,
            'area_id'   => ($request->area_id == 'null' || $request->area_id == '') ? null : $request->area_id,
            'file_path' => $filePath, 
            'file_size' => round(strlen($output) / 1024 / 1024, 2),
        ]);

        return response()->json([
            'message' => 'Report generated successfully!',
            'data'    => $report->load('area')
        ], 201);
    }

    /**
     * 3. වාර්තාවක් මකා දැමීම (Delete)
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        // Storage එකේ ඇති ගොනුව (PDF/Excel) ඉවත් කිරීම
        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        // Database record එක ඉවත් කිරීම
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}