<?php

namespace Tests\Unit;

use App\Support\HtmlBio;
use PHPUnit\Framework\TestCase;

class HtmlBioTest extends TestCase
{
    public function test_keeps_the_allowed_formatting(): void
    {
        $this->assertSame(
            '<p>A <strong>bold</strong> <em>idea</em>, <u>underlined</u>.</p>',
            HtmlBio::clean('<p>A <strong>bold</strong> <em>idea</em>, <u>underlined</u>.</p>')
        );
    }

    public function test_maps_b_i_and_div_to_the_allowed_tags(): void
    {
        $this->assertSame(
            '<p><strong>x</strong> <em>y</em></p>',
            HtmlBio::clean('<div><b>x</b> <i>y</i></div>')
        );
    }

    public function test_strips_every_attribute(): void
    {
        $this->assertSame(
            '<p>hi</p>',
            HtmlBio::clean('<p class="x" onclick="alert(1)" style="color:red">hi</p>')
        );
    }

    public function test_drops_a_script_whole_and_unwraps_unknown_tags(): void
    {
        $this->assertSame(
            '<p>safe words</p>',
            HtmlBio::clean('<p><span>safe </span><script>alert(1)</script>words</p>')
        );
    }

    public function test_neutralises_a_javascript_link(): void
    {
        // The <a> is unwrapped to its text, so there is no href to follow.
        $out = HtmlBio::clean('<p>click <a href="javascript:alert(1)">here</a></p>');
        $this->assertSame('<p>click here</p>', $out);
        $this->assertStringNotContainsString('javascript', $out);
        $this->assertStringNotContainsString('<a', $out);
    }

    public function test_escapes_a_bare_angle_bracket_in_plain_text(): void
    {
        $this->assertSame('<p>2 &lt; 3 &amp; 4 &gt; 1</p>', HtmlBio::clean("2 < 3 & 4 > 1"));
    }

    public function test_turns_plain_text_paragraphs_and_breaks_into_html(): void
    {
        $this->assertSame(
            "<p>First line<br>still first.</p><p>Second para.</p>",
            HtmlBio::clean("First line\nstill first.\n\nSecond para.")
        );
    }

    public function test_blank_becomes_null(): void
    {
        $this->assertNull(HtmlBio::clean(''));
        $this->assertNull(HtmlBio::clean('   '));
        $this->assertNull(HtmlBio::clean('<p></p>'));
        $this->assertNull(HtmlBio::clean('<p><br></p>'));
    }

    public function test_keeps_utf8_intact(): void
    {
        $this->assertSame('<p>Timișoara · încă</p>', HtmlBio::clean('<p>Timișoara · încă</p>'));
    }
}
