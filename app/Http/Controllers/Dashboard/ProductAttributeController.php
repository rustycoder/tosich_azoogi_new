<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IProductRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function __construct(private IProductRepository $products) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));
        $group = $request->query('group');
        $activeGroup = filled($group) && $group !== 'all' ? (string) $group : null;

        $visibility = $request->query('visibility');
        $visibleOnly = match ($visibility) {
            'visible' => true,
            'hidden' => false,
            default => null,
        };

        $rawPerPage = (int) $request->query('per_page', 50);
        $perPage = in_array($rawPerPage, [50, 100, 150, 200], true) ? $rawPerPage : 50;

        $attributes = $this->products->attributeDashboardList($search, $activeGroup, $visibleOnly, $perPage);
        $groups = $this->products->attributeGroups();

        return view('dashboard.product-attributes.index', [
            'attributes' => $attributes,
            'search' => $search,
            'groups' => $groups,
            'activeGroup' => $activeGroup,
            'visibility' => $visibility,
            'perPage' => $perPage,
            'perPageOptions' => [50, 100, 150, 200],
        ]);
    }
}
