@extends('layouts.master')

@section('title', 'Deadlock Logs')

@section('style')
<style>
    .deadlock-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .deadlock-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .status-unresolved {
        color: #dc3545;
        font-weight: bold;
    }
    .status-resolved {
        color: #28a745;
        font-weight: bold;
    }
    .severity-error {
        color: #dc3545;
    }
    .severity-warning {
        color: #ffc107;
    }
    .severity-info {
        color: #17a2b8;
    }
    .query-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
    .duration-badge {
        font-size: 0.8em;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Deadlock Logs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">System Logs</a></li>
                    <li class="breadcrumb-item active">Deadlock Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="total-deadlocks">-</h3>
                        <p>Total Deadlocks</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="unresolved-deadlocks">-</h3>
                        <p>Unresolved</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="resolved-deadlocks">-</h3>
                        <p>Resolved</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="today-deadlocks">-</h3>
                        <p>Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filters
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.logs.deadlock-logs') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="unresolved" {{ request('status') == 'unresolved' ? 'selected' : '' }}>Unresolved</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Severity</label>
                                <select name="severity" class="form-control">
                                    <option value="">All Severity</option>
                                    @foreach($severities as $severity)
                                        <option value="{{ $severity }}" {{ request('severity') == $severity ? 'selected' : '' }}>
                                            {{ ucfirst($severity) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Table</label>
                                <select name="table_name" class="form-control">
                                    <option value="">All Tables</option>
                                    @foreach($tableNames as $table)
                                        <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>
                                            {{ $table }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Error Code</label>
                                <select name="error_code" class="form-control">
                                    <option value="">All Codes</option>
                                    @foreach($errorCodes as $code)
                                        <option value="{{ $code }}" {{ request('error_code') == $code ? 'selected' : '' }}>
                                            {{ $code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('admin.logs.deadlock-logs') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </a>
                                    <button type="button" class="btn btn-warning" onclick="triggerMonitoring()">
                                        <i class="fas fa-search"></i> Monitor Now
                                    </button>
                                    <a href="{{ route('admin.logs.deadlock-export', request()->query()) }}" class="btn btn-success">
                                        <i class="fas fa-download"></i> Export CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deadlock Logs Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Deadlock Logs ({{ $deadlocks->total() }} total)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $deadlocks->count() }} shown</span>
                </div>
            </div>
            <div class="card-body">
                @if($deadlocks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Table</th>
                                    <th>Error Code</th>
                                    <th>Query Preview</th>
                                    <th>Duration</th>
                                    <th>User</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deadlocks as $deadlock)
                                    <tr>
                                        <td>{{ $deadlock->id }}</td>
                                        <td>
                                            @if($deadlock->is_resolved)
                                                <span class="status-resolved">
                                                    <i class="fas fa-check-circle"></i> Resolved
                                                </span>
                                            @else
                                                <span class="status-unresolved">
                                                    <i class="fas fa-exclamation-triangle"></i> Unresolved
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($deadlock->table_name)
                                                <span class="badge badge-info">{{ $deadlock->table_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">{{ $deadlock->error_code }}</span>
                                        </td>
                                        <td>
                                            <div class="query-preview" title="{{ $deadlock->query_text }}">
                                                {{ $deadlock->query_text }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($deadlock->duration_ms)
                                                <span class="badge badge-secondary duration-badge">
                                                    {{ $deadlock->formatted_duration }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($deadlock->user_name)
                                                <small>{{ $deadlock->user_name }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $deadlock->created_at->format('M d, Y') }}</small>
                                            <br><small class="text-muted">{{ $deadlock->created_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.logs.deadlock-detail', $deadlock->id) }}" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$deadlock->is_resolved)
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="resolveDeadlock({{ $deadlock->id }})" 
                                                        title="Mark as Resolved">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $deadlocks->firstItem() }} to {{ $deadlocks->lastItem() }} 
                            of {{ $deadlocks->total() }} results
                        </div>
                        <div>
                            {{ $deadlocks->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No deadlock logs found</h5>
                        <p class="text-muted">Try adjusting your filters or check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Resolve Deadlock Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1" role="dialog" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resolveModalLabel">
                    <i class="fas fa-check"></i> Resolve Deadlock
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="resolveForm">
                    <div class="form-group">
                        <label for="resolvedBy">Resolved By</label>
                        <input type="text" class="form-control" id="resolvedBy" name="resolved_by" 
                               value="{{ auth()->user()->user_name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any notes about how this deadlock was resolved..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmResolve()">
                    <i class="fas fa-check"></i> Mark as Resolved
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let currentDeadlockId = null;

// Load statistics on page load
$(document).ready(function() {
    loadStatistics();
    
    // Auto-submit form on filter change
    $('select[name="status"], select[name="severity"], select[name="table_name"], select[name="error_code"]').on('change', function() {
        $(this).closest('form').submit();
    });
});

function loadStatistics() {
    fetch('/admin/logs/deadlock-statistics')
        .then(response => response.json())
        .then(data => {
            $('#total-deadlocks').text(data.total.toLocaleString());
            $('#unresolved-deadlocks').text(data.unresolved.toLocaleString());
            $('#resolved-deadlocks').text(data.resolved.toLocaleString());
            $('#today-deadlocks').text(data.today.toLocaleString());
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

function triggerMonitoring() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Monitoring...';
    button.disabled = true;
    
    fetch('/admin/logs/deadlock-monitor', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Deadlock monitoring completed. Found ' + data.deadlocks_found + ' deadlocks.');
            // Reload the page to show new deadlocks
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            toastr.error('Monitoring failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Monitoring failed');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function resolveDeadlock(deadlockId) {
    currentDeadlockId = deadlockId;
    $('#resolveModal').modal('show');
}

function confirmResolve() {
    const resolvedBy = document.getElementById('resolvedBy').value;
    const notes = document.getElementById('notes').value;
    
    if (!resolvedBy.trim()) {
        toastr.error('Please enter who resolved this deadlock');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resolving...';
    button.disabled = true;
    
    fetch(`/admin/logs/deadlock-logs/${currentDeadlockId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            resolved_by: resolvedBy,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Deadlock resolved successfully');
            $('#resolveModal').modal('hide');
            // Reload the page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            toastr.error('Failed to resolve deadlock: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Failed to resolve deadlock');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endsection
