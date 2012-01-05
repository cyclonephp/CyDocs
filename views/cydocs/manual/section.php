<h<?= $level ?>><a name="<?= $section->id ?>"><?= $section->title ?></a></h<?= $level ?>>
<?= $section->text ?>
<? foreach ($section->sections as $subsection) 
        echo cyclone\view\PHPView::factory('cydocs/manual/section', array(
            'section' => $subsection,
            'level' => $level + 1
        ))->render(); ?>