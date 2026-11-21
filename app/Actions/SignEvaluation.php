<?php

namespace App\Actions;

use App\Models\Evaluation;
use App\Models\WorkerCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SignEvaluation
{
    private const MAX_SIGNATURE_BYTES = 2_000_000;

    public function handle(WorkerCase $case, string $evaluatorSignature, string $workerSignature): Evaluation
    {
        $evaluation = $case->evaluation()->first();

        if (! $evaluation) {
            throw ValidationException::withMessages([
                'evaluator_signature' => 'لا يوجد نموذج تقييم مسجل، احفظ التقييم أولاً',
            ]);
        }

        if ($evaluation->signed_at !== null) {
            throw ValidationException::withMessages([
                'evaluator_signature' => 'النموذج مقفل بعد التوقيع، لا يمكن التوقيع مرة أخرى',
            ]);
        }

        $validator = Validator::make([
            'evaluator_signature' => $evaluatorSignature,
            'worker_signature' => $workerSignature,
        ], [
            'evaluator_signature' => 'required|string',
            'worker_signature' => 'required|string',
        ], [
            'required' => ':attribute مطلوب',
        ], [
            'evaluator_signature' => 'توقيع المقيّم',
            'worker_signature' => 'توقيع العامل',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->messages());
        }

        $this->assertValidSignature($evaluatorSignature, 'evaluator_signature');
        $this->assertValidSignature($workerSignature, 'worker_signature');

        $evaluation->forceFill([
            'evaluator_signature' => $evaluatorSignature,
            'worker_signature' => $workerSignature,
            'signed_at' => now(),
        ])->save();

        return $evaluation->fresh();
    }

    private function assertValidSignature(string $signature, string $key): void
    {
        if (! preg_match('/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/', $signature)) {
            throw ValidationException::withMessages([
                $key => 'بصمة التوقيع غير صالحة',
            ]);
        }

        $payload = substr($signature, strrpos($signature, ',') + 1);
        $binary = base64_decode($payload, true);

        if ($binary === false || strlen($binary) > self::MAX_SIGNATURE_BYTES) {
            throw ValidationException::withMessages([
                $key => 'بصمة التوقيع غير صالحة',
            ]);
        }
    }
}
