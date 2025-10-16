<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - {{ $dateRange['label'] }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #ddd6fe 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        h1 {
            font-size: 1.875rem;
            color: #1f2937;
            font-weight: bold;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-gray {
            background: #6b7280;
            color: white;
        }

        .btn-gray:hover {
            background: #4b5563;
        }

        .btn-green {
            background: #16a34a;
            color: white;
        }

        .btn-green:hover {
            background: #15803d;
        }

        .archive-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #7c3aed;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem;
            border-radius: 0.5rem;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .summary-card.total {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .summary-card.overtime {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .summary-card.undertime {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
        }

        .summary-card.net {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }

        .summary-card.net.negative {
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
        }

        .summary-card-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-card-value {
            font-size: 2rem;
            font-weight: bold;
        }

        .summary-card-subtitle {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }

        tr:hover {
            background: #f9fafb;
        }

        .hours-display {
            font-weight: 600;
            font-size: 1.125rem;
            color: #1f2937;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-overtime {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-undertime {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-on-time {
            background: #d1fae5;
            color: #065f46;
        }

        .variance {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .variance-positive {
            color: #2563eb;
        }

        .variance-negative {
            color: #dc2626;
        }

        .info-box {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            color: #92400e;
        }

        .info-box i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fa-solid fa-folder-open"></i> Archived Period</h1>
                <div style="margin-top: 0.5rem;">
                    <span class="archive-badge">
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $dateRange['label'] }}
                    </span>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('timesheets.archive.export', $period) }}" class="btn btn-green">
                    <i class="fa-solid fa-download"></i> Export CSV
                </a>
                <a href="{{ route('timesheets.archives') }}" class="btn btn-gray">
                    <i class="fa-solid fa-arrow-left"></i> Back to Archives
                </a>
            </div>
        </div>

        <div class="info-box">
            <i class="fa-solid fa-lock"></i>
            <strong>Read-Only Archive:</strong> This period has been processed and archived. Entries cannot be modified.
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card total">
                <div class="summary-card-label">
                    <i class="fa-solid fa-clock"></i> Total Hours Worked
                </div>
                <div class="summary-card-value">{{ number_format($totalHours, 2) }}</div>
                <div class="summary-card-subtitle">Expected: {{ number_format($expectedHours, 2) }} hrs</div>
            </div>

            <div class="summary-card overtime">
                <div class="summary-card-label">
                    <i class="fa-solid fa-arrow-up"></i> Total Overtime
                </div>
                <div class="summary-card-value">+{{ number_format($totalOvertime, 2) }}</div>
                <div class="summary-card-subtitle">Extra hours rendered</div>
            </div>

            <div class="summary-card undertime">
                <div class="summary-card-label">
                    <i class="fa-solid fa-arrow-down"></i> Total Undertime
                </div>
                <div class="summary-card-value">-{{ number_format($totalUndertime, 2) }}</div>
                <div class="summary-card-subtitle">Hours short</div>
            </div>

            <div class="summary-card net {{ $netVariance >= 0 ? '' : 'negative' }}">
                <div class="summary-card-label">
                    <i class="fa-solid fa-scale-balanced"></i> Net Balance
                </div>
                <div class="summary-card-value">
                    {{ $netVariance >= 0 ? '+' : '' }}{{ number_format($netVariance, 2) }}
                </div>
                <div class="summary-card-subtitle">
                    @if($netVariance > 0)
                        <i class="fa-solid fa-check-circle"></i> {{ number_format($netVariance, 2) }} extra hours rendered
                    @elseif($netVariance < 0)
                        <i class="fa-solid fa-exclamation-triangle"></i> {{ number_format(abs($netVariance), 2) }} hours short
                    @else
                        <i class="fa-solid fa-check-circle"></i> Perfect! On-time
                    @endif
                </div>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-calendar"></i> Date</th>
                        <th><i class="fa-solid fa-arrow-right-to-bracket"></i> Time In</th>
                        <th><i class="fa-solid fa-arrow-right-from-bracket"></i> Time Out</th>
                        <th><i class="fa-solid fa-hourglass-half"></i> Hours Worked</th>
                        <th><i class="fa-solid fa-chart-line"></i> Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timesheets as $timesheet)
                        <tr>
                            <td>{{ $timesheet->date->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($timesheet->time_in)->format('h:i:s A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($timesheet->time_out)->format('h:i:s A') }}</td>
                            <td class="hours-display">{{ $timesheet->hours_worked }} hrs</td>
                            <td>
                                <span class="status-badge status-{{ $timesheet->status }}">
                                    @if($timesheet->status == 'overtime')
                                        <i class="fa-solid fa-arrow-up"></i> OVERTIME
                                    @elseif($timesheet->status == 'undertime')
                                        <i class="fa-solid fa-arrow-down"></i> UNDERTIME
                                    @else
                                        <i class="fa-solid fa-check"></i> ON-TIME
                                    @endif
                                </span>
                                @if($timesheet->variance_hours != 0)
                                    <div class="variance {{ $timesheet->variance_hours > 0 ? 'variance-positive' : 'variance-negative' }}">
                                        @if($timesheet->variance_hours > 0)
                                            +{{ $timesheet->variance_hours }} hrs excess
                                        @else
                                            {{ $timesheet->variance_hours }} hrs ({{ abs($timesheet->variance_hours) }} hrs needed)
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fa-solid fa-inbox"></i> No entries found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>