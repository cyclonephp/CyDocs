<h<?= $level ?>><a name="<?= $section->title ?>"><?= $section->title ?></a></h<?= $level ?>>
<?= nl2br($section->text) ?>
<? foreach ($section->sections as $subsection) 
        echo cyclone\View::factory('cydocs/manual/section', array(
            'section' => $subsection,
            'level' => $level + 1
        ))->render(); ?>