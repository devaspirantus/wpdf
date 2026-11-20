<?php

namespace App\Actions;

use App\Enums\CaseStatus;
use App\Models\WorkerCase;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateWorkerCase
{
    private const DUPLICATE_TICKET_MESSAGE = 'رقم التذكرة مستخدم مسبقاَ';

    public function handle(string $fullName, string $passportNumber, string $ticketNumber): WorkerCase
    {
        $this->validate($fullName, $passportNumber, $ticketNumber);

        $ticketNumber = trim($ticketNumber);

        if (WorkerCase::query()->where('ticket_number', $ticketNumber)->exists()) {
            throw ValidationException::withMessages([
                'ticketNumber' => self::DUPLICATE_TICKET_MESSAGE,
            ]);
        }

        try {
            return WorkerCase::query()->create([
                'full_name' => $fullName,
                'passport_number' => $passportNumber,
                'ticket_number' => $ticketNumber,
                'status' => CaseStatus::reception,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'ticketNumber' => self::DUPLICATE_TICKET_MESSAGE,
            ]);
        }
    }

    private function validate(string $fullName, string $passportNumber, string $ticketNumber): void
    {
        $validator = Validator::make([
            'fullName' => $fullName,
            'passportNumber' => $passportNumber,
            'ticketNumber' => $ticketNumber,
        ], [
            'fullName' => 'required|string|max:255',
            'passportNumber' => 'required|string|max:255',
            'ticketNumber' => 'required|string|max:255',
        ], [
            'required' => 'هذا الحقل مطلوب',
            'max' => 'هذا الحقل يتجاوز الحد المسموح',
        ], [
            'fullName' => 'اسم العامل',
            'passportNumber' => 'رقم الجواز',
            'ticketNumber' => 'رقم التذكرة',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->messages());
        }
    }
}
