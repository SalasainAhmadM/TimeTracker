<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daily Timesheet</title>
    <!-- Font Awesome -->
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

        tfoot tr {
            background: #f3f4f6;
            font-weight: bold;
        }

        tfoot td {
            font-size: 1.125rem;
        }

        .total-hours {
            color: #2563eb;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-clock"></i> Daily Timesheet</h1>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-blue" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Add New Entry
                </button>
                <a href="{{ route('timesheets.export') }}" class="btn btn-green">
                    <i class="fa-solid fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <div class="alert">
            <i class="fa-solid fa-info-circle"></i> <strong>Note:</strong> Lunch break (12:00 PM - 1:00 PM) is automatically excluded from calculations
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-calendar"></i> Date</th>
                        <th><i class="fa-solid fa-arrow-right-to-bracket"></i> Time In</th>
                        <th><i class="fa-solid fa-arrow-right-from-bracket"></i> Time Out</th>
                        <th><i class="fa-solid fa-hourglass-half"></i> Hours Worked</th>
                        <th style="text-align: center;"><i class="fa-solid fa-gears"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timesheets as $timesheet)
                        <tr>
                            <td>{{ $timesheet->date->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($timesheet->time_in)->format('h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($timesheet->time_out)->format('h:i A') }}</td>
                            <td class="hours-display">{{ $timesheet->hours_worked }} hrs</td>
                            <td class="actions">
                                <button class="btn btn-yellow" onclick="openEditModal({{ $timesheet->id }}, '{{ $timesheet->date->format('Y-m-d') }}', '{{ $timesheet->time_in }}', '{{ $timesheet->time_out }}')">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <form action="{{ route('timesheets.destroy', $timesheet) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-red">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fa-solid fa-inbox"></i> No entries yet. Click "Add New Entry" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;">TOTAL HOURS:</td>
                        <td class="total-hours">{{ number_format($totalHours, 2) }} hrs</td>
                        <td></td>
                    </tr>
                </tfoot>
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
                    <input type="time" name="time_in" required value="09:00">
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time Out</label>
                    <input type="time" name="time_out" required value="17:00">
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
                    <input type="time" name="time_in" id="edit_time_in" required>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-clock"></i> Time Out</label>
                    <input type="time" name="time_out" id="edit_time_out" required>
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>