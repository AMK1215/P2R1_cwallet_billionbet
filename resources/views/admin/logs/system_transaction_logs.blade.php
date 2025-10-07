@extends('layouts.master')

@section('title', 'System Transaction Logs')

@section('style')
<style>
    .system-warning {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .log-type {
        font-weight: bold;
    }
    .log-type.deposit {
        color: #28a745;
    }
    .log-type.withdraw {
        color: #dc3545;
    }
    .log-type.balance {
        color: #007bff;
    }
    .status-success {
        color: #28a745;
        font-weight: bold;
    }
    .status-failure {
        color: #dc3545;
        font-weight: bold;
    }
    .status-partial {
        color: #ffc107;
        font-weight: bold;
    }
    .request-data {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: 'Courier New', monospace;
        font-size: 0.8em;
    }
    .response-data {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: 'Courier New', monospace;
        font-size: 0.8em;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">System Transaction Logs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">System Logs</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.system-logs') }}">System Logs Dashboard</a></li>
                    <li class="breadcrumb-item active">Transaction Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- System Warning -->
        <div class="system-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> System Logs - Admin Only</h5>
            <p class="mb-0">
                <strong>Warning:</strong> This section contains sensitive system transaction logs. 
                These logs are not visible to clients and are used for system administration and debugging purposes only.
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $logs->total() }}</h3>
                        <p>Total Logs</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $logs->where('status', 'success')->count() }}</h3>
                        <p>Successful</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $logs->where('status', '!=', 'success')->count() }}</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $logs->count() }}</h3>
                        <p>Showing</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-eye"></i>
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
                <form method="GET" action="{{ route('admin.logs.system-transaction-logs') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                    <a href="{{ route('admin.logs.system-transaction-logs') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </a>
                                    <a href="{{ route('admin.logs.system-export-logs', request()->query()) }}" class="btn btn-success">
                                        <i class="fas fa-download"></i> Export CSV
                                    </a>
                                    <a href="{{ route('admin.logs.system-logs') }}" class="btn btn-info">
                                        <i class="fas fa-broom"></i> Cleanup Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transaction Logs Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> System Transaction Logs ({{ $logs->total() }} total)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $logs->count() }} shown</span>
                </div>
            </div>
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Request Data</th>
                                    <th>Response Data</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <span class="log-type {{ $log->type }}">
                                                {{ ucfirst($log->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->status === 'success')
                                                <span class="status-success">
                                                    <i class="fas fa-check-circle"></i> Success
                                                </span>
                                            @elseif($log->status === 'failure')
                                                <span class="status-failure">
                                                    <i class="fas fa-times-circle"></i> Failure
                                                </span>
                                            @else
                                                <span class="status-partial">
                                                    <i class="fas fa-exclamation-triangle"></i> {{ ucfirst($log->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="request-data" title="{{ json_encode($log->batch_request, JSON_PRETTY_PRINT) }}">
                                                {{ json_encode($log->batch_request) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="response-data" title="{{ json_encode($log->response_data, JSON_PRETTY_PRINT) }}">
                                                {{ json_encode($log->response_data) }}
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, Y') }}</small>
                                            <br><small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewLogDetails({{ $log->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary" onclick="copyLogData({{ $log->id }})" title="Copy Data">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} 
                            of {{ $logs->total() }} results
                        </div>
                        <div>
                            {{ $logs->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No transaction logs found</h5>
                        <p class="text-muted">Try adjusting your filters or check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="fas fa-info-circle"></i> Transaction Log Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading log details...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" onclick="copyFullLogData()">
                    <i class="fas fa-copy"></i> Copy Full Data
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let currentLogData = null;

// Auto-submit form on filter change
$(document).ready(function() {
    $('select[name="type"], select[name="status"]').on('change', function() {
        $(this).closest('form').submit();
    });
});

function viewLogDetails(logId) {
    $('#logDetailsModal').modal('show');
    
    // Find the log data from the current page
    const logRow = $(`button[onclick="viewLogDetails(${logId})"]`).closest('tr');
    const type = logRow.find('.log-type').text().trim();
    const status = logRow.find('.status-success, .status-failure, .status-partial').text().trim();
    const requestData = logRow.find('.request-data').attr('title');
    const responseData = logRow.find('.response-data').attr('title');
    const createdAt = logRow.find('td:nth-child(6) small:first').text();
    
    currentLogData = {
        id: logId,
        type: type,
        status: status,
        request_data: requestData,
        response_data: responseData,
        created_at: createdAt
    };
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Basic Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>ID:</strong></td><td>${logId}</td></tr>
                    <tr><td><strong>Type:</strong></td><td><span class="badge badge-primary">${type}</span></td></tr>
                    <tr><td><strong>Status:</strong></td><td>${status}</td></tr>
                    <tr><td><strong>Created:</strong></td><td>${createdAt}</td></tr>
                </table>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h6>Request Data</h6>
                <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">${requestData}</pre>
            </div>
            <div class="col-md-6">
                <h6>Response Data</h6>
                <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">${responseData}</pre>
            </div>
        </div>
    `;
    
    $('#logDetailsContent').html(html);
}

function copyLogData(logId) {
    const logRow = $(`button[onclick="viewLogDetails(${logId})"]`).closest('tr');
    const requestData = logRow.find('.request-data').attr('title');
    const responseData = logRow.find('.response-data').attr('title');
    
    const logData = {
        id: logId,
        request_data: JSON.parse(requestData),
        response_data: JSON.parse(responseData)
    };
    
    copyToClipboard(JSON.stringify(logData, null, 2));
}

function copyFullLogData() {
    if (currentLogData) {
        copyToClipboard(JSON.stringify(currentLogData, null, 2));
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        toastr.success('Data copied to clipboard');
    }, function(err) {
        console.error('Could not copy text: ', err);
        toastr.error('Failed to copy to clipboard');
    });
}
</script>
@endsection
