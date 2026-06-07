<?php

namespace Tests\Unit\Services;

use App\Services\SoalSanitizer;
use Tests\TestCase;

class SoalSanitizerTest extends TestCase
{
    public function test_sanitizer_removes_malicious_script()
    {
        $sanitizer = new SoalSanitizer;
        $html = '<p>Test</p><script>alert("XSS")</script>';

        $clean = $sanitizer->sanitize($html);

        $this->assertStringContainsString('<p>Test</p>', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('alert', $clean);
    }

    public function test_sanitizer_preserves_katex_mathml()
    {
        $sanitizer = new SoalSanitizer;
        $html = '<p>Berapa <math xmlns="http://www.w3.org/1998/Math/MathML"><semantics><annotation encoding="application/x-tex">\frac{1}{2}</annotation></semantics></math>?</p>';

        $clean = $sanitizer->sanitize($html);

        $this->assertStringContainsString('<math xmlns="http://www.w3.org/1998/Math/MathML">', $clean);
        $this->assertStringContainsString('<semantics>', $clean);
        $this->assertStringContainsString('<annotation encoding="application/x-tex">\frac{1}{2}</annotation>', $clean);
    }
}
