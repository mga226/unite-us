<?php

/**
 * Things that implement this interface will world like this:
 *
 *   $template = new StringTemplateInterface;
 *   $template->setTemplate('Hello, {name}');
 *   echo $template->parse(['name' => 'world']); //  --> "Hello, world."
 *   echo $template->parse(['oops' => 'world']); //  --> "Hello, {name}."
 *
 */
interface StringTemplateInterface
{
    public function setTemplate(string $template) : void;
    public function parse(array $data) : string;
}

/**
 * Super-basic implementation of the StringTemplateInterface
 */
class BasicStringTemplate implements StringTemplateInterface
{
    /**
     * @var string
     */
    protected $template = '';

    /**
     * The template to be used when ::parse is called, with any dynamic
     * content specified like this: Hello {world}.
     * @param string $template
     */
    public function setTemplate(string $template) : void
    {
        $this->template = $template;
    }

    /**
     * @param  string[]  $data
     * @return  string
     */
    public function parse(array $data) : string
    {
        $parsed_template = $this->template;
        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw new Exception('Data passed to template parser must be array of strings');
            }
            $parsed_template = str_replace('{'.$key.'}', $value, $parsed_template);
        }
        return $parsed_template;
    }
}

/**
 * In real use I'd use some kind of IoC container class for this, but
 * for now this controls what class gets instantiated when we want an
 * implementation of StringTemplateInterface.
 */
class StringTemplateFactory
{
    public static function create() : StringTemplateInterface
    {
        return new BasicStringTemplate(); // change this line to use a different parser.
    }
}
