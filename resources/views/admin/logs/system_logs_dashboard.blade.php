@extends('layouts.master')

@section('title', 'System Logs Dashboard')

@section('style')
<style>
    .system-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .system-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .stats-card.success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stats-card.warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .stats-card.danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }
    .log-type-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    .cleanup-preview {
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
                <h1 class="m-0">System Logs Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.logs.index') }}">System Logs</a></li>
                    <li class="breadcrumb-item active">System Logs Dashboard</li>
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
                <div class="small-box stats-card success">
                    <div class="inner">
                        <h3 id="total-transaction-logs">-</h3>
                        <p>Total Transaction Logs</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box stats-card">
                    <div class="inner">
                        <h3 id="today-transaction-logs">-</h3>
                        <p>Today's Logs</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box stats-card warning">
                    <div class="inner">
                        <h3 id="this-week-logs">-</h3>
                        <p>This Week</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box stats-card danger">
                    <div class="inner">
                        <h3 id="this-month-logs">-</h3>
                        <p>This Month</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Log Type Cards -->
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card system-card" onclick="window.location.href='{{ route('admin.logs.system-transaction-logs') }}'">
                    <div class="card-body text-center">
                        <div class="log-type-icon text-primary">
                            <i class="fas fa-database"></i>
                        </div>
                        <h5 class="card-title">Transaction Logs</h5>
                        <p class="card-text">View and manage system transaction logs (Admin only - not visible to clients).</p>
                        <div class="mt-3">
                            <span class="badge badge-primary">System</span>
                            <span class="badge badge-warning">Admin Only</span>
                            <span class="badge badge-info">Performance</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 mb-4">
                <div class="card system-card" onclick="showCleanupModal()">
                    <div class="card-body text-center">
                        <div class="log-type-icon text-danger">
                            <i class="fas fa-broom"></i>
                        </div>
                        <h5 class="card-title">Cleanup Management</h5>
                        <p class="card-text">Automatically clean up old transaction logs to improve database performance.</p>
                        <div class="mt-3">
                            <span class="badge badge-danger">Cleanup</span>
                            <span class="badge badge-success">Auto</span>
                            <span class="badge badge-info">Performance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cleanup Management Section -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-broom"></i> Cleanup Management
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Current Settings</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Auto Cleanup:</strong> <span id="auto-cleanup-status">Enabled</span></li>
                                    <li><strong>Retention Period:</strong> <span id="retention-days">3 days</span></li>
                                    <li><strong>Batch Size:</strong> <span id="batch-size">1000 records</span></li>
                                    <li><strong>Schedule:</strong> <span id="cleanup-schedule">Every 3 days</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Quick Actions</h5>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning" onclick="previewCleanup()">
                                        <i class="fas fa-eye"></i> Preview Cleanup
                                    </button>
                                    <button class="btn btn-danger" onclick="performCleanup()">
                                        <i class="fas fa-broom"></i> Run Cleanup Now
                                    </button>
                                    <button class="btn btn-info" onclick="optimizeTable()">
                                        <i class="fas fa-tools"></i> Optimize Table
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Log Statistics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="log-statistics">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Loading statistics...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" role="dialog" aria-labelledby="cleanupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanupModalLabel">
                    <i class="fas fa-broom"></i> Transaction Logs Cleanup
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cleanupForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cleanupDays">Days to Keep</label>
                                <select class="form-control" id="cleanupDays" name="days">
                                    <option value="1">1 day</option>
                                    <option value="2">2 days</option>
                                    <option value="3" selected>3 days</option>
                                    <option value="7">7 days</option>
                                    <option value="14">14 days</option>
                                    <option value="30">30 days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="batchSize">Batch Size</label>
                                <select class="form-control" id="batchSize" name="batch_size">
                                    <option value="500">500 records</option>
                                    <option value="1000" selected>1000 records</option>
                                    <option value="2000">2000 records</option>
                                    <option value="5000">5000 records</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will permanently delete transaction logs older than the specified days. 
                        This action cannot be undone.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="previewCleanupFromModal()">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmCleanup()">
                    <i class="fas fa-broom"></i> Cleanup Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye"></i> Cleanup Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading preview...
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
$(document).ready(function() {
    loadSystemStats();
    loadLogStatistics();
});

function loadSystemStats() {
    // Load basic statistics
    $('#total-transaction-logs').text('{{ $stats["total_transaction_logs"] }}');
    $('#today-transaction-logs').text('{{ $stats["today_transaction_logs"] }}');
    $('#this-week-logs').text('{{ $stats["this_week_transaction_logs"] }}');
    $('#this-month-logs').text('{{ $stats["this_month_transaction_logs"] }}');
}

function loadLogStatistics() {
    fetch('/admin/logs/system-cleanup-stats?days=3')
        .then(response => response.json())
        .then(data => {
            let html = '<div class="row">';
            
            // By Type
            html += '<div class="col-12 mb-3">';
            html += '<h6>By Type:</h6>';
            data.by_type.forEach(function(item) {
                html += `<div class="d-flex justify-content-between">
                    <span>${item.type}</span>
                    <span class="badge badge-primary">${item.count}</span>
                </div>`;
            });
            html += '</div>';
            
            // By Status
            html += '<div class="col-12">';
            html += '<h6>By Status:</h6>';
            data.by_status.forEach(function(item) {
                html += `<div class="d-flex justify-content-between">
                    <span>${item.status}</span>
                    <span class="badge badge-info">${item.count}</span>
                </div>`;
            });
            html += '</div>';
            
            html += '</div>';
            $('#log-statistics').html(html);
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
            $('#log-statistics').html('<div class="alert alert-danger">Failed to load statistics</div>');
        });
}

function showCleanupModal() {
    $('#cleanupModal').modal('show');
}

function previewCleanup() {
    const days = 3; // Default
    showCleanupPreview(days);
}

function previewCleanupFromModal() {
    const days = document.getElementById('cleanupDays').value;
    showCleanupPreview(days);
}

function showCleanupPreview(days) {
    $('#previewModal').modal('show');
    
    fetch(`/admin/logs/system-cleanup-preview?days=${days}&limit=20`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="alert alert-info">
                    <strong>Cleanup Preview:</strong> ${data.total_to_delete} records will be deleted
                    <br><strong>Cutoff Date:</strong> ${new Date(data.cutoff_date).toLocaleString()}
                    <br><strong>Days to Keep:</strong> ${data.days_kept}
                </div>
            `;
            
            if (data.sample_records && data.sample_records.length > 0) {
                html += '<h6>Sample Records to be Deleted:</h6>';
                html += '<div class="table-responsive cleanup-preview">';
                html += '<table class="table table-sm table-striped">';
                html += '<thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Created At</th></tr></thead><tbody>';
                
                data.sample_records.forEach(function(record) {
                    html += `<tr>
                        <td>${record.id}</td>
                        <td><span class="badge badge-primary">${record.type}</span></td>
                        <td><span class="badge badge-info">${record.status}</span></td>
                        <td>${new Date(record.created_at).toLocaleString()}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
            } else {
                html += '<div class="alert alert-success">No records to delete.</div>';
            }
            
            $('#previewContent').html(html);
        })
        .catch(error => {
            console.error('Error loading preview:', error);
            $('#previewContent').html('<div class="alert alert-danger">Failed to load preview</div>');
        });
}

function performCleanup() {
    if (confirm('Are you sure you want to perform cleanup? This action cannot be undone.')) {
        const days = 3; // Default
        const batchSize = 1000; // Default
        
        executeCleanup(days, batchSize);
    }
}

function confirmCleanup() {
    const days = document.getElementById('cleanupDays').value;
    const batchSize = document.getElementById('batchSize').value;
    
    if (confirm(`Are you sure you want to delete transaction logs older than ${days} days? This action cannot be undone.`)) {
        $('#cleanupModal').modal('hide');
        executeCleanup(days, batchSize);
    }
}

function executeCleanup(days, batchSize) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cleaning...';
    button.disabled = true;
    
    fetch('/admin/logs/system-cleanup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            days: days,
            batch_size: batchSize
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            // Reload statistics
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Cleanup failed');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function optimizeTable() {
    if (confirm('Are you sure you want to optimize the transaction logs table?')) {
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Optimizing...';
        button.disabled = true;
        
        fetch('/admin/logs/system-optimize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                if (data.data.space_saved) {
                    toastr.info(`Space saved: ${data.data.space_saved}`);
                }
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Table optimization failed');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}
</script>
@endsection
