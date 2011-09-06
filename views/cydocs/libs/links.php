<div class="links">
<? if ( ! empty($model->uses)) : ?>
<br/><strong>Uses</strong>
<ul class="linklist">
    <? foreach ($model->uses as $annot) : ?>
    <li><?= $annot->link . ' ' . $annot->text ?></li>
    <? endforeach; ?>
</ul>
<? endif ?>
<? if ( ! empty($model->usedby)) : ?>
<br/><strong>Used by</strong>
<ul class="linklist">
    <? foreach ($model->usedby as $annot) : ?>
    <li><?= $annot->link . ' ' . $annot->text ?></li>
    <? endforeach; ?>
</ul>
<? endif ?>
<? if ( ! empty($model->link)) : ?>
<br/><strong>See also</strong>
<ul class="linklist">
    <? foreach ($model->link as $annot) : ?>
    <li><?= $annot->link . ' ' . $annot->text ?></li>
    <? endforeach; ?>
</ul>
<? endif ?>
</div>