<?php

namespace Tests\Feature;

use Tests\TestCase;

class PdfSpikeTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputDir = base_path('spikes/pdf-spike/output');
    }

    public function test_sample_pdf_artifact_exists_and_is_a_valid_pdf(): void
    {
        $path = $this->outputDir.'/browsershot.pdf';

        $this->assertFileExists($path);
        $this->assertSame('%PDF-', substr(file_get_contents($path, false, null, 0), 0, 5));
        $this->assertLessThan(10 * 1024 * 1024, filesize($path));
    }

    public function test_rendered_html_contains_arabic_tashkeel_content(): void
    {
        $html = file_get_contents($this->outputDir.'/rendered.html');

        foreach (['كهربائيّ مبيدات', 'مصطفى بن سعيد العمرانيّ', 'وَالدَّم', 'يُقفل'] as $phrase) {
            $this->assertStringContainsString($phrase, $html);
        }
    }

    public function test_template_is_rtl_and_renders_all_criteria_sections(): void
    {
        $template = file_get_contents(base_path('spikes/pdf-spike/templates/pdf.blade.php'));
        $this->assertStringContainsString('dir="rtl"', $template);

        $html = file_get_contents($this->outputDir.'/rendered.html');
        foreach (['أولا — فهم المشكلة وسير العملية', 'ثانيا — الأمن والسلامة', 'ثالثا — الأدوات والمعدات', 'رابعا — الصحة والنظافة'] as $section) {
            $this->assertStringContainsString($section, $html);
        }
    }

    public function test_browsershot_is_the_locked_pdf_library(): void
    {
        $agents = file_get_contents(base_path('AGENTS.md'));

        $this->assertStringContainsString('spatie/browsershot', $agents);
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $this->assertArrayHasKey('spatie/browsershot', $composer['require']);
        $this->assertArrayNotHasKey('mpdf/mpdf', $composer['require']);
    }
}
