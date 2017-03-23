<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers BasicStringTemplate
 */
final class StringTemplateTest extends TestCase {

    public function testParse() : void
    {
        $template = StringTemplateFactory::create();
        $template->setTemplate('{greeting}, my name is {name}');
        $data = ['greeting' => 'hello', 'name' => 'mike'];

        $this->assertEquals(
            $template->parse($data),
            'hello, my name is mike'
        );
    }

    public function testParseThrowsExceptionForBadData() : void
    {
        $template = StringTemplateFactory::create();
        $template->setTemplate('{greeting}, my name is {name}');
        $data = ['greeting' => 1, 'name' => array(1,2,3,4,5)];

        $this->expectException(Exception::class);
        $template->parse($data);
    }

    public function testParseWithoutSettingTemplate() : void
    {
        $template = StringTemplateFactory::create();
        $data = ['greeting' => 'hello', 'name' => 'mike'];

        $this->assertEquals(
            $template->parse($data),
            ''
        );
    }

    public function testParseWithMissingFieldsLeavesTemplateTag() : void
    {
        $template = StringTemplateFactory::create();
        $template->setTemplate('{greeting}, my name is {whoops}');
        $data = ['greeting' => 'hello', 'name' => 'mike'];

        $this->assertEquals(
            $template->parse($data),
            'hello, my name is {whoops}'
        );
    }

}
