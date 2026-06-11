<!DOCTYPE html>
<html>
<head>
    <title>FloodSense - Professional Analytical Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #1a1a1a; font-size: 11px; }

        .page { padding: 40px 45px 80px; }

        /* ── Top Blue Bar ── */
        .top-bar {
            background: #1a52cc;
            color: white;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar-left { font-size: 13px; font-weight: bold; letter-spacing: 0.5px; }
        .top-bar-right { font-size: 10px; opacity: 0.85; text-align: right; line-height: 1.6; }

        /* ── Title Block ── */
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

        /* ── Note / Summary Box ── */
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

        /* ── Sensor Values ── */
        .sensor-val {
            font-weight: bold;
            color: #1a52cc;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            display: block;
            margin-bottom: 2px;
        }
        .val-unit { font-size: 9px; color: #888; font-weight: normal; font-family: Arial, sans-serif; }

        /* ── Threshold Labels ── */
        .limit-label {
            font-size: 8.5px;
            color: #888;
            text-transform: uppercase;
            display: block;
            letter-spacing: 0.3px;
        }
        .limit-val {
            font-weight: bold;
            color: #cc2200;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }

        /* ── Severity Badges ── */
        .badge {
            padding: 3px 8px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
            text-align: center;
            min-width: 60px;
            letter-spacing: 0.3px;
            border-radius: 2px;
        }
        .critical { background: #fde8e8; color: #cc2200; border: 1px solid #cc2200; }
        .warning  { background: #fff3e0; color: #e07800; border: 1px solid #e07800; }
        .info     { background: #eef3ff; color: #1a52cc; border: 1px solid #1a52cc; }

        /* ── Signature ── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .sig-table td {
            padding: 0 10px;
            width: 50%;
            text-align: left;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #333;
            padding-top: 6px;
            margin-top: 36px;
            width: 180px;
        }
        .sig-name { font-size: 10px; font-weight: bold; color: #1a1a1a; }
        .sig-role { font-size: 8.5px; color: #666; margin-top: 2px; }

        /* ── Fixed Footer ── */
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
            {{ $area_name }}<br>
            Kalu Ganga Basin | Rathnapura District, Sri Lanka<br>
            Disaster Management Centre — AI Prediction Unit
        </div>
        </div>
    </div>

    <div class="page">

        {{-- ── Title Block ── --}}
        <div class="title-block">
            <h1>Flood Prediction Intelligence Report</h1>
            <p>AI-Generated Analytical Report &mdash; Incident &amp; Sensor Analysis &mdash; {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
        </div>

        {{-- ── Report Info ── --}}
        <table class="info-table">
            <tr>
                <td class="info-label">Report ID</td>
                <td class="info-value">FS-{{ strtoupper(substr(md5(time()), 0, 8)) }}</td>
                <td class="info-label">Generated At</td>
                <td class="info-value">{{ $generated_at }}</td>
            </tr>
            <tr>
                <td class="info-label">Region</td>
                <td class="info-value">{{ $area_name }}</td>
                <td class="info-label">Period</td>
                <td class="info-value">{{ $from_date }} &mdash; {{ $to_date }}</td>
            </tr>
            <tr>
                <td class="info-label">Report Type</td>
                <td class="info-value">Incident Analytical Report</td>
                <td class="info-label">Total Records</td>
                <td class="info-value" style="color:#1a52cc; font-weight:bold;">{{ count($data) }}</td>
            </tr>
        </table>

        {{-- ── Analytical Note Box ── --}}
        <div class="note-box">
            <strong>Analytical Note:</strong> This report displays the specific sensor readings recorded during each incident.
            Safety Limits are retrieved from the <em>Alert Thresholds</em> configuration for <strong>{{ $area_name }}</strong>,
            showing both Warning and Critical levels for the relevant environmental factor.
        </div>

        {{-- ── Data Table ── --}}
        <div class="section-title">Incident Records &amp; Sensor Observations</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:16%;">Date / Time</th>
                    <th style="width:16%;">Incident</th>
                    
                    <th style="width:22%;">System Thresholds</th>
                    <th style="width:20%; text-align:center;">Severity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>
                        {{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}<br>
                        <span style="color:#888; font-size:9px;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }}</span>
                    </td>
                    <td style="font-weight:bold;">{{ $item->type }}</td>
                    <td>
                        @if($item->sensorReading)
                            <span class="sensor-val">Water: {{ $item->sensorReading->water_level }}<span class="val-unit"> m</span></span>
                            <span class="sensor-val">Rainfall: {{ $item->sensorReading->rainfall }}<span class="val-unit"> mm</span></span>
                        @else
                            <span style="color:#bbb; font-style:italic;">No Sensor Records</span>
                        @endif
                    </td>
                    <td>
                        @if($item->threshold)
                            @if(str_contains(strtolower($item->type), 'rain'))
                                <span class="limit-label">Rain Warning:</span>
                                <span class="limit-val">{{ $item->threshold->rain_warning_level }}<span class="val-unit"> mm</span></span>
                                <span class="limit-label" style="margin-top:4px;">Rain Critical:</span>
                                <span class="limit-val">{{ $item->threshold->rain_critical_level }}<span class="val-unit"> mm</span></span>
                            @else
                                <span class="limit-label">Water Warning:</span>
                                <span class="limit-val">{{ $item->threshold->water_warning_level }}<span class="val-unit"> m</span></span>
                                <span class="limit-label" style="margin-top:4px;">Water Critical:</span>
                                <span class="limit-val">{{ $item->threshold->water_critical_level }}<span class="val-unit"> m</span></span>
                            @endif
                        @else
                            <span style="color:#bbb;">N/A</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @php
                            $sev   = strtoupper($item->severity ?? 'INFO');
                            $class = $sev === 'CRITICAL' ? 'critical' : (in_array($sev, ['HIGH', 'WARNING']) ? 'warning' : 'info');
                        @endphp
                        <span class="badge {{ $class }}">{{ $sev }}</span>
                    </td>
                </tr>
                @endforeach

                @if(count($data) == 0)
                <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:#999; font-style:italic;">
                        No incidents were recorded within the specified parameters.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- ── Signatures ── --}}
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">Technical Lead</div>
                        <div class="sig-role">FloodSense AI Monitoring Unit</div>
                    </div>
                </td>
                <td style="text-align:right;">
                    <div class="sig-line" style="float:right;">
                        <div class="sig-name">{{ \Carbon\Carbon::now()->format('d F Y') }}</div>
                        <div class="sig-role">System Verified Date</div>
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