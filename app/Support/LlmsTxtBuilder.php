<?php

namespace App\Support;

use App\Enums\Status;
use App\Models\LlmFeed;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class LlmsTxtBuilder
{
    public const CACHE_KEY_SUMMARY = 'site.llms.txt';

    public const CACHE_KEY_FULL = 'site.llms_full.txt';

    public const CACHE_TTL_HOURS = 24;

    public const FEED_KEY_SUMMARY = 'summary';

    public const FEED_KEY_FULL = 'full';

    /**
     * Get or build the cached /llms.txt summary markdown.
     */
    public function getCachedSummary(): string
    {
        return Cache::remember(
            self::CACHE_KEY_SUMMARY,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): string => $this->buildSummaryMarkdown()
        );
    }

    /**
     * Get or build the cached /llms-full.txt detailed markdown.
     */
    public function getCachedFull(): string
    {
        return Cache::remember(
            self::CACHE_KEY_FULL,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): string => $this->buildFullMarkdown()
        );
    }

    /**
     * Clear all cached LLM text feeds.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_SUMMARY);
        Cache::forget(self::CACHE_KEY_FULL);
    }

    /**
     * Get active custom summary override if enabled, or null.
     */
    public function getCustomSummary(): ?string
    {
        try {
            $feed = LlmFeed::query()->where('key', self::FEED_KEY_SUMMARY)->first();
            if ($feed && $feed->is_custom && ! empty($feed->content)) {
                $baseUrl = rtrim(url('/'), '/');

                return str_replace(
                    ['{baseUrl}', 'https://demo.tosichcapital.com', 'http://localhost:8000'],
                    $baseUrl,
                    (string) $feed->content
                );
            }
        } catch (\Throwable) {
            // Fall back to default generation if database is unseeded or inaccessible
        }

        return null;
    }

    /**
     * Build the standard /llms.txt curated markdown index.
     */
    public function buildSummaryMarkdown(): string
    {
        $custom = $this->getCustomSummary();
        if ($custom !== null) {
            return $custom;
        }

        return $this->buildDefaultSummaryMarkdown();
    }

    /**
     * Build the default curated /llms.txt markdown index.
     */
    public function buildDefaultSummaryMarkdown(): string
    {
        $baseUrl = rtrim(url('/'), '/');

        $lines = [];
        $lines[] = '# Azoogi | Australian Manufacturer of Custom Architectural LED Lighting';
        $lines[] = '';
        $lines[] = '> Azoogi is a Sydney-based Australian manufacturer of custom architectural LED lighting. We design, engineer, supply and commission LED linear lighting, aluminium extrusion profiles, neon flex, COB and SMD strips, and smart lighting control systems (Casambi, Silvair, DALI-2 and Madrix) for commercial, hospitality, residential and exterior projects across Australia.';
        $lines[] = '';
        $lines[] = 'Azoogi works with architects, specifiers, lighting designers and electrical contractors, from concept and photometric design through to supply and commissioning. Profiles are cut to custom lengths and assembled in-house in Sydney. Azoogi does not install; installation is carried out by the project\'s electrical contractor.';
        $lines[] = '';
        $lines[] = '## Company & Trade Information';
        $lines[] = '- Location & HQ: Unit 47, 10-12 Girwah Place, Matraville NSW 2036, Australia.';
        $lines[] = '- Operations: In-house assembly, custom profile cutting, powder coating, and photometric testing.';
        $lines[] = '- Compliance & Warranties: Complies with Australian Standards and EESS requirements, with warranties up to 5 years (varies by product).';
        $lines[] = '- Services: Custom lighting design and engineering, photometric reports (DIALux/AGi32), IES file downloads, linear schedule takeoffs, custom lengths, and commissioning of smart control systems.';
        $lines[] = '- Trade Access: Wholesale portal access for approved architects, specifiers, lighting designers, and electrical contractors.';
        $lines[] = sprintf('- [Contact & Head Office](%s/contact): Speak directly with Azoogi\'s lighting design specialists.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Core Architectural Lighting Ranges';
        $lines[] = sprintf('- [Profiles](%s/products?category=Profiles): Aluminium channels that house and diffuse LED strips, cut to custom lengths. Styles include surface-mounted, recessed, trimless, corner, stair, skirting, wall washer and bendable.', $baseUrl);
        $lines[] = sprintf('- [COB Strips](%s/products?category=COB+Strips): Continuous, dot-free lines of light in standard, mini and multicolour options.', $baseUrl);
        $lines[] = sprintf('- [SMD Strips](%s/products?category=SMD+Strips): Surface-mounted diode strips for general lighting, in single colour and bendable zig-zag formats.', $baseUrl);
        $lines[] = sprintf('- [Neon Flex](%s/products?category=Neon+Flex): Silicone-encased LED lighting in standard, mini, black, 3D, dual colour, honeycomb, optic, sauna-rated and IP69-rated options.', $baseUrl);
        $lines[] = sprintf('- [Flex Panel Sheets](%s/products?category=Flex+Panel+Sheets): Even, panel-style LED illumination for backlit stone, signage and feature surfaces.', $baseUrl);
        $lines[] = sprintf('- [Handrail](%s/products?category=Handrail): Integrated LED handrail systems providing guidance and safety lighting for stairs and walkways.', $baseUrl);
        $lines[] = sprintf('- [Landscape Lighting](%s/products?category=Landscape+Lighting): Outdoor lighting including submersible pool lights and weatherproof garden spike lights.', $baseUrl);
        $lines[] = sprintf('- [Drivers](%s/products?category=Drivers): Constant voltage LED drivers in DALI, 5-in-1, switchable 12/24V and non-dimmable formats.', $baseUrl);
        $lines[] = sprintf('- [Controllers](%s/products?category=Controllers): Smart controllers and converters (DALI, Tuya and Casambi) for dimming, colour temperature and scene control in commercial and residential projects.', $baseUrl);
        $lines[] = sprintf('- [Accessories](%s/products?category=Accessories): Everything needed to install Azoogi products, including end caps, mounting clips, couplers, joiners, diffusers and suspension kits.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Smart Lighting Protocols & Control Systems';
        $lines[] = sprintf('- [Casambi Wireless Mesh Control](%s/casambi): Bluetooth Low Energy (BLE) wireless lighting networks for commercial projects, with no single point of failure.', $baseUrl);
        $lines[] = sprintf('- [Silvair Bluetooth Mesh](%s/silvair): Qualified Bluetooth Mesh smart building infrastructure and luminaire-level sensor networks.', $baseUrl);
        $lines[] = sprintf('- [DALI Control Centre](%s/dali-centre): DALI-2 and D4i addressable lighting control for multi-floor commercial buildings.', $baseUrl);
        $lines[] = sprintf('- [Madrix Pixel Mapping](%s/madrix): Art-Net, sACN and DMX512 control for 2D and 3D architectural pixel lighting.', $baseUrl);
        $lines[] = sprintf('- [AI Smart Lighting Systems](%s/ai-lighting): Occupancy-based control, human-centric circadian scheduling and energy optimisation.', $baseUrl);
        $lines[] = sprintf('- [Data Centre Lighting](%s/data-centre): High-efficiency, low-maintenance containment lighting for mission-critical facilities.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Tools, Project Support & Calculators';
        $lines[] = sprintf('- [Request a Quote & Project Support](%s/request-a-quote): Trade pricing, takeoff assistance, custom profile cutting and photometric specification support.', $baseUrl);
        $lines[] = '- Photometric & IES Downloads: Direct IES files and DIALux simulation support for lighting designers and architects.';
        $lines[] = sprintf('- [LED Strip Calculator](%s/led-strip-calculator): Online tool for voltage drop, maximum single-feed run lengths, driver sizing and wattage planning.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Projects';
        $lines[] = sprintf('- [Case Studies](%s/projects): Australian commercial, hospitality, luxury residential and exterior lighting projects.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Detailed Technical Specification Feed';
        $lines[] = sprintf('For complete SKU-level product parameters, datasheets and case studies, see the full LLM index: [%s/llms-full.txt](%s/llms-full.txt)', $baseUrl, $baseUrl);

        return implode("\n", $lines);
    }

    /**
     * Build the comprehensive /llms-full.txt feed including product catalogue and case studies.
     */
    public function buildFullMarkdown(): string
    {
        $baseUrl = rtrim(url('/'), '/');
        $summary = $this->buildSummaryMarkdown();

        $lines = [$summary];
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Published Product Catalog & Technical Specifications';

        $products = Product::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'Publish');
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('slug')->where('slug', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('airtable_id')->where('airtable_id', '!=', '');
                });
            })
            ->orderBy('product_name')
            ->get();

        foreach ($products as $product) {
            $identifier = $product->slug ?: $product->airtable_id;
            $url = $baseUrl.'/products/'.$identifier;
            $code = $product->product_code ? ' (Code: '.$product->product_code.')' : '';
            $desc = trim((string) ($product->product_description ?: $product->meta_description));

            $specs = [];
            if (! empty($product->category)) {
                $specs[] = 'Category: '.$product->category;
            }
            if (! empty($product->product_type)) {
                $specs[] = 'Type: '.$product->product_type;
            }
            if ($product->dimming_control) {
                $specs[] = 'Dimmable: Yes';
            }

            $specsStr = $specs !== [] ? ' ['.implode(' | ', $specs).']' : '';

            $lines[] = '';
            $lines[] = sprintf('### [%s%s](%s)%s', $product->product_name, $code, $url, $specsStr);
            if ($desc !== '') {
                $lines[] = $desc;
            }
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Featured Case Studies & Architectural Projects';

        $projects = Project::query()
            ->where('status', Status::Active)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('featured_order')
            ->get();

        foreach ($projects as $project) {
            $url = $baseUrl.'/project-detail?slug='.urlencode($project->slug);
            $tag = $project->tag ? ' | '.$project->tag : '';
            $location = $project->location ? ' ('.$project->location.')' : '';
            $summaryText = trim((string) ($project->summary ?: $project->description));

            $lines[] = '';
            $lines[] = sprintf('### [%s%s](%s)%s', $project->title, $location, $url, $tag);
            if ($summaryText !== '') {
                $lines[] = $summaryText;
            }
        }

        return implode("\n", $lines);
    }
}
