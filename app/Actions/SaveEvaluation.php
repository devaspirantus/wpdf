<?php

namespace App\Actions;

use App\Enums\Difficulty;
use App\Models\Evaluation;
use App\Models\WorkerCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveEvaluation
{
    public const SECTIONS = [
        'فهم المشكلة وسير العملية' => [
            1 => 'أظهر فهماً لأهداف المهمة والغرض منها والعمل على تحقيقها',
            2 => 'استخدم الموارد المتاحة بفعالية لجمع المعلومات الضرورية',
            3 => 'اتبع جميع التعليمات',
            4 => 'أظهر مبادرة في إدارة الوقت لإكمال المهمة ضمن إطار مقبول',
            5 => 'اتبع التسلسل المنطقي وأدى المهام بالترتيب الصحيح',
        ],
        'الأمن والسلامة' => [
            6 => 'اختار معدات الحماية الشخصية المناسبة وارتداها قبل التنفيذ',
            7 => 'اتخذ جميع احتياطات السلامة أثناء الأداء',
            8 => 'تعامل مع المواد/الحاويات الخطرة بشكل آمن واتبع التخلص السليم',
            9 => 'أظهر وعياً بمحيطه والمخاطر على نفسه وعلى الآخرين',
        ],
        'الأدوات والمعدات' => [
            10 => 'أظهر الكفاءة في استخدام الأدوات والمعدات المناسبة',
            11 => 'أظهر فهماً للمبادئ الرئيسية وأجزائها لتحديد المشكلة الأساسية',
        ],
        'الصحة والنظافة' => [
            12 => 'حافظ على منطقة عمل نظيفة ومنظمة طوال المهمة',
            13 => 'سلّم جميع المعدات في حالة جيدة',
        ],
    ];

    public const SCALE = [
        0 => 'غير مؤدٍ',
        1 => 'ضعيف',
        2 => 'أقل من المتوسط',
        3 => 'مرضٍ',
        4 => 'جيد',
        5 => 'ممتاز',
    ];

    public function handle(WorkerCase $case, string $task, Difficulty $difficulty, array $scores): Evaluation
    {
        $this->validate($task, $difficulty, $scores);

        $user = Auth::user();

        $this->ensureEvaluatable($case);

        return $case->evaluation()->updateOrCreate(['case_id' => $case->id], [
            'task' => $task,
            'difficulty' => $difficulty,
            'scores' => $scores,
            'evaluator_name' => $user->name,
            'evaluator_id_number' => $user->email,
            'evaluator_signature' => '',
            'worker_signature' => '',
            'signed_at' => null,
        ]);
    }

    private function validate(string $task, Difficulty $difficulty, array $scores): void
    {
        $scoreRules = [];
        $scoreAttributes = [];
        foreach (self::flatCriteriaKeys() as $key) {
            $scoreRules["scores.{$key}"] = 'required|integer|between:0,5';
            $scoreAttributes["scores.{$key}"] = 'معيار رقم '.$key;
        }

        $validator = Validator::make([
            'task' => $task,
            'difficulty' => $difficulty->value,
            'scores' => $scores,
        ], [
            'task' => 'required|string|max:255',
            'difficulty' => 'required|in:'.implode(',', array_map(
                static fn (Difficulty $difficulty) => $difficulty->value,
                Difficulty::cases(),
            )),
            'scores' => 'required|array',
            ...$scoreRules,
        ], [
            'required' => ':attribute هذا الحقل مطلوب',
            'integer' => ':attribute يجب أن يكون رقمًا صحيحًا',
            'between' => ':attribute يجب أن يكون بين 0 و5',
            'in' => 'القيمة المختارة غير صالحة',
        ], [
            'task' => 'المهمة المطلوب تنفيذها',
            'difficulty' => 'مستوى الصعوبة',
            'scores' => 'الدرجات',
            ...$scoreAttributes,
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->messages());
        }
    }

    private function ensureEvaluatable(WorkerCase $case): void
    {
        $existing = $case->evaluation;

        if ($existing && $existing->signed_at !== null) {
            throw ValidationException::withMessages([
                'scores' => 'النموذج مقفل بعد التوقيع، لا يمكن تعديله',
            ]);
        }
    }

    private static function flatCriteriaKeys(): array
    {
        $keys = [];
        foreach (self::SECTIONS as $criteria) {
            $keys = array_merge($keys, array_keys($criteria));
        }

        return $keys;
    }
}
