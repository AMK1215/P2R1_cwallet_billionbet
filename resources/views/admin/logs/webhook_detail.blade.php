@extends('layouts.master')

@section('title', 'Webhook Log Details')

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
    .status-success {
        background-color: #28a745;
        color: white;
    }
    .status-failure {
        background-color: #dc3545;
        color: white;
    }
    .status-partial {
        background-color: #ffc107;
        color: black;
    }
    .log-type {
        font-weight: bold;
        font-size: 1.2em;
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
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Webhook Log Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">System Logs</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.webhook-logs') }}">Webhook Logs</a></li>
                    <li class="breadcrumb-item active">Log #{{ $log->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Log Overview -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Webhook Log Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Log ID:</strong></td>
                                        <td>{{ $log->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td>
                                            <span class="log-type {{ $log->type }}">
                                                {{ ucfirst($log->type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($log->status === 'success')
                                                <span class="badge status-badge status-success">
                                                    <i class="fas fa-check-circle"></i> Success
                                                </span>
                                            @elseif($log->status === 'failure')
                                                <span class="badge status-badge status-failure">
                                                    <i class="fas fa-times-circle"></i> Failure
                                                </span>
                                            @else
                                                <span class="badge status-badge status-partial">
                                                    <i class="fas fa-exclamation-triangle"></i> {{ ucfirst($log->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Created At:</strong></td>
                                        <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Updated At:</strong></td>
                                        <td>{{ $log->updated_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td>
                                            @if($log->created_at && $log->updated_at)
                                                {{ $log->created_at->diffInMilliseconds($log->updated_at) }}ms
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Request Data -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-arrow-right"></i> Request Data
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" onclick="copyToClipboard('{{ addslashes(json_encode($log->batch_request, JSON_PRETTY_PRINT)) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="json-display">{{ json_encode($log->batch_request, JSON_PRETTY_PRINT) }}</div>
                    </div>
                </div>

                <!-- Response Data -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-arrow-left"></i> Response Data
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" onclick="copyToClipboard('{{ addslashes(json_encode($log->response_data, JSON_PRETTY_PRINT)) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="json-display">{{ json_encode($log->response_data, JSON_PRETTY_PRINT) }}</div>
                    </div>
                </div>
            </div>

            <!-- Analysis & Actions -->
            <div class="col-md-4">
                <!-- Request Analysis -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i> Request Analysis
                        </h3>
                    </div>
                    <div class="card-body">
                        @php
                            $batchRequests = $log->batch_request['batch_requests'] ?? [];
                            $totalRequests = count($batchRequests);
                            $successCount = 0;
                            $errorCount = 0;
                            
                            if (isset($log->response_data['data'])) {
                                foreach ($log->response_data['data'] as $response) {
                                    if (isset($response['code']) && $response['code'] === 0) {
                                        $successCount++;
                                    } else {
                                        $errorCount++;
                                    }
                                }
                            }
                        @endphp
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-list"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Requests</span>
                                        <span class="info-box-number">{{ $totalRequests }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Successful</span>
                                        <span class="info-box-number">{{ $successCount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($errorCount > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger">
                                        <i class="fas fa-times"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Failed</span>
                                        <span class="info-box-number">{{ $errorCount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" style="width: {{ $totalRequests > 0 ? ($successCount / $totalRequests) * 100 : 0 }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $totalRequests > 0 ? ($errorCount / $totalRequests) * 100 : 0 }}%"></div>
                        </div>
                        
                        <small class="text-muted">
                            Success Rate: {{ $totalRequests > 0 ? round(($successCount / $totalRequests) * 100, 1) : 0 }}%
                        </small>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info"></i> Request Details
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(isset($log->batch_request['operator_code']))
                            <p><strong>Operator Code:</strong><br>
                            <code>{{ $log->batch_request['operator_code'] }}</code></p>
                        @endif
                        
                        @if(isset($log->batch_request['currency']))
                            <p><strong>Currency:</strong><br>
                            <span class="badge badge-info">{{ $log->batch_request['currency'] }}</span></p>
                        @endif
                        
                        @if(isset($log->batch_request['request_time']))
                            <p><strong>Request Time:</strong><br>
                            <small>{{ date('M d, Y H:i:s', $log->batch_request['request_time']) }}</small></p>
                        @endif
                        
                        @if(isset($log->batch_request['sign']))
                            <p><strong>Signature:</strong><br>
                            <code style="font-size: 0.8em;">{{ $log->batch_request['sign'] }}</code></p>
                        @endif
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
                            <a href="{{ route('admin.logs.webhook-logs') }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Webhook Logs
                            </a>
                            
                            <button class="btn btn-info" onclick="copyFullLog()">
                                <i class="fas fa-copy"></i> Copy Full Log
                            </button>
                            
                            <button class="btn btn-warning" onclick="testWebhook()">
                                <i class="fas fa-play"></i> Test Similar Request
                            </button>
                            
                            @if($log->status !== 'success')
                                <button class="btn btn-danger" onclick="showRetryModal()">
                                    <i class="fas fa-redo"></i> Retry Request
                                </button>
                            @endif
                            
                            <button class="btn btn-primary" onclick="loadRetryHistory()">
                                <i class="fas fa-history"></i> Retry History
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Analysis -->
                @if($log->status !== 'success' && isset($log->response_data['data']))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> Error Analysis
                        </h3>
                    </div>
                    <div class="card-body">
                        @foreach($log->response_data['data'] as $index => $response)
                            @if(isset($response['code']) && $response['code'] !== 0)
                                <div class="alert alert-danger">
                                    <strong>Request #{{ $index + 1 }}:</strong><br>
                                    <strong>Code:</strong> {{ $response['code'] }}<br>
                                    <strong>Message:</strong> {{ $response['message'] ?? 'No message' }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Retry Modal -->
<div class="modal fade" id="retryModal" tabindex="-1" role="dialog" aria-labelledby="retryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retryModalLabel">
                    <i class="fas fa-redo"></i> Retry Failed Webhook
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="retryForm">
                    <div class="form-group">
                        <label for="retryType">Retry Type</label>
                        <select class="form-control" id="retryType" name="retry_type" required>
                            <option value="">Select Retry Type</option>
                            <option value="full">Full Retry (All Transactions)</option>
                            <option value="partial">Partial Retry (Failed Only)</option>
                            <option value="individual">Individual Retry (Select Specific)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="individualTransactions" style="display: none;">
                        <label>Select Transactions to Retry</label>
                        <div id="transactionList" class="border p-3" style="max-height: 200px; overflow-y: auto;">
                            <!-- Individual transactions will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="retryReason">Reason for Retry</label>
                        <textarea class="form-control" id="retryReason" name="reason" rows="3" 
                                  placeholder="Explain why this webhook is being retried..." required></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will create a new webhook request with the same parameters. 
                        Make sure to verify the data before proceeding.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="executeRetry()">
                    <i class="fas fa-redo"></i> Execute Retry
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Retry History Modal -->
<div class="modal fade" id="retryHistoryModal" tabindex="-1" role="dialog" aria-labelledby="retryHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retryHistoryModalLabel">
                    <i class="fas fa-history"></i> Retry History
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="retryHistoryContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading retry history...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
const logId = {{ $log->id }};
const logData = @json($log);

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        toastr.success('Data copied to clipboard');
    }, function(err) {
        console.error('Could not copy text: ', err);
        toastr.error('Failed to copy to clipboard');
    });
}

function copyFullLog() {
    const fullLogData = {
        id: logData.id,
        type: logData.type,
        status: logData.status,
        created_at: logData.created_at,
        batch_request: logData.batch_request,
        response_data: logData.response_data
    };
    
    copyToClipboard(JSON.stringify(fullLogData, null, 2));
}

function testWebhook() {
    if (confirm('This will simulate the webhook request without making actual changes. Continue?')) {
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        button.disabled = true;
        
        fetch(`/admin/logs/webhook/${logId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Webhook test completed successfully');
                console.log('Test results:', data.data);
            } else {
                toastr.error('Webhook test failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Webhook test failed');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

function showRetryModal() {
    $('#retryModal').modal('show');
    loadTransactionList();
}

function loadTransactionList() {
    const transactionList = document.getElementById('transactionList');
    const batchRequests = logData.batch_request.batch_requests || [];
    
    if (batchRequests.length === 0) {
        transactionList.innerHTML = '<p class="text-muted">No transactions found</p>';
        return;
    }
    
    let html = '';
    batchRequests.forEach((transaction, index) => {
        const isFailed = isTransactionFailed(index);
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       id="transaction_${index}" 
                       value="${index}"
                       ${isFailed ? 'checked' : ''}>
                <label class="form-check-label" for="transaction_${index}">
                    <strong>Transaction ${index + 1}:</strong> 
                    ${transaction.member_account || 'N/A'} - 
                    ${transaction.amount || 0} 
                    ${logData.batch_request.currency || ''}
                    ${isFailed ? '<span class="badge badge-danger ml-2">Failed</span>' : ''}
                </label>
            </div>
        `;
    });
    
    transactionList.innerHTML = html;
}

function isTransactionFailed(index) {
    if (!logData.response_data || !logData.response_data.data) return false;
    const response = logData.response_data.data[index];
    return response && response.code !== 0;
}

function executeRetry() {
    const retryType = document.getElementById('retryType').value;
    const reason = document.getElementById('retryReason').value;
    
    if (!retryType || !reason.trim()) {
        toastr.error('Please select retry type and provide a reason');
        return;
    }
    
    const formData = {
        retry_type: retryType,
        reason: reason
    };
    
    if (retryType === 'individual') {
        const selectedTransactions = Array.from(document.querySelectorAll('#transactionList input:checked'))
            .map(input => parseInt(input.value));
        
        if (selectedTransactions.length === 0) {
            toastr.error('Please select at least one transaction to retry');
            return;
        }
        
        formData.transaction_ids = selectedTransactions;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Retrying...';
    button.disabled = true;
    
    fetch(`/admin/logs/webhook/${logId}/retry`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Webhook retry completed successfully');
            $('#retryModal').modal('hide');
            // Refresh the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            toastr.error('Webhook retry failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Webhook retry failed');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function loadRetryHistory() {
    $('#retryHistoryModal').modal('show');
    
    fetch(`/admin/logs/webhook/${logId}/retry-history`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('retryHistoryContent');
            
            if (data.success && data.data.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-striped">';
                html += '<thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Retry By</th><th>Created At</th><th>Actions</th></tr></thead><tbody>';
                
                data.data.forEach(retry => {
                    html += `
                        <tr>
                            <td>${retry.id}</td>
                            <td><span class="badge badge-info">${retry.type}</span></td>
                            <td>
                                <span class="badge ${retry.status === 'success' ? 'badge-success' : 'badge-danger'}">
                                    ${retry.status}
                                </span>
                            </td>
                            <td>${retry.batch_request?.retry_metadata?.retry_by || 'N/A'}</td>
                            <td>${new Date(retry.created_at).toLocaleString()}</td>
                            <td>
                                <a href="/admin/logs/webhook/${retry.id}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<div class="text-center text-muted"><i class="fas fa-inbox"></i><br>No retry history found</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('retryHistoryContent').innerHTML = 
                '<div class="alert alert-danger">Failed to load retry history</div>';
        });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide individual transactions based on retry type
    document.getElementById('retryType').addEventListener('change', function() {
        const individualDiv = document.getElementById('individualTransactions');
        if (this.value === 'individual') {
            individualDiv.style.display = 'block';
        } else {
            individualDiv.style.display = 'none';
        }
    });
});
</script>
@endsection
