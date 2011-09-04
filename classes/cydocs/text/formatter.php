<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cydocs
 */
class CyDocs_Text_Formatter {

    /**
     * Retrurns a formatter that is able to format the lines as library manual text.
     *
     * @param array $text
     * @return CyDocs_Text_Formatter
     */
    public static function manual_formatter($text) {
        return new CyDocs_Text_Formatter($text, array(
            'c',
            'code',
//            'endcode',
            'internal',
            'section',
            'subsection',
            'img',
            'include'
        ));
    }

    /**
     * Retrurns a formatter that is able to format the lines as API doc text.
     *
     * @param array $text
     * @return CyDocs_Text_Formatter
     */
    public static function comment_formatter($text) {
        return new CyDocs_Text_Formatter($text, array(
            'c',
            'code',
            'internal'
        ));
    }

    private static $_available_tag_callbacks = array(
        'c' => 'coderef',
        'code' => 'code',
        'internal' => 'internal',
        'section' => 'section',
        'subsection' => 'subsection',
        'img' => 'image',
        'include' => 'include'
    );

    private $_tag_callbacks = array();

    private $_text;

    private $_length;

    private $_idx;

    /**
     *
     * @var CyDocs_Model_Manual
     */
    private $_manual;

    /**
     *
     * @var CyDocs_Model_Manual_Section
     */
    private $_current_section;

    /**
     *
     * @var CyDocs_Model_Manual_Section
     */
    private $_current_subsection;

    private function  __construct($text, $enabled_tags) {
        if (is_array($text)) {
            $text = implode("\n", $text);
        }
        foreach (self::$_available_tag_callbacks as $tag => $avail_cb) {
            if (in_array($tag, $enabled_tags)) {
                $this->_tag_callbacks[$tag] = $avail_cb;
            }
        }
        $this->_text = $text;
        $this->_length = strlen($text);
    }

    public function format() {
        $rval = '';
        $len = $this->_length;
        for($this->_idx = 0; $this->_idx < $len; ++$this->_idx) {
            $char = $this->_text[$this->_idx];// echo $char;
            if ($char === '@' || $char === '\\') {
                ++$this->_idx;
                $token = $this->next_token();
                if (isset($this->_tag_callbacks[$token])) {
                    $just_parsed = call_user_func(array($this
                            , 'tag_' . $this->_tag_callbacks[$token])
                            , $token);
                } else {
                    log_warning($this, "unknown formatting tag '$token'");
                }
            } else {
                $just_parsed = $char;
            }
            if ( ! is_null($this->_current_subsection)) {
                $this->_current_subsection->text .= $just_parsed;
            } elseif ( ! is_null($this->_current_section)) {
                $this->_current_section->text .= $just_parsed;
            } else {
                $rval .= $just_parsed;
            }
        }
        return $rval;
    }

    private function tag_coderef($tag) {
        $this->read_whitespaces();
        $coderef = $this->next_token();
        return CyDocs_Model::coderef_to_anchor($coderef) . ' '; // :)
    }

    private function tag_code($tag) {
        $rval = '<pre>';
        $code = '';
        for (;;) {
            if ($this->_idx == $this->_length - 1) {
                log_error("unclosed @code tag, omitting formatted source from output.");
                return '';
            }
            $code .= $this->read_whitespaces();
            $token = $this->next_token();
            if ($token == '@endcode' || $token == '\endcode')
                break;
            $code .= $token;
        }
        $highlighted = highlight_string('<?php ' . $code, true);

        $unwanted_prefix = "<code><span style=\"color: #000000\">
<span style=\"color: #0000BB\">&lt;?php&nbsp;<br />";

        $highlighted = substr($highlighted, strlen($unwanted_prefix));

        $rval .= $highlighted;
        return $rval . '</code></pre>';
    }

    private function tag_internal($tag) {
        if ( ! CyDocs::inst()->internal) {
            $this->_idx = $this->_length; // skipping the remaining (internal) part
        }
        return '';
    }

    private function tag_section($tag) {
        if (NULL === $this->_manual)
            throw new CyDocs_Exception("@section tags are not enabled in comment texts");

        $section = new CyDocs_Model_Manual_Section;
        $this->_manual->sections []= $section;
        $this->_current_section = $section;

        $this->parse_section_line($section);
    }

    private function parse_section_line(CyDocs_Model_Manual_Section $section) {
        $section_line = '';
        for(; $this->_idx < $this->_length; ++$this->_idx) {
            $char = $this->_text[$this->_idx];
            if ($char === "\n" || $char === "\r") // end of line
                break;

            $section_line .= $char;
        }

        $words = explode(' ', trim($section_line));
        $word_count = count($words);
        if ($word_count == 0) {
            log_warning($this, "failed to parse @section tag");
        } elseif ($word_count == 1) {
            $section->title = $section->id = $words[0];
            log_warning($this, "no section title found, using the section ID ('{$section->id}') as title");
        } else {
            $section->id = array_shift($words);
            $section->title = implode(' ', $words);
        }
    }

    private function tag_subsection($tag) {
        if (NULL === $this->_manual)
            throw new CyDocs_Exception("@subsection tags are not enabled in comment texts");

        if (NULL === $this->_current_section)
             throw new CyDocs_Exception("@subsection tags should not appear before at least one @section tag");

        $subsection = new CyDocs_Model_Manual_Section;
        $this->_current_section->sections []= $subsection;
        $this->_current_subsection = $subsection;

        $this->parse_section_line($subsection);
    }

    private function tag_img($tag) {
        
    }

    private function tag_include($tag) {
        
    }

    private function next_token() {
        $rval = '';
        for (; $this->_idx < $this->_length; ++$this->_idx) {
            $char = $this->_text[$this->_idx];
            if ($char == ' ' || $char == "\t" || $char == "\n") {
                break;
            }
            $rval .= $char;
        }
        return $rval;
    }

    private function read_whitespaces() {
        $rval = '';
        for (; $this->_idx < $this->_length; ++$this->_idx) {
            $char = $this->_text[$this->_idx];
            if ( ! ($char == ' ' || $char == "\t" || $char == "\n")) {
//                --$this->_idx;
                break;
            }
            $rval .= $char;
        }
        return $rval;
    }

    

    public function __invoke() {
        return $this->format();
    }

    public function create_manual() {
        $this->_manual = new CyDocs_Model_Manual;
        $this->_manual->text = $this->format();
        return $this->_manual;
    }

}