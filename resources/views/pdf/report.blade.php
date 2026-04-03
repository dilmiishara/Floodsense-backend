<!DOCTYPE html>
<html>
<head>
    <title>FloodSense - Professional Analytical Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .container { padding: 30px; }
        
        /* Header Section */
        .header { text-align: center; border-bottom: 3px solid #1a1a1a; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #1a1a1a; font-size: 22px; }
        .header p { margin: 5px 0 0; font-size: 14px; font-weight: bold; color: #555; }
        
        /* Meta/Summary Info */
        .report-info { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .report-info td { font-size: 12px; vertical-align: top; padding: 4px 0; }
        .info-label { font-weight: bold; color: #1a1a1a; width: 120px; }
        
        /* Summary Box */
        .summary-box { 
            background: #f8f9fa; 
            border-left: 5px solid #1a1a1a;
            padding: 12px; 
            margin-bottom: 25px; 
        }
        .summary-box span { font-size: 11px; color: #444; line-height: 1.4; }
        
        /* Table Styling */
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .table th { 
            background-color: #1a1a1a; 
            color: white; 
            padding: 12px 8px; 
            text-align: left; 
            font-size: 10px; 
            text-transform: uppercase; 
        }
        .table td { 
            padding: 12px 8px; 
            border-bottom: 1px solid #eee; 
            font-size: 11px; 
            vertical-align: middle;
        }
        
        /* Severity Badges */
        .badge { padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 10px; color: white; display: inline-block; text-align: center; width: 70px; }
        .critical { background-color: #d32f2f; }
        .warning { background-color: #f57c00; }
        .info { background-color: #1976d2; }
        
        /* Value Styling */
        .val-unit { font-size: 10px; color: #777; font-weight: normal; }
        .sensor-val { font-weight: bold; color: #2e7d32; display: block; margin-bottom: 2px; }
        .limit-label { font-size: 9px; color: #888; text-transform: uppercase; display: block; }
        .limit-val { font-weight: bold; color: #c62828; }

        .footer { 
            position: fixed; 
            bottom: 20px; 
            width: 100%; 
            text-align: center; 
            font-size: 9px; 
            color: #999; 
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>FloodSense Monitoring System</h2>
            <p>{{ $title }}</p>
        </div>

        <table class="report-info">
            <tr>
                <td class="info-label">Region:</td>
                <td>{{ $area_name }}</td>
                <td class="info-label">Report ID:</td>
                <td style="text-align: right;">FS-{{ strtoupper(substr(md5(time()), 0, 8)) }}</td>
            </tr>
            <tr>
                <td class="info-label">Period:</td>
                <td>{{ $from_date }} to {{ $to_date }}</td>
                <td class="info-label">Generated At:</td>
                <td style="text-align: right;">{{ $generated_at }}</td>
            </tr>
        </table>

        <div class="summary-box">
            <span><strong>Analytical Note:</strong> This report displays the specific sensor readings recorded during each incident. Safety Limits are retrieved from the <i>Alert Thresholds</i> configuration for <strong>{{ $area_name }}</strong>, showing both Warning and Critical levels for the relevant environmental factor.</span>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="18%">Date/Time</th>
                    <th width="18%">Incident</th>
                    <th width="28%">Recorded Observations</th>
                    <th width="21%">System Thresholds</th>
                    <th width="15%" style="text-align: center;">Severity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}<br><small style="color: #888;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i:s') }}</small></td>
                    <td style="font-weight: bold;">{{ $item->type }}</td>
                    <td>
                        @if($item->sensorReading)
                            <div class="sensor-val">Water: {{ $item->sensorReading->water_level }}<span class="val-unit">m</span></div>
                            <div class="sensor-val">Rainfall: {{ $item->sensorReading->rainfall }}<span class="val-unit">mm</span></div>
                        @else
                            <span style="color: #bbb; font-style: italic;">No Sensor Records</span>
                        @endif
                    </td>
                    <td>
                        @if($item->threshold)
                            @if(str_contains(strtolower($item->type), 'rain'))
                                {{-- Rainfall Alert එකක් නම් Rain Thresholds පෙන්වන්න --}}
                                <span class="limit-label">Rain Warning:</span>
                                <span class="limit-val">{{ $item->threshold->rain_warning_level }}<span class="val-unit">mm</span></span>
                                <span class="limit-label" style="margin-top: 4px;">Rain Critical:</span>
                                <span class="limit-val">{{ $item->threshold->rain_critical_level }}<span class="val-unit">mm</span></span>
                            @else
                                {{-- Flood/Water Alert එකක් නම් Water Thresholds පෙන්වන්න --}}
                                <span class="limit-label">Water Warning:</span>
                                <span class="limit-val">{{ $item->threshold->water_warning_level }}<span class="val-unit">m</span></span>
                                <span class="limit-label" style="margin-top: 4px;">Water Critical:</span>
                                <span class="limit-val">{{ $item->threshold->water_critical_level }}<span class="val-unit">m</span></span>
                            @endif
                        @else
                            <span style="color: #bbb;">N/A</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @php
                            $sev = strtoupper($item->severity ?? 'INFO');
                            $class = $sev == 'CRITICAL' ? 'critical' : (in_array($sev, ['HIGH', 'WARNING']) ? 'warning' : 'info');
                        @endphp
                        <span class="badge {{ $class }}">{{ $sev }}</span>
                    </td>
                </tr>
                @endforeach
                
                @if(count($data) == 0)
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #999; font-style: italic;">
                        No incidents were recorded within the specified parameters.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <div style="margin-top: 60px; font-size: 11px;">
            <table width="100%">
                <tr>
                    <td width="50%">
                        <div style="border-top: 1px solid #333; width: 180px; padding-top: 5px;">
                            <strong>Technical Lead</strong>
                        </div>
                        <span style="font-size: 9px; color: #666;">FloodSense AI Monitoring Unit</span>
                    </td>
                    <td width="50%" style="text-align: right;">
                        <div style="border-top: 1px solid #333; width: 180px; padding-top: 5px; float: right;">
                            <strong>System Verified Date</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        FloodSense AI Dashboard | Confidential Analytical Report | {{ date('Y') }} &copy; Project FloodSense
    </div>
</body>
</html>