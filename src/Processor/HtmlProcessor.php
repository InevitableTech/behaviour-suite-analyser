<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

class HtmlProcessor
{
    private $html = '<!DOCTYPE html>';

    public function openCloseTag(string $tag, string $content = null, string $class = null)
    {
        $this->openTag(explode(',', $tag), $class);
        $this->content($content);
        $this->closeTag(array_reverse(explode(',', $tag)));

        return $this;
    }

    public function openTag($tag, string $class = null)
    {
        if (is_array($tag)) {
            foreach ($tag as $t) {
                $this->html .= "<$t";
                if ($class) {
                    $this->html .= " class='$class'";
                }
                $this->html .= '>';
            }

            return $this;
        }

        $this->html .= "<$tag";
        if ($class) {
            $this->html .= " class='$class'";
        }
        $this->html .= '>';

        return $this;
    }

    public function tag(string $tag)
    {
        $this->html .= "<$tag>";

        return $this;
    }

    public function closeTag($tag)
    {
        if (is_array($tag)) {
            foreach ($tag as $t) {
                $this->html .= "</$t>";
            }

            return $this;
        }

        $this->html .= "</$tag>";

        return $this;
    }

    public function content(string $content)
    {
        $this->html .= $content;

        return $this;
    }

    public function generate(): string
    {
        return $this->html;
    }
}
