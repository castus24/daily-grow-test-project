<?php

namespace App\Imports;

use App\Models\Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ClientsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;

    private bool $updateExisting;
    private bool $skipDuplicates;

    public function __construct(
        bool $updateExisting = false,
        bool $skipDuplicates = false
    )
    {
        $this->updateExisting = $updateExisting;
        $this->skipDuplicates = $skipDuplicates;
    }

    public function model(array $row): Model|array|Builder|Client|null
    {
        $data = [
            'name' => trim($row['fio']),
            'phone' => $this->normalizePhone($row['telefon'] ?? null),
            'email' => !empty($row['email']) ? trim($row['email']) : null,
            'birth_date' => $this->parseDate($row['den_rozdeniia'] ?? null),
        ];

        $existingClient = null;
        if (!empty($data['email'])) {
            Log::info(111);
            $existingClient = Client::query()
                ->where('email', $data['email'])
                ->first();
        }

        if (!$existingClient && !empty($data['phone'])) {
            Log::info(222);
            $existingClient = Client::query()
                ->where('phone', $data['phone'])
                ->first();
        }

        if ($existingClient) {
            Log::info(333);
            if ($this->skipDuplicates) {
                Log::info(444);
                $this->skippedCount++;
                return null;
            }

            if ($this->updateExisting) {
                Log::info(555);
                $existingClient->update($data);
                $this->updatedCount++;
                return $existingClient;
            }
        }

        $this->createdCount++;
        return new Client($data);
    }

    public function rules(): array
    {
        return [
            '*.telefon' => 'nullable|string|max:20',
            '*.email' => 'nullable|email|max:255',
            '*.fio' => 'nullable|string|max:255',
            '*.den_rozdeniia' => 'nullable|numeric',
        ];
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    protected function normalizePhone($phone): array|string|null
    {
        if (empty($phone)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $phone);
    }

    protected function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (is_numeric($date)) {
                return Carbon::instance(Date::excelToDateTimeObject($date))
                    ->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (Exception $e) {
            Log::error('Ошибка парсинга даты: ' . $e->getMessage());
            return null;
        }
    }
}
