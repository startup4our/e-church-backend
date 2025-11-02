<?php

namespace App\Repositories;

use App\Models\Handout;
use App\Enums\HandoutStatus;
use Illuminate\Support\Collection;

class HandoutRepository
{
    public function all(?int $churchId = null): Collection
    {
        $query = Handout::query();

        if ($churchId) {
            $query->where('church_id', $churchId);
        }

        return $query->orderByDesc('priority')
                     ->orderByDesc('publish_date')
                     ->get();
    }

    public function find(int $id): ?Handout
    {
        return Handout::find($id);
    }

    public function create(array $data): Handout
    {
        $data['publish_date'] = $data['publish_date'] ?? now();
        $data['status'] = $data['status'] ?? HandoutStatus::PENDING->value;

        return Handout::create($data);
    }

    public function update(Handout $handout, array $data): Handout
    {
        $handout->update($data);
        return $handout;
    }

    public function delete(Handout $handout): bool
    {
        return $handout->update(['status' => HandoutStatus::INACTIVE->value]);
    }

    public function activate(Handout $handout): Handout
    {
        $handout->update(['status' => HandoutStatus::ACTIVE->value]);
        return $handout;
    }

    public function schedule(Handout $handout, string $startDate, ?string $endDate = null): Handout
    {
        $handout->update([
            'status' => HandoutStatus::PENDING->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $handout;
    }

    public function getVisibleNow(int $churchId, array $areaIds): Collection
    {
        return Handout::query()
        ->where('church_id', $churchId)
        ->where(function ($query) use ($areaIds) {
            $query->whereIn('area_id', $areaIds)
                  ->orWhereNull('area_id');
        })
        ->visibleNow()
        ->get();
    }
}
