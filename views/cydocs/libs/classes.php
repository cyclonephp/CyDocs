<ul>
    <? foreach ($classes as $name => $url) : ?>
    <li><a href="<?= $url ?>"><?= $name ?></a></li>
    <? endforeach; ?>
</ul>