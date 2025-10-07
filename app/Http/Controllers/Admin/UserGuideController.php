<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UserGuideController extends Controller
{
    /**
     * Display the main user guide index page
     */
    public function index()
    {
        $guides = [
            [
                'id' => 'admin-user-guide',
                'title' => 'အက်မင်စနစ် အသုံးပြုသူလမ်းညွှန်',
                'title_en' => 'Admin System User Guide',
                'description' => 'အက်မင်စနစ်၏ ပြည့်စုံသော အသုံးပြုသူလမ်းညွှန်',
                'description_en' => 'Complete admin system user guide',
                'icon' => 'fas fa-book',
                'color' => 'primary',
                'file' => 'ADMIN_USER_GUIDE_MYANMAR.md'
            ],
            [
                'id' => 'system-logs-cleanup',
                'title' => 'စနစ်လော့များနှင့် ရှင်းလင်းမှုစနစ်',
                'title_en' => 'System Logs and Cleanup System',
                'description' => 'စနစ်လော့များနှင့် ရှင်းလင်းမှုစနစ် အသုံးပြုသူလမ်းညွှန်',
                'description_en' => 'System logs and cleanup system user guide',
                'icon' => 'fas fa-broom',
                'color' => 'success',
                'file' => 'SYSTEM_LOGS_CLEANUP_GUIDE_MYANMAR.md'
            ],
            [
                'id' => 'quick-reference',
                'title' => 'အက်မင်စနစ် အမြန်လမ်းညွှန်',
                'title_en' => 'Admin System Quick Reference',
                'description' => 'အက်မင်စနစ်၏ အမြန်လမ်းညွှန်',
                'description_en' => 'Quick reference guide for admin system',
                'icon' => 'fas fa-bolt',
                'color' => 'warning',
                'file' => 'QUICK_REFERENCE_MYANMAR.md'
            ],
            [
                'id' => 'logging-system-setup',
                'title' => 'လော့ဂျင်းစနစ် စတင်ခြင်း',
                'title_en' => 'Logging System Setup Guide',
                'description' => 'လော့ဂျင်းစနစ် စတင်ခြင်း လမ်းညွှန်',
                'description_en' => 'Logging system setup guide',
                'icon' => 'fas fa-cogs',
                'color' => 'info',
                'file' => 'LOGGING_SYSTEM_SETUP_GUIDE.md'
            ],
            [
                'id' => 'webhook-custom-wallet',
                'title' => 'ဝဘ်ဟွတ်နှင့် ကွန်တိုက်ပိုက်တ်ဝေါလ်လက်',
                'title_en' => 'Webhook and Custom Wallet Documentation',
                'description' => 'ဝဘ်ဟွတ်နှင့် ကွန်တိုက်ပိုက်တ်ဝေါလ်လက် စာရွက်စာတမ်း',
                'description_en' => 'Webhook and custom wallet documentation',
                'icon' => 'fas fa-link',
                'color' => 'secondary',
                'file' => 'WEBHOOK_CUSTOM_WALLET_DOCUMENTATION.md'
            ],
            [
                'id' => 'admin-logging-system',
                'title' => 'အက်မင်လော့ဂျင်းစနစ် စာရွက်စာတမ်း',
                'title_en' => 'Admin Logging System Documentation',
                'description' => 'အက်မင်လော့ဂျင်းစနစ် စာရွက်စာတမ်း',
                'description_en' => 'Admin logging system documentation',
                'icon' => 'fas fa-clipboard-list',
                'color' => 'dark',
                'file' => 'ADMIN_LOGGING_SYSTEM_DOCUMENTATION.md'
            ]
        ];

        return view('admin.user-guide.index', compact('guides'));
    }

    /**
     * Display a specific user guide
     */
    public function show(Request $request, $guideId)
    {
        $guides = [
            'admin-user-guide' => [
                'title' => 'အက်မင်စနစ် အသုံးပြုသူလမ်းညွှန်',
                'title_en' => 'Admin System User Guide',
                'file' => 'ADMIN_USER_GUIDE_MYANMAR.md'
            ],
            'system-logs-cleanup' => [
                'title' => 'စနစ်လော့များနှင့် ရှင်းလင်းမှုစနစ်',
                'title_en' => 'System Logs and Cleanup System',
                'file' => 'SYSTEM_LOGS_CLEANUP_GUIDE_MYANMAR.md'
            ],
            'quick-reference' => [
                'title' => 'အက်မင်စနစ် အမြန်လမ်းညွှန်',
                'title_en' => 'Admin System Quick Reference',
                'file' => 'QUICK_REFERENCE_MYANMAR.md'
            ],
            'logging-system-setup' => [
                'title' => 'လော့ဂျင်းစနစ် စတင်ခြင်း',
                'title_en' => 'Logging System Setup Guide',
                'file' => 'LOGGING_SYSTEM_SETUP_GUIDE.md'
            ],
            'webhook-custom-wallet' => [
                'title' => 'ဝဘ်ဟွတ်နှင့် ကွန်တိုက်ပိုက်တ်ဝေါလ်လက်',
                'title_en' => 'Webhook and Custom Wallet Documentation',
                'file' => 'WEBHOOK_CUSTOM_WALLET_DOCUMENTATION.md'
            ],
            'admin-logging-system' => [
                'title' => 'အက်မင်လော့ဂျင်းစနစ် စာရွက်စာတမ်း',
                'title_en' => 'Admin Logging System Documentation',
                'file' => 'ADMIN_LOGGING_SYSTEM_DOCUMENTATION.md'
            ]
        ];

        if (!isset($guides[$guideId])) {
            abort(404, 'User guide not found');
        }

        $guide = $guides[$guideId];
        $filePath = base_path('docs/' . $guide['file']);

        if (!File::exists($filePath)) {
            abort(404, 'Guide file not found');
        }

        $content = File::get($filePath);
        
        // Convert markdown to HTML (basic conversion)
        $htmlContent = $this->markdownToHtml($content);

        return view('admin.user-guide.show', compact('guide', 'htmlContent', 'guideId'));
    }

    /**
     * Convert markdown to HTML (basic conversion)
     */
    private function markdownToHtml($markdown)
    {
        // Basic markdown to HTML conversion
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        
        // Italic
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Code blocks
        $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
        
        // Inline code
        $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);
        
        // Lists
        $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Line breaks
        $html = nl2br($html);
        
        // Clean up multiple line breaks
        $html = preg_replace('/(<br\s*\/?>){3,}/', '<br><br>', $html);
        
        return $html;
    }

    /**
     * Download a user guide as PDF
     */
    public function download(Request $request, $guideId)
    {
        $guides = [
            'admin-user-guide' => 'ADMIN_USER_GUIDE_MYANMAR.md',
            'system-logs-cleanup' => 'SYSTEM_LOGS_CLEANUP_GUIDE_MYANMAR.md',
            'quick-reference' => 'QUICK_REFERENCE_MYANMAR.md',
            'logging-system-setup' => 'LOGGING_SYSTEM_SETUP_GUIDE.md',
            'webhook-custom-wallet' => 'WEBHOOK_CUSTOM_WALLET_DOCUMENTATION.md',
            'admin-logging-system' => 'ADMIN_LOGGING_SYSTEM_DOCUMENTATION.md'
        ];

        if (!isset($guides[$guideId])) {
            abort(404, 'User guide not found');
        }

        $filePath = base_path('docs/' . $guides[$guideId]);

        if (!File::exists($filePath)) {
            abort(404, 'Guide file not found');
        }

        $content = File::get($filePath);
        $fileName = $guides[$guideId];

        return response($content)
            ->header('Content-Type', 'text/markdown')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Search user guides
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $results = [];

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $guideFiles = [
            'ADMIN_USER_GUIDE_MYANMAR.md',
            'SYSTEM_LOGS_CLEANUP_GUIDE_MYANMAR.md',
            'QUICK_REFERENCE_MYANMAR.md',
            'LOGGING_SYSTEM_SETUP_GUIDE.md',
            'WEBHOOK_CUSTOM_WALLET_DOCUMENTATION.md',
            'ADMIN_LOGGING_SYSTEM_DOCUMENTATION.md'
        ];

        foreach ($guideFiles as $file) {
            $filePath = base_path('docs/' . $file);
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                if (stripos($content, $query) !== false) {
                    $results[] = [
                        'file' => $file,
                        'title' => $this->getGuideTitle($file),
                        'snippet' => $this->getSnippet($content, $query)
                    ];
                }
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Get guide title from filename
     */
    private function getGuideTitle($filename)
    {
        $titles = [
            'ADMIN_USER_GUIDE_MYANMAR.md' => 'အက်မင်စနစ် အသုံးပြုသူလမ်းညွှန်',
            'SYSTEM_LOGS_CLEANUP_GUIDE_MYANMAR.md' => 'စနစ်လော့များနှင့် ရှင်းလင်းမှုစနစ်',
            'QUICK_REFERENCE_MYANMAR.md' => 'အက်မင်စနစ် အမြန်လမ်းညွှန်',
            'LOGGING_SYSTEM_SETUP_GUIDE.md' => 'လော့ဂျင်းစနစ် စတင်ခြင်း',
            'WEBHOOK_CUSTOM_WALLET_DOCUMENTATION.md' => 'ဝဘ်ဟွတ်နှင့် ကွန်တိုက်ပိုက်တ်ဝေါလ်လက်',
            'ADMIN_LOGGING_SYSTEM_DOCUMENTATION.md' => 'အက်မင်လော့ဂျင်းစနစ် စာရွက်စာတမ်း'
        ];

        return $titles[$filename] ?? $filename;
    }

    /**
     * Get snippet around search term
     */
    private function getSnippet($content, $query, $length = 200)
    {
        $pos = stripos($content, $query);
        if ($pos === false) {
            return substr($content, 0, $length) . '...';
        }

        $start = max(0, $pos - $length / 2);
        $snippet = substr($content, $start, $length);
        
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $length < strlen($content)) {
            $snippet = $snippet . '...';
        }

        return $snippet;
    }
}
