@extends('layouts.master')

@section('title', 'Deadlock Details')

@section('style')
<style>
    .json-display {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 400px;
        overflow-y: auto;
    }
    .status-badge {
        font-size: 1.1em;
        padding: 8px 16px;
    }
    .status-unresolved {
        background-color: #dc3545;
        color: white;
    }
    .status-resolved {
        background-color: #28a745;
        color: white;
    }
    .severity-error {
        background-color: #dc3545;
        color: white;
    }
    .severity-warning {
        background-color: #ffc107;
        color: black;
    }
    .severity-info {
        background-color: #17a2b8;
        color: white;
    }
    .query-display {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Deadlock Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">System Logs</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.deadlock-logs') }}">Deadlock Logs</a></li>
                    <li class="breadcrumb-item active">Deadlock #{{ $deadlock->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Deadlock Overview -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Deadlock Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Deadlock ID:</strong></td>
                                        <td>{{ $deadlock->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($deadlock->is_resolved)
                                                <span class="badge status-badge status-resolved">
                                                    <i class="fas fa-check-circle"></i> Resolved
                                                </span>
                                            @else
                                                <span class="badge status-badge status-unresolved">
                                                    <i class="fas fa-exclamation-triangle"></i> Unresolved
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Severity:</strong></td>
                                        <td>
                                            <span class="badge status-badge severity-{{ strtolower($deadlock->severity) }}">
                                                {{ $deadlock->severity }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Error Code:</strong></td>
                                        <td><span class="badge badge-danger">{{ $deadlock->error_code }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Database:</strong></td>
                                        <td>{{ $deadlock->database_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Table:</strong></td>
                                        <td>
                                            @if($deadlock->table_name)
                                                <span class="badge badge-info">{{ $deadlock->table_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Session ID:</strong></td>
                                        <td>{{ $deadlock->session_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Process ID:</strong></td>
                                        <td>{{ $deadlock->process_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>User:</strong></td>
                                        <td>{{ $deadlock->user_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Application:</strong></td>
                                        <td>{{ $deadlock->application_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Client IP:</strong></td>
                                        <td>{{ $deadlock->client_addr ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td>
                                            @if($deadlock->duration_ms)
                                                <span class="badge badge-secondary">{{ $deadlock->formatted_duration }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> Error Message
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>{{ $deadlock->error_code }}:</strong> {{ $deadlock->error_message }}
                        </div>
                    </div>
                </div>

                <!-- SQL Query -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-database"></i> SQL Query
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" onclick="copyToClipboard('{{ addslashes($deadlock->query_text) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="query-display">{{ $deadlock->query_text }}</div>
                    </div>
                </div>

                <!-- Deadlock Details -->
                @if($deadlock->deadlock_details)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info"></i> Deadlock Details
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" onclick="copyToClipboard('{{ addslashes($deadlock->deadlock_details) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="json-display">{{ $deadlock->deadlock_details }}</div>
                    </div>
                </div>
                @endif

                <!-- Involved Queries -->
                @if($deadlock->involved_queries && count($deadlock->involved_queries) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Involved Queries
                        </h3>
                    </div>
                    <div class="card-body">
                        @foreach($deadlock->involved_queries as $index => $query)
                            <div class="mb-3">
                                <h6>Query {{ $index + 1 }}:</h6>
                                <div class="query-display">{{ $query }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Lock Waits -->
                @if($deadlock->lock_waits && count($deadlock->lock_waits) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lock"></i> Lock Waits
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="json-display">{{ json_encode($deadlock->lock_waits, JSON_PRETTY_PRINT) }}</div>
                    </div>
                </div>
                @endif

                <!-- Stack Trace -->
                @if($deadlock->stack_trace && count($deadlock->stack_trace) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-code"></i> Stack Trace
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="json-display">{{ json_encode($deadlock->stack_trace, JSON_PRETTY_PRINT) }}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Actions & Timeline -->
            <div class="col-md-4">
                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i> Timeline
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-red">{{ $deadlock->created_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle bg-red"></i>
                                <div class="timeline-item">
                                    <span class="time">{{ $deadlock->created_at->format('H:i:s') }}</span>
                                    <h3 class="timeline-header">Deadlock Detected</h3>
                                    <div class="timeline-body">
                                        Deadlock occurred in {{ $deadlock->table_name ?? 'unknown table' }}
                                    </div>
                                </div>
                            </div>
                            
                            @if($deadlock->is_resolved && $deadlock->resolved_at)
                            <div>
                                <i class="fas fa-check bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time">{{ $deadlock->resolved_at->format('H:i:s') }}</span>
                                    <h3 class="timeline-header">Deadlock Resolved</h3>
                                    <div class="timeline-body">
                                        Resolved by: {{ $deadlock->resolved_by }}
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.logs.deadlock-logs') }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Deadlock Logs
                            </a>
                            
                            <button class="btn btn-info" onclick="copyFullDeadlock()">
                                <i class="fas fa-copy"></i> Copy Full Details
                            </button>
                            
                            @if(!$deadlock->is_resolved)
                                <button class="btn btn-success" onclick="resolveDeadlock()">
                                    <i class="fas fa-check"></i> Mark as Resolved
                                </button>
                            @endif
                            
                            <button class="btn btn-warning" onclick="analyzeDeadlock()">
                                <i class="fas fa-search"></i> Analyze Similar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resolution Info -->
                @if($deadlock->is_resolved)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle"></i> Resolution Info
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Resolved By:</strong><br>{{ $deadlock->resolved_by }}</p>
                        <p><strong>Resolved At:</strong><br>{{ $deadlock->resolved_at->format('M d, Y H:i:s') }}</p>
                        <p><strong>Resolution Time:</strong><br>
                            {{ $deadlock->created_at->diffForHumans($deadlock->resolved_at) }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Performance Impact -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i> Performance Impact
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Duration</span>
                                        <span class="info-box-number">{{ $deadlock->formatted_duration }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-database"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Table</span>
                                        <span class="info-box-number">{{ $deadlock->table_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
const deadlockId = {{ $deadlock->id }};
const deadlockData = @json($deadlock);

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        toastr.success('Data copied to clipboard');
    }, function(err) {
        console.error('Could not copy text: ', err);
        toastr.error('Failed to copy to clipboard');
    });
}

function copyFullDeadlock() {
    const fullDeadlockData = {
        id: deadlockData.id,
        status: deadlockData.is_resolved ? 'Resolved' : 'Unresolved',
        error_code: deadlockData.error_code,
        error_message: deadlockData.error_message,
        table_name: deadlockData.table_name,
        query_text: deadlockData.query_text,
        deadlock_details: deadlockData.deadlock_details,
        involved_queries: deadlockData.involved_queries,
        lock_waits: deadlockData.lock_waits,
        stack_trace: deadlockData.stack_trace,
        created_at: deadlockData.created_at,
        resolved_at: deadlockData.resolved_at,
        resolved_by: deadlockData.resolved_by
    };
    
    copyToClipboard(JSON.stringify(fullDeadlockData, null, 2));
}

function resolveDeadlock() {
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
    
    fetch(`/admin/logs/deadlock-logs/${deadlockId}/resolve`, {
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

function analyzeDeadlock() {
    if (confirm('This will search for similar deadlocks in the system. Continue?')) {
        // This would redirect to a search page with similar deadlock criteria
        const searchUrl = `/admin/logs/deadlock-logs?table_name=${encodeURIComponent(deadlockData.table_name || '')}&error_code=${encodeURIComponent(deadlockData.error_code || '')}`;
        window.open(searchUrl, '_blank');
    }
}
</script>
@endsection
