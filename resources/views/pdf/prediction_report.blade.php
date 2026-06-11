<!DOCTYPE html>
<html>
<head>
    <title>FloodSense - Flood Prediction Analysis Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #1a1a1a; font-size: 11px; }

        .page { padding: 40px 45px 60px; }

        /* ── Top Header Bar ── */
        .top-bar {
            background: #1a52cc;
            color: white;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        .top-bar-left { font-size: 13px; font-weight: bold; letter-spacing: 0.5px; }
        .top-bar-right { font-size: 10px; opacity: 0.85; text-align: right; line-height: 1.6; }

        /* ── Report Title Block ── */
        .title-block {
            border: 1px solid #1a52cc;
            border-top: none;
            padding: 14px 20px;
            margin-bottom: 20px;
            background: #f8faff;
        }
        .title-block h1 {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title-block p {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }

        /* ── Info Table ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #c5d2ee;
        }
        .info-table td {
            padding: 7px 12px;
            font-size: 10px;
            border: 1px solid #c5d2ee;
            vertical-align: top;
        }
        .info-label {
            background: #eef3ff;
            font-weight: bold;
            color: #1a52cc;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
            width: 100px;
        }
        .info-value {
            color: #1a1a1a;
            font-weight: 600;
        }

        /* ── Summary Stats ── */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-table td {
            border: 1px solid #c5d2ee;
            padding: 10px 14px;
            text-align: center;
            width: 25%;
        }
        .stats-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            display: block;
            margin-bottom: 4px;
        }
        .stats-value {
            font-size: 22px;
            font-weight: bold;
            display: block;
            line-height: 1;
        }
        .stats-sub {
            font-size: 8px;
            color: #888;
            display: block;
            margin-top: 3px;
        }
        .stat-blue   { border-top: 3px solid #1a52cc; } .stat-blue   .stats-value { color: #1a52cc; }
        .stat-yellow { border-top: 3px solid #b8a000; } .stat-yellow .stats-value { color: #b8a000; }
        .stat-orange { border-top: 3px solid #e07800; } .stat-orange .stats-value { color: #e07800; }
        .stat-red    { border-top: 3px solid #cc2200; } .stat-red    .stats-value { color: #cc2200; }

        /* ── Section Title ── */
        .section-title {
            background: #1a52cc;
            color: white;
            padding: 7px 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
        }

        /* ── Main Data Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #c5d2ee;
        }
        .data-table thead tr {
            background: #eef3ff;
        }
        .data-table th {
            padding: 8px 8px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #1a52cc;
            border: 1px solid #c5d2ee;
            text-align: left;
        }
        .data-table td {
            padding: 8px 8px;
            font-size: 10px;
            border: 1px solid #dde4f5;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background: #f8faff;
        }
        .data-table tbody tr:last-child td {
            border-bottom: 2px solid #1a52cc;
        }

        /* ── Row number ── */
        .row-num {
            color: #aaa;
            font-size: 9px;
            text-align: center;
        }

        /* ── Water level ── */
        .water-val {
            font-weight: bold;
            color: #1a52cc;
            font-family: 'Courier New', monospace;
            font-size: 11px;
        }

        /* ── Risk Badges ── */
        .badge {
            padding: 3px 8px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
            text-align: center;
            min-width: 52px;
            letter-spacing: 0.3px;
            border-radius: 2px;
        }
        .b-major  { background: #fde8e8; color: #cc2200; border: 1px solid #cc2200; }
        .b-minor  { background: #fff3e0; color: #e07800; border: 1px solid #e07800; }
        .b-alert  { background: #fefce0; color: #b8a000; border: 1px solid #b8a000; }

        /* ── Note Box ── */
        .note-box {
            border: 1px solid #c5d2ee;
            border-left: 4px solid #1a52cc;
            padding: 9px 12px;
            margin-bottom: 20px;
            background: #f8faff;
            font-size: 9.5px;
            color: #444;
            line-height: 1.6;
        }

        /* ── Disclaimer ── */
        .disclaimer {
            border: 1px solid #dde4f5;
            padding: 9px 12px;
            margin-bottom: 24px;
            background: #fafafa;
            font-size: 8.5px;
            color: #777;
            line-height: 1.6;
        }

        /* ── Signature ── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .sig-table td {
            padding: 0 10px;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #333;
            padding-top: 6px;
            margin-top: 36px;
        }
        .sig-name { font-size: 10px; font-weight: bold; color: #1a1a1a; }
        .sig-role { font-size: 8.5px; color: #666; margin-top: 2px; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            border-top: 2px solid #1a52cc;
            padding: 5px 40px;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #666;
            background: white;
        }
        .footer-center { color: #1a52cc; font-weight: bold; }
    </style>
</head>
<body>

    {{-- ── Top Blue Bar ── --}}
    <div class="top-bar">
        <div class="top-bar-left">
            FLOODSENSE MONITORING SYSTEM
        </div>
        <div class="top-bar-right">
            Kalu Ganga Basin | Rathnapura District, Sri Lanka<br>
            Disaster Management Centre — AI Prediction Unit
        </div>
    </div>

    <div class="page">

        {{-- ── Title Block ── --}}
        <div class="title-block">
            <h1>Flood Alert Report</h1>
            <p>AI-Generated Flood Risk Forecast &mdash; Gauging Station Analysis &mdash; {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
        </div>

        {{-- ── Report Info ── --}}
        <table class="info-table">
            <tr>
                <td class="info-label">Report ID</td>
                <td class="info-value">FS-{{ strtoupper(substr(md5(time()), 0, 8)) }}</td>
                <td class="info-label">Generated At</td>
                <td class="info-value">{{ $generated_at }}</td>
                <td class="info-label">Prepared By</td>
                <td class="info-value">FloodSense AI System</td>
            </tr>
            <tr>
                <td class="info-label">Station</td>
                <td class="info-value">{{ $station_name ?? 'All Stations' }}</td>
                <td class="info-label">Period From</td>
                <td class="info-value">{{ $from_date }}</td>
                <td class="info-label">Period To</td>
                <td class="info-value">{{ $to_date }}</td>
            </tr>
            <tr>
                <td class="info-label">Report Type</td>
                <td class="info-value">Flood Prediction Analysis</td>
                <td class="info-label">Risk Levels Shown</td>
                <td class="info-value">Alert, Minor Flood, Major Flood</td>
                <td class="info-label">Total Records</td>
                <td class="info-value" style="color:#1a52cc; font-weight:bold;">{{ count($data) }}</td>
            </tr>
        </table>

        {{-- ── Stats Row ── --}}
        @php
            $alertCount = $data->filter(fn($i) => strtolower($i->flood_risk_level) === 'alert')->count();
            $minorCount = $data->filter(fn($i) => strtolower($i->flood_risk_level) === 'minor')->count();
            $majorCount = $data->filter(fn($i) => strtolower($i->flood_risk_level) === 'major')->count();
        @endphp
        <table class="stats-table">
            <tr>
                <td class="stat-blue">
                    <span class="stats-label">Total Events</span>
                    <span class="stats-value">{{ count($data) }}</span>
                    <span class="stats-sub">All Risk Levels</span>
                </td>
                <td class="stat-yellow">
                    <span class="stats-label">Alert Level</span>
                    <span class="stats-value">{{ $alertCount }}</span>
                    <span class="stats-sub">Watch — Monitor</span>
                </td>
                <td class="stat-orange">
                    <span class="stats-label">Minor Flood</span>
                    <span class="stats-value">{{ $minorCount }}</span>
                    <span class="stats-sub">Warning — Act Now</span>
                </td>
                <td class="stat-red">
                    <span class="stats-label">Major Flood</span>
                    <span class="stats-value">{{ $majorCount }}</span>
                    <span class="stats-sub">Critical — Evacuate</span>
                </td>
            </tr>
        </table>

        {{-- ── Data Table ── --}}
        <div class="section-title">Flood Risk Prediction Records</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:3%; text-align:center;">#</th>
                    <th style="width:11%;">Created Date</th>
                    <th style="width:11%;">Forecast Time</th>
                    <th style="width:12%;">Station</th>
                    <th style="width:11%;">Water Level (m)</th>
                    <th style="width:9%;">Risk Level</th>
                    <th style="width:11%;">Affected Area</th>
                    <th style="width:9%;">Rainfall (mm)</th>
                    <th style="width:9%;">Temp (°C)</th>
                    <th style="width:9%;">Humidity (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                @php
                    $level = strtolower($item->flood_risk_level ?? 'alert');
                    $cls   = $level === 'major' ? 'b-major' : ($level === 'minor' ? 'b-minor' : 'b-alert');
                @endphp
                <tr>
                    <td class="row-num" style="text-align:center;">{{ $index + 1 }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}<br>
                        <span style="color:#888; font-size:9px;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</span>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($item->forecast_time)->format('d M Y') }}<br>
                        <span style="color:#888; font-size:9px;">{{ \Carbon\Carbon::parse($item->forecast_time)->format('H:i') }}</span>
                    </td>
                    <td style="font-weight:bold;">{{ $item->station_name }}</td>
                    <td>
                        <span class="water-val">{{ number_format($item->predicted_water_level, 3) }}</span>
                        <span style="font-size:9px; color:#888;"> m</span>
                    </td>
                    <td><span class="badge {{ $cls }}">{{ strtoupper($level) }}</span></td>
                    <td>
                        {{ $item->affected_area_sqkm > 0
                            ? number_format($item->affected_area_sqkm, 2) . ' km²'
                            : '—' }}
                    </td>
                    <td>{{ $item->rainfall ?? '—' }}</td>
                    <td>{{ $item->temperature ?? '—' }}</td>
                    <td>{{ $item->humidity ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:30px; color:#999; font-style:italic;">
                        No flood prediction records found within the specified parameters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ── Signatures ── --}}
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">Technical Officer</div>
                        <div class="sig-role">FloodSense AI Monitoring Unit</div>
                    </div>
                </td>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">Authorized Officer</div>
                        <div class="sig-role">Disaster Management Centre</div>
                    </div>
                </td>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">{{ \Carbon\Carbon::now()->format('d F Y') }}</div>
                        <div class="sig-role">Date of Issue</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

    {{-- ── Fixed Footer ── --}}
    <div class="footer">
        <span>CONFIDENTIAL — For Authorized Personnel Only</span>
        <span class="footer-center">FloodSense AI Dashboard &mdash; Rathnapura District Flood Monitoring</span>
        <span>{{ date('Y') }} &copy; Faculty of Technology, University of Colombo</span>
    </div>

</body>
</html>