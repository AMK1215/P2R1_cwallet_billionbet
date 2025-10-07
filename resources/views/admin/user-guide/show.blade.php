@extends('layouts.master')

@section('title', $guide['title'] . ' - User Guide')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-book"></i> 
                        {{ $guide['title'] }}
                    </h1>
                    <p class="text-muted">{{ $guide['title_en'] }}</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">ပင်မဒက်ရှ်ဘုတ်</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.logs.user-guide') }}">အသုံးပြုသူလမ်းညွှန်</a></li>
                        <li class="breadcrumb-item active">{{ $guide['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.logs.user-guide') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ပြန်သွားရန်
                        </a>
                        <a href="{{ route('admin.logs.user-guide.download', $guideId) }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> ဒေါင်းလုဒ်လုပ်ရန်
                        </a>
                        <button class="btn btn-info" onclick="printGuide()">
                            <i class="fas fa-print"></i> ပုံနှိပ်ရန်
                        </button>
                        <button class="btn btn-success" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i> ဖန်သားပြင်ပြည့်ကြည့်ရှုရန်
                        </button>
                    </div>
                </div>
            </div>

            <!-- Guide Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt"></i> 
                                လမ်းညွှန်အကြောင်းအရာ (Guide Content)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="guideContent">
                            <div class="guide-content">
                                {!! $htmlContent !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation for Other Guides -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> 
                                အခြားလမ်းညွှန်များ (Other Guides)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'admin-user-guide') }}" 
                                       class="btn btn-primary btn-block {{ $guideId == 'admin-user-guide' ? 'disabled' : '' }}">
                                        <i class="fas fa-book"></i><br>
                                        အက်မင်စနစ် လမ်းညွှန်
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'system-logs-cleanup') }}" 
                                       class="btn btn-success btn-block {{ $guideId == 'system-logs-cleanup' ? 'disabled' : '' }}">
                                        <i class="fas fa-broom"></i><br>
                                        ရှင်းလင်းမှုစနစ်
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'quick-reference') }}" 
                                       class="btn btn-warning btn-block {{ $guideId == 'quick-reference' ? 'disabled' : '' }}">
                                        <i class="fas fa-bolt"></i><br>
                                        အမြန်လမ်းညွှန်
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'logging-system-setup') }}" 
                                       class="btn btn-info btn-block {{ $guideId == 'logging-system-setup' ? 'disabled' : '' }}">
                                        <i class="fas fa-cogs"></i><br>
                                        လော့ဂျင်းစနစ်
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'webhook-custom-wallet') }}" 
                                       class="btn btn-secondary btn-block {{ $guideId == 'webhook-custom-wallet' ? 'disabled' : '' }}">
                                        <i class="fas fa-link"></i><br>
                                        ဝဘ်ဟွတ်နှင့်ဝေါလ်လက်
                                    </a>
                                </div>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <a href="{{ route('admin.logs.user-guide.show', 'admin-logging-system') }}" 
                                       class="btn btn-dark btn-block {{ $guideId == 'admin-logging-system' ? 'disabled' : '' }}">
                                        <i class="fas fa-clipboard-list"></i><br>
                                        အက်မင်လော့ဂျင်း
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
                                        <li><i class="fas fa-check text-success"></i> လမ်းညွှန်ကို ပုံနှိပ်ရန် "ပုံနှိပ်ရန်" ခလုတ်ကို နှိပ်ပါ</li>
                                        <li><i class="fas fa-check text-success"></i> လမ်းညွှန်ကို ဒေါင်းလုဒ်လုပ်ရန် "ဒေါင်းလုဒ်လုပ်ရန်" ခလုတ်ကို နှိပ်ပါ</li>
                                        <li><i class="fas fa-check text-success"></i> ဖန်သားပြင်ပြည့်ကြည့်ရှုရန် "ဖန်သားပြင်ပြည့်ကြည့်ရှုရန်" ခလုတ်ကို နှိပ်ပါ</li>
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
.guide-content {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
}

.guide-content h1 {
    color: #007bff;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.guide-content h2 {
    color: #28a745;
    border-bottom: 1px solid #28a745;
    padding-bottom: 5px;
    margin-top: 30px;
    margin-bottom: 15px;
}

.guide-content h3 {
    color: #ffc107;
    margin-top: 25px;
    margin-bottom: 10px;
}

.guide-content h4 {
    color: #6c757d;
    margin-top: 20px;
    margin-bottom: 10px;
}

.guide-content p {
    margin-bottom: 15px;
    text-align: justify;
}

.guide-content ul, .guide-content ol {
    margin-bottom: 15px;
    padding-left: 30px;
}

.guide-content li {
    margin-bottom: 5px;
}

.guide-content code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    color: #e83e8c;
}

.guide-content pre {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
    overflow-x: auto;
    margin-bottom: 20px;
}

.guide-content pre code {
    background-color: transparent;
    padding: 0;
    color: #333;
}

.guide-content strong {
    color: #007bff;
    font-weight: 600;
}

.guide-content em {
    color: #6c757d;
    font-style: italic;
}

.guide-content a {
    color: #007bff;
    text-decoration: none;
}

.guide-content a:hover {
    text-decoration: underline;
}

.fullscreen-mode {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: white;
    z-index: 9999;
    overflow-y: auto;
    padding: 20px;
}

.fullscreen-mode .guide-content {
    max-width: 1200px;
    margin: 0 auto;
}

.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media print {
    .content-header,
    .btn-group,
    .card-header,
    .card:not(.guide-content) {
        display: none !important;
    }
    
    .guide-content {
        font-size: 12pt;
        line-height: 1.4;
    }
    
    .guide-content h1 {
        page-break-before: always;
    }
    
    .guide-content h1:first-child {
        page-break-before: avoid;
    }
}
</style>

<script>
function printGuide() {
    window.print();
}

function toggleFullscreen() {
    var content = document.getElementById('guideContent');
    var btn = event.target;
    
    if (content.classList.contains('fullscreen-mode')) {
        content.classList.remove('fullscreen-mode');
        btn.innerHTML = '<i class="fas fa-expand"></i> ဖန်သားပြင်ပြည့်ကြည့်ရှုရန်';
    } else {
        content.classList.add('fullscreen-mode');
        btn.innerHTML = '<i class="fas fa-compress"></i> ပုံမှန်ကြည့်ရှုရန်';
    }
}

// Add smooth scrolling for anchor links
$(document).ready(function() {
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Add table of contents functionality
    var toc = generateTableOfContents();
    if (toc) {
        $('.guide-content').prepend('<div class="toc-section"><h2>စာရင်းပါဝင်မှုများ (Table of Contents)</h2>' + toc + '</div>');
    }
});

function generateTableOfContents() {
    var headings = $('.guide-content h1, .guide-content h2, .guide-content h3');
    if (headings.length === 0) return '';
    
    var toc = '<ul class="toc-list">';
    headings.each(function(index) {
        var heading = $(this);
        var id = 'heading-' + index;
        var text = heading.text();
        
        heading.attr('id', id);
        
        var level = heading.prop('tagName').toLowerCase();
        var indent = level === 'h1' ? '' : level === 'h2' ? '&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;';
        
        toc += '<li>' + indent + '<a href="#' + id + '">' + text + '</a></li>';
    });
    toc += '</ul>';
    
    return toc;
}
</script>
@endsection
