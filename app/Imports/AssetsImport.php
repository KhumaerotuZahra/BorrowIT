<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetGroup;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AssetsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    private $imported = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        $groupCode = $row['group_code'] ?? $row['grup_code'] ?? $row['group'] ?? null;
        $assetNumber = $row['asset_number'] ?? $row['no_asset'] ?? $row['nomor_asset'] ?? $row['number'] ?? null;
        $assetName = $row['asset_name'] ?? $row['nama_asset'] ?? $row['name'] ?? $row['nama'] ?? null;
        $stock = $row['available_stock'] ?? $row['stock'] ?? $row['qty'] ?? $row['quantity'] ?? $row['jumlah'] ?? 1;

        if (!$assetName) {
            $this->skipped++;
            return null;
        }

        if ($assetNumber && Asset::where('asset_number', $assetNumber)->exists()) {
            $this->skipped++;
            return null;
        }

        // Find asset group by code
        $groupId = null;
        if ($groupCode) {
            $group = AssetGroup::where('group_code', $groupCode)->first();
            $groupId = $group ? $group->id : null;
        }

        $this->imported++;

        return new Asset([
            'asset_id'        => Asset::generateAssetId(),
            'asset_group_id'  => $groupId,
            'asset_number'    => $assetNumber ?? 'AST-' . str_pad(Asset::count() + $this->imported, 3, '0', STR_PAD_LEFT),
            'asset_name'      => $assetName,
            'total_stock'     => (int) $stock,
            'available_stock' => (int) $stock,
        ]);
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }
}
