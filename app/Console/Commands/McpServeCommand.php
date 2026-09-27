<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mcp\Protocol\McpServer;
use App\Mcp\Tools\Backend\InspectSchemaTool;
use App\Mcp\Tools\Backend\MutateModelTool;
use App\Mcp\Tools\Backend\QueryModelRecordsTool;
use App\Mcp\Tools\Backend\UpdatePageContentTool;
use App\Mcp\Tools\Frontend\ListBladeViewsTool;
use App\Mcp\Tools\Frontend\ListRoutesTool;
use App\Mcp\Tools\Frontend\ReadBladeViewTool;
use App\Mcp\Tools\Frontend\SearchFrontendAssetsTool;
use App\Mcp\Tools\PublicChat\ManageQuoteListTool;
use App\Mcp\Tools\PublicChat\SearchProductsTool;
use App\Mcp\Tools\PublicChat\SubmitQuoteEnquiryTool;
use Illuminate\Console\Command;

class McpServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:serve {--mode=all : Capabilities to expose: all, frontend, backend, or public_chat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the native PHP Model Context Protocol (MCP) stdio server';

    /**
     * Execute the console command.
     */
    public function handle(McpServer $server): int
    {
        $mode = (string) $this->option('mode');

        if (in_array($mode, ['all', 'frontend'], true)) {
            $server->registerTool(app(ListBladeViewsTool::class));
            $server->registerTool(app(ReadBladeViewTool::class));
            $server->registerTool(app(ListRoutesTool::class));
            $server->registerTool(app(SearchFrontendAssetsTool::class));
        }

        if (in_array($mode, ['all', 'backend'], true)) {
            $server->registerTool(app(InspectSchemaTool::class));
            $server->registerTool(app(QueryModelRecordsTool::class));
            $server->registerTool(app(MutateModelTool::class));
            $server->registerTool(app(UpdatePageContentTool::class));
        }

        if (in_array($mode, ['all', 'public_chat'], true)) {
            $server->registerTool(app(SearchProductsTool::class));
            $server->registerTool(app(ManageQuoteListTool::class));
            $server->registerTool(app(SubmitQuoteEnquiryTool::class));
        }

        $server->listen();

        return 0;
    }
}
