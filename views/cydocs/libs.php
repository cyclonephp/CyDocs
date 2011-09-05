<html>
    <head>
        <link type="text/css" rel="stylesheet" href="stylesheet.css"/>
    </head>
    <body>
<h1>Libraries</h1>
<span class="lnk-manual">(<a href="manual.html" target="frm-content">manual</a>)</span>
<ul class="libs">
    <? foreach ($libs as $name => $classes_url) : ?>
    <li><a href="<?= $classes_url ?>" target="frm-classes"><?= $name ?></a>
        <span class="lnk-manual">(<a href="libs/<?= $name ?>/manual.html" target="frm-content">manual</a>)</span>
    </li>
    <? endforeach; ?>
</ul>
    </body>
</html>
