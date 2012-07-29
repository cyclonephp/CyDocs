<?php

namespace cyclone\docs;

use cyclone as cy;
use cyclone\docs;
use cyclone\docs\model;
/**
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package cydocs
 */
class Formatter {

    /**
     * Retrurns a formatter that is able to format the lines as library manual text.
     * After creation the manual can be created using the @c create_manual() method.
     *
     * @param array $text
     * @return CyDocs_Text_Formatter
     * @usedby CyDocs_Output_HTML::generate_manual()
     * @usedby CyDocs_Output_HTML_Library::generate_manual()
     */
    public static function manual_formatter($text) {
        return new Formatter($text, array(
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
        return new Formatter($text, array(
            'c',
            'code',
            'internal'
        ));
    }

    /**
     * Annotation name => formatter callback pairs.
     *
     * Not all annotations are allowed in any kind of documentation that can be
     * formatted by this class, the enabled annotations are set up at the static
     * factory methods.
     *
     * @see CyDocs_Text_Formatter::comment_formatter()
     * @see CyDocs_Text_Formatter::manual_formatter()
     * @var array
     */
    private static $_available_tag_callbacks = array(
        'c' => 'coderef',
        'code' => 'code',
        'internal' => 'internal',
        'section' => 'section',
        'subsection' => 'subsection',
        'img' => 'img',
        'include' => 'include'
    );

    /**
     * Annotation name => formatter callback pairs. Set up in the constructor.
     * The array items are a subset of @c CyDocs_Text_Formatter::$_available_tag_callbacks
     *
     * @var array
     */
    private $_tag_callbacks = array();

    /**
     * The original, raw text to be parsed.
     *
     * @var string
     */
    private $_text;

    /**
     * The length of the original text. It's stored in this property to avoid
     * re-calculating <code>strlen($this->_text)</code> in every parser methods
     * in the class.
     *
     * @var int
     */
    private $_length;

    /**
     * The index of the last parsed character in $_text .
     * The main character iteration is done at @c CyDocs_Text_Formatter::format()
     * but the other parser methods - called by the @c format() method will also
     * modify this value. All parser methods should maintain the proper value
     * manually, first of all they should avoid running it over @c CyDocs_Text_Formatter::$_length .
     *
     * @var int
     */
    private $_idx;

    /**
     * The manual instace to be generated. The instance value is created by @c create_manual()
     * and the parser methods that need this value will throw an exception  if
     * its value is <code>NULL</code>.
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
            if ($char === '@') {
                ++$this->_idx;
                $token = $this->next_token();
                if (isset($this->_tag_callbacks[$token])) {
                    $just_parsed = call_user_func(array($this
                            , 'tag_' . $this->_tag_callbacks[$token])
                            , $token);
                } else {
                    log_warning($this, "unknown formatting tag '$token'");
                    $just_parsed = $char . $token;
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

    public static function add_paragraphs($str) {
        $str = str_replace(array("\n\n", "\r\n\r\n"), '</p><p>', $str);
        return '<p>' . $str . '</p>';
    }

    private function tag_coderef($tag) {
        $this->read_whitespaces();
        $coderef = $this->next_token();
        return cy\docs\model\AbstractModel::coderef_to_anchor($coderef) . ' '; // :)
    }

    private function tag_code($tag) {
        $rval = '<pre>';
        $code = '';
        for (;;) {
            if ($this->_idx == $this->_length) {
                log_error($this, "unclosed @code tag, omitting formatted source from output.");
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

        if (cy\Docs::inst()->line_numbers) {
            $lines = explode('<br />', $highlighted);
            $idx = 1;
            $highlighted = '';
            foreach ($lines as $line) {
                $highlighted .= '<span style="width: 24px; display: inline-block; color: grey">' . $idx . '</span>' . $line . PHP_EOL;
                ++$idx;
            }
        }

        $rval .= $highlighted;
        return $rval . '</code></pre>';
    }

    private function tag_internal($tag) {
        if ( ! cy\Docs::inst()->internal) {
            $this->_idx = $this->_length; // skipping the remaining (internal) part
        }
        return '';
    }

    private function tag_section($tag) {
        if (NULL === $this->_manual)
            throw new docs\Exception("@section tags are not enabled in comment texts");

        $section = new model\SectionModel;
        $this->_manual->sections []= $section;
        $this->_current_subsection = NULL;
        if ( ! is_null($this->_current_section)) {
            $this->_current_section->text = self::add_paragraphs($this->_current_section->text);
        }
        $this->_current_section = $section;

        $this->parse_section_line($section);
    }

    private function parse_section_line(model\SectionModel $section) {
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

        $subsection = new model\SectionModel;
        $this->_current_section->sections []= $subsection;
        if ( ! is_null($this->_current_subsection)) {
            $this->_current_subsection->text = self::add_paragraphs($this->_current_subsection->text);
        }

        $this->_current_subsection = $subsection;

        $this->parse_section_line($subsection);
    }

    private function tag_img($tag) {
        $this->read_whitespaces();
        $img_rel_path = $this->next_token();
        $root_path_provider = model\AbstractModel::get_root_path_provider();
        $root_path = $root_path_provider->path_to_root(cy\Docs::inst()->current_class);
        if ($this->_manual !== NULL) {
            $this->_manual->assets []= $img_rel_path;
        }
        return "<img src='{$root_path}manual/{$img_rel_path}'/>";
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

    /**
     * Creates the object representing the manual created from the parsed text.
     * You are supposed to call this method only on those instances which were
     * created using @c CyDocs_Text_Formatter::manual_formatter() .
     *
     *
     * @return CyDocs_Model_Manual
     */
    public function create_manual() {
        $this->_manual = new model\ManualModel;
        $this->_manual->text = $this->format();
        return $this->_manual;
    }

}