<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daily Timesheet</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        .btn-green {
            background: #16a34a;
            color: white;
        }

        .btn-green:hover {
            background: #15803d;
        }

        .btn-blue {
            background: #2563eb;
            color: white;
        }

        .btn-blue:hover {
            background: #1d4ed8;
        }

        .btn-purple {
            background: #7c3aed;
            color: white;
        }

        .btn-purple:hover {
            background: #6d28d9;
        }

        .btn-red {
            background: #dc2626;
            color: white;
            padding: 0.25rem 0.75rem;
        }

        .btn-red:hover {
            background: #b91c1c;
        }

        .btn-yellow {
            background: #eab308;
            color: white;
            padding: 0.25rem 0.75rem;
        }

        .btn-yellow:hover {
            background: #ca8a04;
        }

        .alert {
            padding: 1rem;
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            color: #1e40af;
        }

        .alert-success {
            background: #d1fae5;
            border-color: #6ee7b7;
            color: #065f46;
        }

        .alert-warning {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #92400e;
        }

        .cutoff-info {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cutoff-info-text {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .cutoff-info-text i {
            font-size: 1.5rem;
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

        .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
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

        input[type="date"],
        input[type="time"],
        input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-clock"></i> Daily Timesheet</h1>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn btn-blue" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Add Entry
                </button>
                <button class="btn btn-purple" onclick="processCutoff()">
                    <i class="fa-solid fa-scissors"></i> Process Cut-Off
                </button>
                <a href="{{ route('timesheets.export') }}" class="btn btn-green">
                    <i class="fa-solid fa-download"></i> Export
                </a>
                @if(isset($archivedPeriods) && count($archivedPeriods) > 0)
                <button class="btn" onclick="viewArchives()" style="background: #6b7280; color: white;">
                    <i class="fa-solid fa-archive"></i> Archives
                </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('cutoff_summary'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <strong>Cut-off processed successfully!</strong><br>
                Period: {{ session('cutoff_summary')['date_range'] }}<br>
                Total Hours: {{ session('cutoff_summary')['total_hours'] }} hrs | 
                Net Balance: {{ session('cutoff_summary')['net_variance'] >= 0 ? '+' : '' }}{{ session('cutoff_summary')['net_variance'] }} hrs
            </div>
        @endif

        <!-- Current Cut-off Period Info -->
        <div class="cutoff-info">
            <div class="cutoff-info-text">
                <i class="fa-solid fa-calendar-days"></i>
                <div>
                    <div style="font-weight: 600; font-size: 1.125rem;">Current Period: {{ $currentPeriodLabel }}</div>
                    <div style="font-size: 0.875rem; opacity: 0.9;">{{ $timesheets->count() }} working days recorded</div>
                </div>
            </div>
        </div>

        <div class="alert">
            <i class="fa-solid fa-info-circle"></i> <strong>Note:</strong> Lunch break (12:00 PM - 1:00 PM) is automatically excluded. Standard: 8 hours/day
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
                        <i class="fa-solid fa-check-circle"></i> {{ number_format($netVariance, 2) }} extra hours!
                    @elseif($netVariance < 0)
                        <i class="fa-solid fa-exclamation-triangle"></i> Need {{ number_format(abs($netVariance), 2) }} more hours
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
                        <th style="text-align: center;"><i class="fa-solid fa-gears"></i> Actions</th>
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
                            <td class="actions">
                                <button class="btn btn-yellow" onclick="openEditModal({{ $timesheet->id }}, '{{ $timesheet->date->format('Y-m-d') }}', '{{ $timesheet->time_in }}', '{{ $timesheet->time_out }}')">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <form id="delete-form-{{ $timesheet->id }}" action="{{ route('timesheets.destroy', $timesheet) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $timesheet->id }}, '{{ $timesheet->date->format('M d, Y') }}')" class="btn btn-red">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fa-solid fa-inbox"></i> No entries yet. Click "Add Entry" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-plus-circle"></i> Add New Entry</h2>
            <form action="{{ route('timesheets.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label><i class="fa-solid fa-calendar"></i> Date</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time In</label>
                    <input type="time" name="time_in" step="1" required value="09:00:00">
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time Out</label>
                    <input type="time" name="time_out" step="1" required value="17:00:00">
                </div>
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeAddModal()" style="background: #6b7280; color: white;">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-blue">
                        <i class="fa-solid fa-floppy-disk"></i> Save Entry
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-pen-to-square"></i> Edit Entry</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label><i class="fa-solid fa-calendar"></i> Date</label>
                    <input type="date" name="date" id="edit_date" required>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time In</label>
                    <input type="time" name="time_in" step="1" id="edit_time_in" required>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time Out</label>
                    <input type="time" name="time_out" step="1" id="edit_time_out" required>
                </div>
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeEditModal()" style="background: #6b7280; color: white;">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-blue">
                        <i class="fa-solid fa-floppy-disk"></i> Update Entry
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }

        function openEditModal(id, date, timeIn, timeOut) {
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_time_in').value = timeIn;
            document.getElementById('edit_time_out').value = timeOut;
            document.getElementById('editForm').action = '/timesheets/' + id;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function confirmDelete(id, date) {
            Swal.fire({
                title: 'Delete Entry?',
                html: `Are you sure you want to delete the timesheet entry for <strong>${date}</strong>?<br><br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-trash"></i> Yes, delete it!',
                cancelButtonText: '<i class="fa-solid fa-xmark"></i> Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function processCutoff() {
            // Get the data from the page
            const periodLabel = "{{ $currentPeriodLabel }}";
            const workingDays = {{ $timesheets->count() }};
            const totalHours = {{ number_format($totalHours, 2) }};
            const netVariance = {{ $netVariance }};
            const netColor = netVariance >= 0 ? '#10b981' : '#ef4444';
            const netSign = netVariance >= 0 ? '+' : '';
            
            Swal.fire({
                title: '<i class="fa-solid fa-scissors"></i> Process Cut-Off Period?',
                html: `
                    <div style="text-align: left; padding: 1rem;">
                        <p style="margin-bottom: 1rem;">You are about to process the current cut-off period:</p>
                        <div style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                            <strong>Period:</strong> ${periodLabel}<br>
                            <strong>Working Days:</strong> ${workingDays}<br>
                            <strong>Total Hours:</strong> ${totalHours} hrs<br>
                            <strong>Net Balance:</strong> <span style="color: ${netColor};">${netSign}${Math.abs(netVariance).toFixed(2)} hrs</span>
                        </div>
                        <p style="color: #dc2626; margin-bottom: 0.5rem;"><strong>⚠️ Warning:</strong></p>
                        <p style="color: #6b7280;">This will archive all entries in this period. They will be moved to the archives and cannot be edited.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-check"></i> Yes, process cut-off',
                cancelButtonText: '<i class="fa-solid fa-xmark"></i> Cancel',
                reverseButtons: true,
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/timesheets/cutoff';
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function viewArchives() {
            window.location.href = '/timesheets/archives';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>