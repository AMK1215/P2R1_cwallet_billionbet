@extends('layouts.master')

@section('title', 'User Guide - Admin Panel')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-book"></i> 
                        အသုံးပြုသူလမ်းညွှန် (User Guide)
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">ပင်မဒက်ရှ်ဘုတ်</a></li>
                        <li class="breadcrumb-item active">အသုံးပြုသူလမ်းညွှန်</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Search Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search"></i> 
                                ရှာဖွေရန် (Search Guides)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="လမ်းညွှန်များကို ရှာဖွေရန်... (Search guides...)" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i> ရှာဖွေရန်
                                    </button>
                                </div>
                            </div>
                            <div id="searchResults" class="mt-3" style="display: none;">
                                <!-- Search results will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Guides Grid -->
            <div class="row">
                @foreach($guides as $guide)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card guide-card h-100" onclick="window.location.href='{{ route('admin.logs.user-guide.show', $guide['id']) }}'">
                        <div class="card-header bg-{{ $guide['color'] }}">
                            <h3 class="card-title text-white">
                                <i class="{{ $guide['icon'] }}"></i>
                                {{ $guide['title'] }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-{{ $guide['color'] }}">
                                {{ $guide['title_en'] }}
                            </h5>
                            <p class="card-text">
                                {{ $guide['description'] }}
                            </p>
                            <p class="card-text text-muted">
                                <small>{{ $guide['description_en'] }}</small>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('admin.logs.user-guide.show', $guide['id']) }}" class="btn btn-{{ $guide['color'] }} btn-block">
                                        <i class="fas fa-eye"></i> ကြည့်ရှုရန်
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('admin.logs.user-guide.download', $guide['id']) }}" class="btn btn-outline-{{ $guide['color'] }} btn-block">
                                        <i class="fas fa-download"></i> ဒေါင်းလုဒ်
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Quick Access Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt"></i> 
                                အမြန်ဝင်ရောက်မှု (Quick Access)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'quick-reference') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-bolt"></i><br>
                                        အမြန်လမ်းညွှန်
                                    </a>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'system-logs-cleanup') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-broom"></i><br>
                                        ရှင်းလင်းမှုစနစ်
                                    </a>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'admin-user-guide') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-book"></i><br>
                                        ပြည့်စုံသောလမ်းညွှန်
                                    </a>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'webhook-custom-wallet') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-link"></i><br>
                                        ဝဘ်ဟွတ်နှင့်ဝေါလ်လက်
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle"></i> 
                                ကူညီရေးနှင့် ပံ့ပိုးမှု (Help & Support)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>အမြန်အကူအညီ (Quick Help)</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> လမ်းညွှန်များကို ရှာဖွေရန် အပေါ်ရှိ ရှာဖွေရေးကို အသုံးပြုပါ</li>
                                        <li><i class="fas fa-check text-success"></i> လိုအပ်သော လမ်းညွှန်ကို နှိပ်ပြီး ကြည့်ရှုပါ</li>
                                        <li><i class="fas fa-check text-success"></i> လမ်းညွှန်များကို ဒေါင်းလုဒ်လုပ်ပြီး သိမ်းဆည်းပါ</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5>ဆက်သွယ်ရေး (Contact)</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-envelope"></i> admin@your-domain.com</li>
                                        <li><i class="fas fa-phone"></i> +95-XXX-XXX-XXXX</li>
                                        <li><i class="fas fa-exclamation-triangle"></i> အရေးပေါ်ဖုန်း: +95-XXX-XXX-XXXX</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.guide-card {
    cursor: pointer;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.guide-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.guide-card .card-header {
    border-bottom: none;
}

.guide-card .card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.search-result-item {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
}

.search-result-item:hover {
    background-color: #e9ecef;
}
</style>

<script>
$(document).ready(function() {
    // Search functionality
    $('#searchBtn').click(function() {
        performSearch();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            performSearch();
        }
    });

    function performSearch() {
        var query = $('#searchInput').val().trim();
        
        if (query.length < 2) {
            alert('အနည်းဆုံး ၂ လုံးရှိရပါမယ် (Minimum 2 characters required)');
            return;
        }

        $.ajax({
            url: '{{ route("admin.logs.user-guide.search") }}',
            method: 'GET',
            data: { q: query },
            beforeSend: function() {
                $('#searchResults').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> ရှာဖွေနေပါသည်...</div>').show();
            },
            success: function(response) {
                displaySearchResults(response.results, query);
            },
            error: function() {
                $('#searchResults').html('<div class="alert alert-danger">ရှာဖွေမှုတွင် အမှားတစ်ခုဖြစ်ပွားပါသည် (Search error occurred)</div>').show();
            }
        });
    }

    function displaySearchResults(results, query) {
        if (results.length === 0) {
            $('#searchResults').html('<div class="alert alert-info">ရှာဖွေမှုနှင့် ကိုက်ညီသော ရလဒ်များ မတွေ့ရှိပါ (No results found)</div>').show();
            return;
        }

        var html = '<h5>ရှာဖွေရလဒ်များ (Search Results):</h5>';
        
        results.forEach(function(result) {
            html += '<div class="search-result-item">';
            html += '<h6><i class="fas fa-file-alt"></i> ' + result.title + '</h6>';
            html += '<p class="text-muted">' + result.snippet + '</p>';
            html += '</div>';
        });

        $('#searchResults').html(html).show();
    }

    // Clear search results when input is cleared
    $('#searchInput').on('input', function() {
        if ($(this).val().trim() === '') {
            $('#searchResults').hide();
        }
    });
});
</script>
@endsection
