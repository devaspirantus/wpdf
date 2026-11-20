<?php

namespace Tests\Feature;

use App\Enums\Role;
use Tests\TestCase;

class WpdfConfigTest extends TestCase
{
    public function test_center_name_is_a_non_empty_string(): void
    {
        $this->assertIsString(config('wpdf.center_name'));
        $this->assertNotSame('', config('wpdf.center_name'));
    }

    public function test_professions_is_a_non_empty_array_of_non_empty_strings(): void
    {
        $professions = config('wpdf.professions');

        $this->assertIsArray($professions);
        $this->assertNotEmpty($professions);
        $this->assertEquals(
            $professions,
            array_filter($professions, fn ($p) => is_string($p) && $p !== '')
        );
    }

    public function test_role_enum_has_exactly_the_four_expected_cases_with_arabic_labels(): void
    {
        $cases = array_column(Role::cases(), 'value');
        sort($cases);

        $this->assertSame(['practical', 'reception', 'review', 'theory'], $cases);

        foreach (Role::cases() as $role) {
            $label = $role->label();
            $this->assertIsString($label);
            $this->assertNotSame('', $label);
            $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $label);
        }
    }

    public function test_role_labels_match_the_specified_arabic_labels(): void
    {
        $expected = [
            'reception' => 'الاستقبال',
            'theory' => 'المعمل النظري',
            'practical' => 'المعمل العملي',
            'review' => 'رفع البيانات',
        ];

        foreach (Role::cases() as $role) {
            $this->assertSame($expected[$role->value], $role->label());
        }
    }
}
