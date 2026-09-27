<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page.
     */
    public function index(Request $request, ?string $topic = 'overview'): View
    {
        $validTopics = [
            'overview' => [
                'title' => 'Overview & Quick Start',
                'keywords' => 'quick start admin dashboard introduction handbook navigation core features overview',
            ],
            'pages' => [
                'title' => 'Pages & Visual Editor',
                'keywords' => 'visual editor page content landing pages hero slides copy sections toggle meta preview in-context',
            ],
            'formatting' => [
                'title' => 'Accent & Outlined Text ({})',
                'keywords' => 'curly braces accent color outlined text typography stroke font syntax brackets styling highlighted formatting',
            ],
            'counters' => [
                'title' => 'Character & Word Counters',
                'keywords' => 'character count word count live meter recommendation badge limits length seo title subtitle counters',
            ],
            'projects' => [
                'title' => 'Project Showcase & Case Studies',
                'keywords' => 'case studies showcase portfolio reorder featured star fixture link architecture gallery projects',
            ],
            'products' => [
                'title' => 'Product Catalog & Airtable',
                'keywords' => 'airtable sync product catalog specs variants live stream background job sku lighting fixtures products',
            ],
            'videos' => [
                'title' => 'Video Guidelines & Encoding',
                'keywords' => 'video encoding mp4 h264 ffmpeg background video poster image handbrake bitrate autoplay audio videos media',
            ],
            'images' => [
                'title' => 'Image Dimensions & WebP',
                'keywords' => 'webp image dimensions resolutions 1920x1080 1200x800 squoosh compression tinypng logos banner sizes images',
            ],
            'seo' => [
                'title' => 'SEO & Social Share (OG)',
                'keywords' => 'search engine optimization meta title meta description open graph og image 1200x630 canonical schema.org rich snippets alt text seo',
            ],
            'sitemap' => [
                'title' => 'XML Sitemap (/sitemap.xml)',
                'keywords' => 'xml sitemap sitemap.xml priority changefreq cache invalidation artisan sitemap:generate googlebot search console',
            ],
            'geo' => [
                'title' => 'Generative Engine Optimization (/llms.txt)',
                'keywords' => 'generative engine optimization llms.txt llms-full.txt ai search chatgpt perplexity claude gemini copilot robots.txt geo:generate knowledge graph citations geo',
            ],
            'enquiries' => [
                'title' => 'Enquiries & Quote Workflow',
                'keywords' => 'leads quotes contact submissions product inquiries pricing request pending resolved status lifecycle enquiries',
            ],
            'datasheets' => [
                'title' => 'Datasheet Exports',
                'keywords' => 'pdf spec sheets downloads export logs specifications technical sheets architects datasheets',
            ],
            'emails' => [
                'title' => 'Email Templates & Alerts',
                'keywords' => 'notification templates email alerts placeholders dynamic variables test email receipts confirmation emails',
            ],
            'staff' => [
                'title' => 'Staff & Role Permissions',
                'keywords' => 'team members permissions admin role rbac can.manage access control staff accounts security users staff',
            ],
            'deployment' => [
                'title' => 'Server Setup & Deployment',
                'keywords' => 'server setup storage:link asset versioning update_version.py turnstile captcha cache:clear production deploy maintenance deployment',
            ],
        ];

        $aliases = [
            'media' => 'videos',
            'content' => 'pages',
            'maintenance' => 'deployment',
        ];

        if (isset($aliases[$topic])) {
            $topic = $aliases[$topic];
        }

        if (! array_key_exists($topic, $validTopics)) {
            $topic = 'overview';
        }

        return view('dashboard.docs.index', [
            'activeTopic' => $topic,
            'topics' => $validTopics,
        ]);
    }
}
