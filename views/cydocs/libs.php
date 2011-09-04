<html>
    <head>
        <link type="text/css" rel="stylesheet" href="stylesheet.css"/>
    </head>
    <body>
<h1>Libraries</h1>
<ul class="libs">
    <? foreach ($libs as $name => $classes_url) : ?>
    <li><a href="<?= $classes_url ?>" target="frm-classes"><?= $name ?></a></li>
    <? endforeach; ?>
</ul>
    </body>
</html>
