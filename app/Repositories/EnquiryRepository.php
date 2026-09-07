<?php

namespace App\Repositories;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use App\Repositories\Concerns\CountsByMonth;
use App\Repositories\Contracts\IEnquiryRepository;
use Illuminate\Support\Collection;

class EnquiryRepository implements IEnquiryRepository
{
    use CountsByMonth;

    public function kanban(EnquiryType $type, ?EnquiryStatus $status = null): Collection
    {
        return Enquiry::query()
            ->with('updater:id,name')
            ->where('type', $type)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get()
            ->groupBy(fn (Enquiry $enquiry): string => $enquiry->status->value);
    }

    public function create(array $data): Enquiry
    {
        return Enquiry::query()->create($data);
    }

    public function save(Enquiry $enquiry): void
    {
        $enquiry->save();
    }

    public function delete(Enquiry $enquiry): void
    {
        $enquiry->delete();
    }

    /**
     * @param  list<EnquiryType>  $types
     * @return array{
     *     countries: Collection<int, object>,
     *     user_agents: Collection<int, object>
     * }
     */
    public function originBuckets(array $types): array
    {
        $query = Enquiry::query()->whereIn('type', $types);

        return [
            'countries' => (clone $query)
                ->selectRaw('country as origin_key, count(*) as total')
                ->groupBy('country')
                ->orderByDesc('total')
                ->get(),
            'user_agents' => (clone $query)
                ->selectRaw('user_agent as origin_key, count(*) as total')
                ->groupBy('user_agent')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    /**
     * @param  list<EnquiryType>  $types
     * @return array<int, int>
     */
    public function monthlyCounts(array $types, int $year): array
    {
        return $this->countsByMonth(Enquiry::query()->whereIn('type', $types), $year);
    }
}
