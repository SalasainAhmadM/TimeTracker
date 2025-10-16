<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timesheet Archives</title>
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
            max-width: 1200px;
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
            margin-bottom: 2rem;
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

        .btn-blue {
            background: #2563eb;
            color: white;
        }

        .btn-blue:hover {
            background: #1d4ed8;
        }

        .btn-green {
            background: #16a34a;
            color: white;
        }

        .btn-green:hover {
            background: #15803d;
        }

        .archive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .archive-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem;
            border-radius: 0.5rem;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .archive-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }

        .archive-card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .archive-card-date {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .archive-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .info-box {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            color: #374151;
        }

        .info-box i {
            color: #2563eb;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-archive"></i> Timesheet Archives</h1>
            <a href="{{ route('timesheets.index') }}" class="btn btn-gray">
                <i class="fa-solid fa-arrow-left"></i> Back to Active
            </a>
        </div>

        <div class="info-box">
            <i class="fa-solid fa-info-circle"></i>
            <strong>About Archives:</strong> These are completed cut-off periods that have been processed and archived. Click on any period to view detailed records.
        </div>

        @if($archivedPeriods->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h2 style="color: #374151; margin-bottom: 0.5rem;">No Archives Yet</h2>
                <p>Archived periods will appear here after you process a cut-off.</p>
            </div>
        @else
            <div class="archive-grid">
                @foreach($archivedPeriods as $archive)
                    <div class="archive-card" onclick="window.location.href='{{ route('timesheets.archive.show', $archive['period']) }}'">
                        <div class="archive-card-title">
                            <i class="fa-solid fa-calendar-days"></i>
                            Period: {{ $archive['period'] }}
                        </div>
                        <div class="archive-card-date">
                            {{ $archive['label'] }}
                        </div>
                        <div class="archive-actions" onclick="event.stopPropagation();">
                            <a href="{{ route('timesheets.archive.show', $archive['period']) }}" class="btn btn-blue" style="flex: 1; justify-content: center;">
                                <i class="fa-solid fa-eye"></i> View Details
                            </a>
                            <a href="{{ route('timesheets.archive.export', $archive['period']) }}" class="btn btn-green">
                                <i class="fa-solid fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>