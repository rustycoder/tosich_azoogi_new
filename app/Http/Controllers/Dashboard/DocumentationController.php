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
        $sections = [
            'Getting Started' => [
                'overview' => [
                    'label' => 'Overview',
                    'title' => 'Overview & Quick Start',
                    'keywords' => 'quick start admin dashboard introduction handbook navigation core features overview',
                ],
                'deployment' => [
                    'label' => 'Server & Setup',
                    'title' => 'Server Setup & Deployment',
                    'keywords' => 'server setup environment env database db mysql sqlite postgres airtable config email smtp mailer storage:link asset versioning update_version.py turnstile captcha cache:clear production deploy maintenance deployment',
                ],
            ],
            'Content & Media' => [
                'pages' => [
                    'label' => 'Pages & Editor',
                    'title' => 'Pages & Visual Editor',
                    'keywords' => 'visual editor page content landing pages hero slides copy sections toggle meta preview in-context',
                ],
                'formatting' => [
                    'label' => 'Text Formatting',
                    'title' => 'Accent & Outlined Text ({})',
                    'keywords' => 'curly braces accent color outlined text typography stroke font syntax brackets styling highlighted formatting',
                ],
                'counters' => [
                    'label' => 'Text Counters',
                    'title' => 'Character & Word Counters',
                    'keywords' => 'character count word count live meter recommendation badge limits length seo title subtitle counters',
                ],
                'projects' => [
                    'label' => 'Featured Projects',
                    'title' => 'Project Showcase & Case Studies',
                    'keywords' => 'case studies showcase portfolio reorder featured star fixture link architecture gallery projects',
                ],
                'videos' => [
                    'label' => 'Video Guidelines',
                    'title' => 'Video Guidelines & Encoding',
                    'keywords' => 'video encoding mp4 h264 ffmpeg background video poster image handbrake bitrate autoplay audio videos media',
                ],
                'images' => [
                    'label' => 'Image Standards',
                    'title' => 'Image Dimensions & WebP',
                    'keywords' => 'webp image dimensions resolutions 1920x1080 1200x800 squoosh compression tinypng logos banner sizes images',
                ],
                'alt-text' => [
                    'label' => 'Image Alt Text',
                    'title' => 'Image Alt Text & Accessibility',
                    'keywords' => 'alt text alternative text accessibility wcag 2.1 screen readers seo google images rankings cover_alt gallery_alts meta descriptions images media',
                ],
            ],
            'SEO & Discovery' => [
                'seo' => [
                    'label' => 'SEO & Social (OG)',
                    'title' => 'SEO & Social Share (OG)',
                    'keywords' => 'search engine optimization meta title meta description open graph og image 1200x630 canonical schema.org rich snippets alt text seo',
                ],
                'sitemap' => [
                    'label' => 'XML Sitemap',
                    'title' => 'XML Sitemap (/sitemap.xml)',
                    'keywords' => 'xml sitemap sitemap.xml priority changefreq cache invalidation artisan sitemap:generate googlebot search console',
                ],
                'geo' => [
                    'label' => 'AI Search (GEO)',
                    'title' => 'Generative Engine Optimization (/llms.txt)',
                    'keywords' => 'generative engine optimization llms.txt llms-full.txt ai search chatgpt perplexity claude gemini copilot robots.txt geo:generate knowledge graph citations geo',
                ],
            ],
            'AI & Automation' => [
                'mcp' => [
                    'label' => 'MCP Protocol & AI',
                    'title' => 'Model Context Protocol (MCP) & AI Integration',
                    'keywords' => 'mcp model context protocol ai claude cursor stdio json-rpc tools frontend exploration backend mutations page updates quotes chatbot automation',
                ],
            ],
            'Operations & Admin' => [
                'products' => [
                    'label' => 'Products & Airtable',
                    'title' => 'Product Catalog & Airtable',
                    'keywords' => 'airtable sync product catalog specs variants live stream background job sku lighting fixtures products order ordering hierarchy category block number formula maintenance sequence 201001 901041 20101 90141 catalog',
                ],
                'enquiries' => [
                    'label' => 'Enquiries & Quotes',
                    'title' => 'Enquiries & Quote Workflow',
                    'keywords' => 'leads quotes contact submissions product inquiries pricing request pending resolved status lifecycle enquiries',
                ],
                'datasheets' => [
                    'label' => 'PDF Datasheets',
                    'title' => 'Datasheet Exports',
                    'keywords' => 'pdf spec sheets downloads export logs specifications technical sheets architects datasheets',
                ],
                'emails' => [
                    'label' => 'Email Templates',
                    'title' => 'Email Templates & Alerts',
                    'keywords' => 'notification templates email alerts placeholders dynamic variables test email receipts confirmation emails',
                ],
                'staff' => [
                    'label' => 'Staff & Permissions',
                    'title' => 'Staff & Role Permissions',
                    'keywords' => 'team members permissions admin role rbac can.manage access control staff accounts security users staff',
                ],
            ],
        ];

        // Flatten valid topics list
        $validTopics = [];
        foreach ($sections as $groupTopics) {
            foreach ($groupTopics as $key => $data) {
                $validTopics[$key] = $data;
            }
        }

        $aliases = [
            'media' => 'videos',
            'content' => 'pages',
            'maintenance' => 'deployment',
            'alt' => 'alt-text',
            'accessibility' => 'alt-text',
            'ordering' => 'products',
            'airtable' => 'products',
            'catalog' => 'products',
        ];

        if (isset($aliases[$topic])) {
            $topic = $aliases[$topic];
        }

        if (! array_key_exists($topic, $validTopics)) {
            $topic = 'overview';
        }

        return view('dashboard.docs.index', [
            'activeTopic' => $topic,
            'sections' => $sections,
            'topics' => $validTopics,
        ]);
    }
}
