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
            'overview' => 'Overview & Quick Start',
            'pages' => 'Pages & Visual Editor',
            'formatting' => 'Accent & Outlined Text ({})',
            'counters' => 'Character & Word Counters',
            'projects' => 'Project Showcase & Case Studies',
            'products' => 'Product Catalog & Airtable',
            'videos' => 'Video Guidelines & Encoding',
            'images' => 'Image Dimensions & WebP',
            'seo' => 'SEO & Social Share (OG)',
            'sitemap' => 'XML Sitemap (/sitemap.xml)',
            'geo' => 'Generative Engine Optimization (/llms.txt)',
            'enquiries' => 'Enquiries & Quote Workflow',
            'datasheets' => 'Datasheet Exports',
            'emails' => 'Email Templates & Alerts',
            'staff' => 'Staff & Role Permissions',
            'deployment' => 'Server Setup & Deployment',
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
