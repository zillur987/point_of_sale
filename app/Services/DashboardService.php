<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\Branch;
use App\Models\Deal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DashboardService
{
    /** @return array<string, mixed> */
    public function stats(): array
    {
        return [
            'branches' => Branch::count(),
            
        ];
    }

    /**
     * All deal stages in order, each tagged as won / lost / open.
     * Uses optional columns only when they exist in the deal_stages table.
     *
     * @return Collection<int, array{id: int, name: string, kind: string}>
     */
    private function stages(): Collection
    {
        $table = 'deal_stages';
        $has = fn (string $col): bool => Schema::hasColumn($table, $col);

        $nameCol = collect(['name', 'title', 'label'])->first($has) ?? 'id';
        $orderCol = collect(['position', 'sort_order', 'order', 'sequence'])->first($has) ?? 'id';

        return DB::table($table)
            ->orderBy($orderCol)
            ->orderBy('id')
            ->get()
            ->map(function ($s) use ($has, $nameCol): array {
                $name = (string) ($s->{$nameCol} ?? $s->id);
                $lower = strtolower($name);

                $kind = 'open';
                if (($has('is_won') && (bool) $s->is_won) || str_contains($lower, 'won')) {
                    $kind = 'won';
                } elseif (($has('is_lost') && (bool) $s->is_lost) || str_contains($lower, 'lost')) {
                    $kind = 'lost';
                }

                return ['id' => (int) $s->id, 'name' => $name, 'kind' => $kind];
            });
    }

    /**
     * Deals per stage, in stage order (every stage listed, even with 0 deals).
     *
     * @param  Collection<int, array{id: int, name: string, kind: string}>  $stages
     * @return Collection<int, array{stage: string, label: string, count: int, total: float}>
     */
    private function pipeline(Collection $stages): Collection
    {
        $rows = Deal::query()
            ->selectRaw('deal_stage_id, COUNT(*) as deal_count, COALESCE(SUM(amount), 0) as deal_total')
            ->groupBy('deal_stage_id')
            ->get()
            ->keyBy('deal_stage_id');

        return $stages->map(function (array $stage) use ($rows): array {
            $row = $rows->get($stage['id']);

            return [
                'stage' => $stage['name'],
                'label' => $stage['name'],
                'count' => (int) ($row->deal_count ?? 0),
                'total' => (float) ($row->deal_total ?? 0),
            ];
        })->values();
    }
}