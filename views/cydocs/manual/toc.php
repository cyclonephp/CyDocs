
<ul class="toc">
    <? foreach ($sections as $section): ?>
    <li><a href="#<?= $section->id ?>"> <?= $section->title ?></a>
        <? if ( ! empty($section->sections))
                echo cyclone\view\PHPView::factory('cydocs/manual/toc', array('sections' => $section->sections))->render() ?>
    </li>
    <? endforeach; ?>
</ul>