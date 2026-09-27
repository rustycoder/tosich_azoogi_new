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
            'media' => 'Media & Asset Guidelines',
            'content' => 'Content & Page Editor',
            'products' => 'Products & Airtable Sync',
            'enquiries' => 'Enquiries & Quote Workflow',
            'datasheets' => 'Datasheet Exports',
            'emails' => 'Email Templates & Alerts',
            'staff' => 'Staff & Role Permissions',
            'maintenance' => 'SEO, Cache & Deployment',
        ];

        if (! array_key_exists($topic, $validTopics)) {
            $topic = 'overview';
        }

        return view('dashboard.docs.index', [
            'activeTopic' => $topic,
            'topics' => $validTopics,
        ]);
    }
}
